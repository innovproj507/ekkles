<?php
/**
 * EventController.php — Gestión de Eventos
 * ------------------------------------------
 * Cultos, conferencias, campamentos y eventos especiales.
 *
 * @package App\Controllers
 */

namespace App\Modules\Events\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Helpers\Validator;
use App\Modules\System\Models\AuditLog;
use App\Modules\Events\Models\Event;
use App\Modules\Events\Models\Ministry;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Ramsey\Uuid\Uuid;
use App\Core\View;
use Dompdf\Dompdf;
use Dompdf\Options;

class EventController extends Controller
{
    private Event $eventModel;

    public function __construct()
    {
        $this->eventModel = new Event();
    }

    public function index(Request $request, Response $response): void
    {
        $churchId = $this->churchId();
        $page     = max(1, (int)$request->get('page', 1));
        $filters  = [
            'search' => $request->get('search', ''),
            'event_type_id' => $request->get('event_type_id', ''),
            'status' => $request->get('status', ''),
        ];

        $pagination = $this->eventModel->search($churchId, $filters, $page);
        $upcoming   = $this->eventModel->getUpcoming($churchId, 5);
        $eventTypes = (new \App\Modules\Events\Models\EventType())->getAllActive($churchId);

        $this->view('Events::events/index', [
            'title'      => 'Eventos',
            'events'     => $pagination['data'],
            'pagination' => $pagination,
            'filters'    => $filters,
            'upcoming'   => $upcoming,
            'eventTypes' => $eventTypes,
        ]);
    }

    public function create(Request $request, Response $response): void
    {
        $ministries = (new \App\Modules\Events\Models\Ministry())->getForSelect($this->churchId());
        $eventTypes = (new \App\Modules\Events\Models\EventType())->getAllActive($this->churchId());
        $this->view('Events::events/create', [
            'title'      => 'Crear Evento',
            'ministries' => $ministries,
            'eventTypes' => $eventTypes,
            'rooms'      => (new \App\Modules\Events\Models\Room())->getActive($this->churchId()),
            'old'        => Session::getFlash('old') ?? [],
            'errors'     => Session::getFlash('errors') ?? [],
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $data = $request->all();

        $validator = Validator::make($data, [
            'title'          => 'required|min:3|max:200',
            'start_datetime' => 'required|date',
            'event_type_id'  => 'required',
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $data);
            $response->redirect('/eventos/crear');
            return;
        }

        $roomId = $this->validRoomId($data);
        $this->guardRoomConflict($roomId, $data, 0, '/eventos/crear');

        $uuid = Uuid::uuid4()->toString();

        // Generar código QR para el evento
        $qrUrl     = rtrim($_ENV['APP_URL'] ?? '', '/') . '/asistencia/checkin/' . $uuid;
        $qrPath    = dirname(__DIR__, 4) . '/public/uploads/qr/';
        $qrFile    = 'event_' . $uuid . '.png';

        if (!is_dir($qrPath)) mkdir($qrPath, 0755, true);

        try {
            $oldErrorReporting = error_reporting();
            error_reporting($oldErrorReporting & ~E_DEPRECATED);
            
            $qrCode  = new QrCode($qrUrl);
            $writer  = new PngWriter();
            $result  = $writer->write($qrCode);
            file_put_contents($qrPath . $qrFile, $result->getString());
            $qrRelative = 'qr/' . $qrFile;
            
            error_reporting($oldErrorReporting);
        } catch (\Throwable $e) {
            $qrRelative = null;
            error_log("QR ERROR: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            if (isset($oldErrorReporting)) {
                error_reporting($oldErrorReporting);
            }
        }

        $eventId = $this->eventModel->create([
            'church_id'          => $this->churchId(),
            'uuid'               => $uuid,
            'title'              => trim($data['title']),
            'description'        => $data['description'] ?? null,
            'event_type_id'      => $data['event_type_id'],
            'start_datetime'     => $data['start_datetime'],
            'end_datetime'       => $data['end_datetime'] ?: null,
            'location'           => $data['location'] ?? null,
            'room_id'            => $roomId,
            'food_contribution'  => isset($data['food_contribution']) ? 1 : 0,
            'online_url'         => $data['online_url'] ?? null,
            'max_capacity'       => !empty($data['max_capacity']) ? (int)$data['max_capacity'] : null,
            'registration_open'  => isset($data['registration_open']) ? 1 : 0,
            'ministry_id'        => !empty($data['ministry_id']) ? (int)$data['ministry_id'] : null,
            'qr_code'            => $qrRelative,
            'status'             => $data['status'] ?? 'publicado',
            'created_by'         => \App\Core\Auth::id(),
        ]);

        AuditLog::record('create', 'events', null, 'events', $eventId);

        $this->redirectWithSuccess('/eventos/' . $eventId, 'Evento creado exitosamente.');
    }

    public function show(Request $request, Response $response): void
    {
        $id    = (int)$request->param('id');
        $event = $this->eventModel->findById($id);

        if (!$event || $event['church_id'] != $this->churchId()) {
            Session::flash('error', 'Evento no encontrado.');
            $response->redirect('/eventos');
            return;
        }

        $attendance = $this->eventModel->getAttendance($id);
        $teams      = $this->eventModel->getTeams($id);

        $this->view('Events::events/show', [
            'title'      => $event['title'],
            'event'      => $event,
            'attendance' => $attendance,
            'teams'      => $teams,
            'qrUrl'      => $event['qr_code'] ? url('uploads/' . $event['qr_code']) : null,
        ]);
    }

    public function edit(Request $request, Response $response): void
    {
        $id    = (int)$request->param('id');
        $event = $this->eventModel->findById($id);

        if (!$event || $event['church_id'] != $this->churchId()) {
            $response->redirect('/eventos');
            return;
        }

        $ministries = (new \App\Modules\Events\Models\Ministry())->getForSelect($this->churchId());
        $eventTypes = (new \App\Modules\Events\Models\EventType())->getAllActive($this->churchId());
        $this->view('Events::events/create', [
            'title'      => 'Editar Evento',
            'event'      => $event,
            'ministries' => $ministries,
            'eventTypes' => $eventTypes,
            'rooms'      => (new \App\Modules\Events\Models\Room())->getActive($this->churchId()),
            'old'        => Session::getFlash('old') ?? $event,
            'errors'     => [],
            'editing'    => true,
        ]);
    }

    public function update(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $id    = (int)$request->param('id');
        $event = $this->eventModel->findById($id);

        if (!$event || $event['church_id'] != $this->churchId()) {
            $response->redirect('/eventos');
            return;
        }

        $data = $request->all();

        $roomId = $this->validRoomId($data);
        $this->guardRoomConflict($roomId, $data, $id, "/eventos/{$id}/editar");

        $qrRelative = $event['qr_code'];
        if (empty($qrRelative)) {
            $qrUrl     = rtrim($_ENV['APP_URL'] ?? '', '/') . '/asistencia/checkin/' . $event['uuid'];
            $qrPath    = dirname(__DIR__, 4) . '/public/uploads/qr/';
            $qrFile    = 'event_' . $event['uuid'] . '.png';

            if (!is_dir($qrPath)) mkdir($qrPath, 0755, true);

            try {
                $oldErrorReporting = error_reporting();
                error_reporting($oldErrorReporting & ~E_DEPRECATED);
                
                $qrCode  = new \Endroid\QrCode\QrCode($qrUrl);
                $writer  = new \Endroid\QrCode\Writer\PngWriter();
                $result  = $writer->write($qrCode);
                file_put_contents($qrPath . $qrFile, $result->getString());
                $qrRelative = 'qr/' . $qrFile;
                
                error_reporting($oldErrorReporting);
            } catch (\Throwable $e) {
                $qrRelative = null;
                if (isset($oldErrorReporting)) {
                    error_reporting($oldErrorReporting);
                }
            }
        }

        $this->eventModel->update($id, [
            'title'          => trim($data['title']),
            'description'    => $data['description'] ?? null,
            'event_type_id'  => $data['event_type_id'],
            'start_datetime' => $data['start_datetime'],
            'end_datetime'   => $data['end_datetime'] ?: null,
            'location'       => $data['location'] ?? null,
            'room_id'        => $roomId,
            'food_contribution' => isset($data['food_contribution']) ? 1 : 0,
            'max_capacity'   => !empty($data['max_capacity']) ? (int)$data['max_capacity'] : null,
            'status'         => $data['status'] ?? $event['status'],
            'ministry_id'    => !empty($data['ministry_id']) ? (int)$data['ministry_id'] : null,
            'qr_code'        => $qrRelative,
        ]);

        AuditLog::record('update', 'events', null, 'events', $id);
        $this->redirectWithSuccess('/eventos/' . $id, 'Evento actualizado correctamente.');
    }

    public function destroy(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $id = (int)$request->param('id');
        $reason = trim((string)$request->post('reason'));
        
        if (empty($reason)) {
            Session::flash('error', 'Debe proporcionar una causal para eliminar el evento.');
            $response->redirect('/eventos/' . $id);
            return;
        }

        $this->eventModel->delete($id);
        AuditLog::record('delete', 'events', null, 'events', $id, null, ['reason' => "Causal de eliminación: " . $reason]);
        $this->redirectWithSuccess('/eventos', 'Evento eliminado.');
    }

    /**
     * Disponibilidad de un salón (AJAX): eventos que ya lo ocupan en el horario indicado
     */
    public function roomAvailability(Request $request, Response $response): void
    {
        $roomId = (int)$request->get('room_id', 0);
        $start  = (string)$request->get('start', '');
        if (!$roomId || !strtotime($start)) {
            $response->json(['conflicts' => []]);
            return;
        }

        $conflicts = $this->eventModel->findRoomConflicts(
            $this->churchId(), $roomId, $start, $request->get('end') ?: null, (int)$request->get('exclude', 0)
        );
        $response->json(['conflicts' => array_map(fn($c) => [
            'id'    => (int)$c['id'],
            'title' => $c['title'],
            'when'  => $this->formatRange($c['start_datetime'], $c['end_datetime']),
        ], $conflicts)]);
    }

    /** room_id del formulario si el salón pertenece a la iglesia actual, si no null */
    private function validRoomId(array $data): ?int
    {
        $roomId = (int)($data['room_id'] ?? 0);
        if (!$roomId) {
            return null;
        }
        $room = (new \App\Modules\Events\Models\Room())->find($roomId);
        return ($room && (int)$room['church_id'] === $this->churchId()) ? $roomId : null;
    }

    /**
     * Si el salón ya está ocupado en ese horario, vuelve al formulario con una alerta.
     * El usuario puede guardar de todos modos marcando "confirm_room_conflict".
     */
    private function guardRoomConflict(?int $roomId, array $data, int $excludeId, string $backUrl): void
    {
        if (!$roomId || empty($data['start_datetime']) || !empty($data['confirm_room_conflict'])) {
            return;
        }

        $conflicts = $this->eventModel->findRoomConflicts(
            $this->churchId(), $roomId, $data['start_datetime'], $data['end_datetime'] ?: null, $excludeId
        );
        if (!$conflicts) {
            return;
        }

        $list = implode('; ', array_map(
            fn($c) => '"' . $c['title'] . '" (' . $this->formatRange($c['start_datetime'], $c['end_datetime']) . ')',
            $conflicts
        ));
        Session::flash('error', "El salón ya está ocupado en ese horario por: {$list}. Cambie el salón u horario, o marque \"Guardar de todos modos\".");
        Session::flash('old', array_merge($data, ['room_conflict' => true]));
        (new Response())->redirect($backUrl);
    }

    private function formatRange(string $start, ?string $end): string
    {
        $text = date('d/m/Y H:i', strtotime($start));
        return $end ? $text . ' – ' . date('H:i', strtotime($end)) : $text;
    }

    public function exportCalendar(Request $request, Response $response): void
    {
        $churchId = $this->churchId();
        $month = (int)$request->get('month', date('n'));
        $year  = (int)$request->get('year', date('Y'));

        // Obtener eventos del mes
        $events = $this->eventModel->getEventsByMonth($churchId, $month, $year);

        // Agrupar eventos por semana/día para una mejor presentación
        $groupedEvents = [];
        foreach ($events as $event) {
            $date = date('Y-m-d', strtotime($event['start_datetime']));
            $groupedEvents[$date][] = $event;
        }

        // Meses en español
        $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $monthName = $meses[$month];

        // Obtener información de la iglesia actual
        $db = \App\Core\Database::getInstance();
        $church = $db->queryOne("SELECT * FROM churches WHERE id = ?", [$churchId]);

        // Rutas del logo
        $logoBase64 = null;
        if (!empty($church['logo'])) {
            $logoPath = dirname(__DIR__, 4) . '/public/uploads/' . $church['logo'];
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($logoData);
            }
        }

        // Generar HTML
        $html = View::make('Events::events/calendar_pdf', [
            'events'        => $events,
            'groupedEvents' => $groupedEvents,
            'monthName'     => $monthName,
            'year'          => $year,
            'church'        => $church,
            'logoBase64'    => $logoBase64
        ]);

        // Configurar Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // para cargar imágenes o estilos externos si los hay

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        
        // El formato carta o A4 portrait es el mejor para enviar listas en WhatsApp
        $dompdf->setPaper('A4', 'portrait');
        
        $dompdf->render();

        $filename = "Calendario_Eventos_{$monthName}_{$year}.pdf";

        AuditLog::record('export', 'events', null, 'events', null, null, ['month' => $month, 'year' => $year]);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        echo $dompdf->output();
        exit;
    }
}


