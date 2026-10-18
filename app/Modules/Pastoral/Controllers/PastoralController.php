<?php
/**
 * PastoralController.php — CRM Pastoral
 * ----------------------------------------
 * Pipeline visitante → miembro, seguimientos y alertas.
 *
 * @package App\Controllers
 */
namespace App\Modules\Pastoral\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Modules\System\Models\AuditLog;
use App\Modules\Members\Models\Member;
use App\Modules\Pastoral\Models\PastoralFollow;
use App\Modules\System\Models\User;

class PastoralController extends Controller
{
    private PastoralFollow $pastoralModel;

    public function __construct()
    {
        $this->pastoralModel = new PastoralFollow();
    }

    public function index(Request $request, Response $response): void
    {
        $churchId = $this->churchId();
        $page     = max(1, (int)$request->get('page', 1));
        $filters  = [
            'status'         => $request->get('status', ''),
            'pipeline_stage' => $request->get('stage', ''),
            'assigned_to'    => $request->get('assigned_to', ''),
            'search'         => $request->get('search', ''),
        ];

        $pagination = $this->pastoralModel->search($churchId, $filters, $page);
        $overdue    = $this->pastoralModel->getOverdue($churchId);
        $users      = (new User())->getByChurch($churchId);

        $this->view('Pastoral::pastoral/index', [
            'title'      => 'Seguimiento de Discipulado',
            'follows'    => $pagination['data'],
            'pagination' => $pagination,
            'filters'    => $filters,
            'overdue'    => $overdue,
            'users'      => $users,
        ]);
    }

    public function pipeline(Request $request, Response $response): void
    {
        $churchId = $this->churchId();
        $pipeline = $this->pastoralModel->getPipelineByStage($churchId);
        $summary  = $this->pastoralModel->getPipelineSummary($churchId);

        $this->view('Pastoral::pastoral/pipeline', [
            'title'    => 'Pipeline de Discipulado',
            'pipeline' => $pipeline,
            'summary'  => $summary,
        ]);
    }

    public function create(Request $request, Response $response): void
    {
        $members = (new Member())->getForSelect($this->churchId());
        $users   = (new User())->getByChurch($this->churchId());

        $this->view('Pastoral::pastoral/create', [
            'title'   => 'Nuevo Seguimiento',
            'members' => $members,
            'users'   => $users,
            'mode'    => 'create',
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $data     = $request->all();
        $churchId = $this->churchId();

        $id = $this->pastoralModel->create([
            'church_id'      => $churchId,
            'member_id'      => (int)$data['member_id'],
            'assigned_to'    => !empty($data['assigned_to']) ? (int)$data['assigned_to'] : Auth::id(),
            'type'           => $data['type'] ?? 'llamada',
            'status'         => 'pendiente',
            'pipeline_stage' => $data['pipeline_stage'] ?? 'visitante',
            'subject'        => $data['subject'] ?? null,
            'notes'          => $data['notes'] ?? null,
            'scheduled_at'   => !empty($data['scheduled_at']) ? $data['scheduled_at'] : null,
            'next_follow_at' => !empty($data['next_follow_at']) ? $data['next_follow_at'] : null,
            'priority'       => $data['priority'] ?? 'media',
            'created_by'     => Auth::id(),
        ]);

        AuditLog::record('create', 'pastoral', null, 'pastoral_follows', $id);
        $this->redirectWithSuccess('/pastoral', 'Seguimiento creado exitosamente.');
    }

    public function show(Request $request, Response $response): void
    {
        $id     = (int)$request->param('id');
        $follow = $this->pastoralModel->find($id);

        if (!$follow || $follow['church_id'] != $this->churchId()) {
            $response->redirect('/pastoral');
            return;
        }

        $member = (new Member())->find($follow['member_id']);
        $this->view('Pastoral::pastoral/show', ['follow' => $follow, 'member' => $member, 'mode' => 'show', 'title' => 'Detalle Seguimiento']);
    }

    public function update(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $id   = (int)$request->param('id');
        $data = $request->all();

        $updateData = [
            'status'         => $data['status'] ?? 'en_proceso',
            'pipeline_stage' => $data['pipeline_stage'] ?? 'visitante',
            'notes'          => $data['notes'] ?? null,
            'subject'        => $data['subject'] ?? null,
            'priority'       => $data['priority'] ?? 'media',
            'next_follow_at' => !empty($data['next_follow_at']) ? $data['next_follow_at'] : null,
        ];

        if ($data['status'] === 'completado') {
            $updateData['completed_at'] = date('Y-m-d H:i:s');
        }

        $this->pastoralModel->update($id, $updateData);
        AuditLog::record('update', 'pastoral', null, 'pastoral_follows', $id);
        $this->redirectWithSuccess('/pastoral', 'Seguimiento actualizado.');
    }
}


