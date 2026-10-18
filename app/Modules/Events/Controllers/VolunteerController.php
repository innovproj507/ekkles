<?php
/**
 * VolunteerController.php — Gestión de Voluntarios
 * @package App\Controllers
 */
namespace App\Modules\Events\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Modules\Members\Models\Member;
use App\Modules\Events\Models\Event;

class VolunteerController extends Controller
{
    public function index(Request $request, Response $response): void
    {
        $churchId   = $this->churchId();
        $eventModel = new Event();
        $upcoming   = $eventModel->getUpcoming($churchId, 10);

        // Obtener equipos de los próximos eventos
        $eventTeams = [];
        foreach ($upcoming as $event) {
            $teams = $eventModel->getTeams($event['id']);
            if (!empty($teams)) {
                $eventTeams[$event['id']] = [
                    'event' => $event,
                    'teams' => $teams,
                    'stats' => [
                        'asignado'   => count(array_filter($teams, fn($t) => $t['status'] === 'asignado')),
                        'confirmado' => count(array_filter($teams, fn($t) => $t['status'] === 'confirmado')),
                        'ausente'    => count(array_filter($teams, fn($t) => $t['status'] === 'ausente')),
                    ],
                ];
            }
        }

        $members = (new Member())->getForSelect($churchId);

        $this->view('Events::volunteers/index', [
            'title'      => 'Voluntarios',
            'eventTeams' => $eventTeams,
            'members'    => $members,
            'upcoming'   => $upcoming,
        ]);
    }

    public function schedule(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $data    = $request->all();
        $eventId = (int)$data['event_id'];
        $memberId= (int)$data['member_id'];
        $role    = $data['role'] ?? 'ujier';

        \App\Core\Database::getInstance()->execute(
            "INSERT INTO event_teams (event_id, member_id, role, status) VALUES (?, ?, ?, 'asignado')
             ON DUPLICATE KEY UPDATE role = VALUES(role)",
            [$eventId, $memberId, $role]
        );

        if ($request->isAjax()) {
            $this->json(['success' => true, 'message' => 'Voluntario asignado.']);
            return;
        }

        $this->redirectWithSuccess('/voluntarios', 'Voluntario programado exitosamente.');
    }

    public function confirm(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $data   = $request->all();
        $teamId = (int)$data['team_id'];
        $status = in_array($data['status'], ['confirmado', 'ausente', 'asignado']) ? $data['status'] : 'confirmado';

        \App\Core\Database::getInstance()->execute(
            "UPDATE event_teams SET status = ? WHERE id = ?",
            [$status, $teamId]
        );

        if ($request->isAjax()) {
            $this->json(['success' => true]);
            return;
        }

        $this->redirectWithSuccess('/voluntarios', 'Estado actualizado.');
    }
}


