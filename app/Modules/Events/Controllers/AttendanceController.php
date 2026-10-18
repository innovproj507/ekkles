<?php
/**
 * AttendanceController.php — Control de Asistencia Simple QR
 * @package App\Controllers
 */
namespace App\Modules\Events\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Database;
use App\Models\SimpleAttendance;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;

class AttendanceController extends Controller
{
    /**
     * Dashboard admin para Asistencia
     */
    public function index(Request $request, Response $response): void
    {
        $churchId = $this->churchId();
        
        // Obtener UUID de la iglesia para generar el enlace del QR
        $db = Database::getInstance();
        $church = $db->queryOne("SELECT uuid, name FROM churches WHERE id = ?", [$churchId]);
        
        $baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
        $qrUrl = $baseUrl . '/qr/asistencia/' . $church['uuid'];
        
        // Generar QR localmente (Suprimir deprecations de la librería en PHP 8.4)
        $oldErrorReporting = error_reporting();
        error_reporting($oldErrorReporting & ~E_DEPRECATED);
        
        $qrResult = Builder::create()
            ->writer(new PngWriter())
            ->data($qrUrl)
            ->encoding(new Encoding('UTF-8'))
            ->size(300)
            ->margin(10)
            ->build();
        $qrImage = $qrResult->getDataUri();
        
        error_reporting($oldErrorReporting);
        
        $attendanceModel = new SimpleAttendance();
        $todayTotals = $attendanceModel->getTodayTotals($churchId);
        
        // Estadísticas de los últimos 7 días
        $startDate = date('Y-m-d', strtotime('-6 days'));
        $endDate = date('Y-m-d');
        $recentStats = $attendanceModel->getAggregatedByDate($churchId, $startDate, $endDate);
        
        // Registros individuales recientes
        $recentRecords = $attendanceModel->getRecentRecords($churchId, 100);

        $this->view('Events::attendance/index', [
            'title'       => 'Control de Asistencia',
            'church'      => $church,
            'qrUrl'       => $qrUrl,
            'qrImage'     => $qrImage,
            'todayTotals' => $todayTotals,
            'recentStats' => $recentStats,
            'recentRecords' => $recentRecords,
        ]);
    }

    /** 
     * Vista pública para escanear el QR y registrar asistencia 
     */
    public function publicQr(Request $request, Response $response): void
    {
        $uuid = $request->param('uuid');
        
        $db = Database::getInstance();
        // Buscar la iglesia por UUID
        $church = $db->queryOne(
            "SELECT id, name, logo 
             FROM churches 
             WHERE uuid = ? AND is_active = 1", 
            [$uuid]
        );
        
        if (!$church) {
            $response->abort(404, "Iglesia no encontrada o inactiva.");
            return;
        }

        $this->view('Events::attendance/qr_form', [
            'title'  => 'Registro de Asistencia - ' . $church['name'],
            'church' => $church,
            'uuid'   => $uuid,
            'layout' => 'layouts/auth' // Use auth layout as it is a clean unauthenticated layout
        ]);
    }

    /** 
     * Guardar el registro público del QR 
     */
    public function storeQr(Request $request, Response $response): void
    {
        $uuid = $request->param('uuid') ?? $request->post('uuid');
        
        $db = Database::getInstance();
        $church = $db->queryOne(
            "SELECT id as church_id 
             FROM churches 
             WHERE uuid = ? AND is_active = 1", 
            [$uuid]
        );
        
        if (!$church) {
            $response->abort(404, "Iglesia no encontrada o inactiva.");
            return;
        }
        
        $adults = (int)$request->post('adults', 0);
        $children = (int)$request->post('children', 0);
        $guests = (int)$request->post('guests', 0);
        $memberId = (int)$request->post('member_id', 0);
        $registeredName = trim($request->post('registered_name', ''));
        
        // Prevent empty submissions
        if ($adults + $children + $guests === 0) {
            Session::flash('error', 'Por favor indique al menos 1 persona.');
            $response->redirect('/qr/asistencia/' . $uuid);
            return;
        }

        $model = new SimpleAttendance();

        // Evitar duplicados: un registro por miembro (o por nombre) por día
        $existing = $model->findForDay($church['church_id'], date('Y-m-d'), $memberId ?: null, $registeredName);
        if ($existing) {
            Session::flash('qr_registered', [
                'name'     => $registeredName,
                'adults'   => (int)$existing['adults_count'],
                'children' => (int)$existing['children_count'],
                'guests'   => (int)$existing['guests_count'],
            ]);
            $response->redirect('/qr/asistencia/' . $uuid . '?success=1&duplicado=1');
            return;
        }

        $model->create([
            'church_id' => $church['church_id'],
            'date' => date('Y-m-d'),
            'member_id' => $memberId > 0 ? $memberId : null,
            'registered_name' => $registeredName ?: null,
            'adults_count' => max(0, $adults),
            'children_count' => max(0, $children),
            'guests_count' => max(0, $guests)
        ]);
        
        if ($memberId > 0) {
            $db->execute("UPDATE members SET last_attendance_at = ? WHERE id = ?", [date('Y-m-d'), $memberId]);
        }

        Session::flash('qr_registered', [
            'name'     => $registeredName,
            'adults'   => max(0, $adults),
            'children' => max(0, $children),
            'guests'   => max(0, $guests),
        ]);
        $response->redirect('/qr/asistencia/' . $uuid . '?success=1');
    }

    public function searchMembers(Request $request, Response $response): void
    {
        $uuid = $request->param('uuid');
        $q = trim($request->get('q', ''));
        
        if (strlen($q) < 2) {
            $response->json([]);
            return;
        }

        $db = Database::getInstance();
        $church = $db->queryOne("SELECT id FROM churches WHERE uuid = ? AND is_active = 1", [$uuid]);
        
        if (!$church) {
            $response->json([]);
            return;
        }

        $search = "%{$q}%";
        $members = $db->query(
            "SELECT id, first_name, last_name 
             FROM members 
             WHERE church_id = ? 
             AND (first_name LIKE ? OR last_name LIKE ? OR CONCAT(first_name, ' ', last_name) LIKE ?)
             LIMIT 10",
            [$church['id'], $search, $search, $search]
        );

        $results = [];
        foreach ($members as $m) {
            $results[] = [
                'id' => $m['id'],
                'name' => trim($m['first_name'] . ' ' . $m['last_name'])
            ];
        }

        $response->json($results);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(Request $request, Response $response): void
    {
        $id = $request->param('id');
        $churchId = $this->churchId();
        
        $db = Database::getInstance();
        $record = $db->queryOne(
            "SELECT a.*, m.first_name, m.last_name 
             FROM simple_attendances a 
             LEFT JOIN members m ON m.id = a.member_id 
             WHERE a.id = ? AND a.church_id = ?",
            [$id, $churchId]
        );

        if (!$record) {
            Session::flash('error', 'Registro no encontrado.');
            $response->redirect('/asistencia');
            return;
        }

        $this->view('Events::attendance/edit', [
            'title' => 'Editar Registro de Asistencia',
            'record' => $record
        ]);
    }

    /**
     * Actualizar registro
     */
    public function update(Request $request, Response $response): void
    {
        $id = $request->param('id');
        $churchId = $this->churchId();
        
        $model = new SimpleAttendance();
        $record = $model->find($id);

        if (!$record || $record['church_id'] != $churchId) {
            Session::flash('error', 'Registro no encontrado.');
            $response->redirect('/asistencia');
            return;
        }

        $adults = (int)$request->post('adults_count', 0);
        $children = (int)$request->post('children_count', 0);
        $guests = (int)$request->post('guests_count', 0);
        $registeredName = trim($request->post('registered_name', ''));

        $model->update($id, [
            'adults_count' => max(0, $adults),
            'children_count' => max(0, $children),
            'guests_count' => max(0, $guests),
            'registered_name' => $registeredName ?: null,
        ]);

        Session::flash('success', 'Registro actualizado correctamente.');
        $response->redirect('/asistencia');
    }

    /**
     * Eliminar registro
     */
    public function destroy(Request $request, Response $response): void
    {
        $id = $request->param('id');
        $churchId = $this->churchId();
        
        $model = new SimpleAttendance();
        $record = $model->find($id);

        if (!$record || $record['church_id'] != $churchId) {
            Session::flash('error', 'Registro no encontrado.');
            $response->redirect('/asistencia');
            return;
        }

        $model->delete($id);

        Session::flash('success', 'Registro eliminado correctamente.');
        $response->redirect('/asistencia');
    }
}
