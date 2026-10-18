<?php
/**
 * MinistryController.php — Gestión de Ministerios
 * @package App\Controllers
 */
namespace App\Modules\Events\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Modules\System\Models\AuditLog;
use App\Modules\Members\Models\Member;
use App\Modules\Events\Models\Ministry;

class MinistryController extends Controller
{
    private Ministry $ministryModel;

    public function __construct()
    {
        $this->ministryModel = new Ministry();
    }

    public function index(Request $request, Response $response): void
    {
        $ministries = $this->ministryModel->getActiveWithCount($this->churchId());
        $this->view('Events::ministries/index', ['title' => 'Ministerios', 'ministries' => $ministries]);
    }

    public function create(Request $request, Response $response): void
    {
        $members = (new Member())->getForSelect($this->churchId());
        $this->view('Events::ministries/create', ['title' => 'Nuevo Ministerio', 'members' => $members, 'mode' => 'create']);
    }

    public function store(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $data = $request->all();
        $id   = $this->ministryModel->create([
            'church_id'   => $this->churchId(),
            'name'        => trim($data['name']),
            'description' => $data['description'] ?? null,
            'color'       => $data['color'] ?? '#6366f1',
            'icon'        => $data['icon'] ?? null,
            'leader_id'   => !empty($data['leader_id']) ? (int)$data['leader_id'] : null,
            'is_active'   => 1,
        ]);
        
        if (!empty($data['members']) && is_array($data['members'])) {
            foreach ($data['members'] as $mId) {
                if (!empty($mId)) {
                    $this->ministryModel->addMember($id, (int)$mId);
                }
            }
        }

        AuditLog::record('create', 'ministries', null, 'ministries', $id);
        $this->redirectWithSuccess('/ministerios/' . $id, 'Ministerio creado exitosamente.');
    }

    public function show(Request $request, Response $response): void
    {
        $id       = (int)$request->param('id');
        $ministry = $this->ministryModel->find($id);
        if (!$ministry || $ministry['church_id'] != $this->churchId()) {
            $response->redirect('/ministerios');
            return;
        }
        
        $ministry['leader_name'] = null;
        if (!empty($ministry['leader_id'])) {
            $leader = (new Member())->find($ministry['leader_id']);
            if ($leader) {
                $ministry['leader_name'] = trim($leader['first_name'] . ' ' . $leader['last_name']);
            }
        }

        $members = $this->ministryModel->getMembers($id);
        $all_members = (new Member())->getForSelect($this->churchId());
        
        $this->view('Events::ministries/show', [
            'title' => $ministry['name'], 
            'ministry' => $ministry, 
            'members' => $members,
            'all_members' => $all_members
        ]);
    }

    public function update(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $id   = (int)$request->param('id');
        $data = $request->all();
        $this->ministryModel->update($id, [
            'name'        => trim($data['name']),
            'description' => $data['description'] ?? null,
            'color'       => $data['color'] ?? '#6366f1',
            'leader_id'   => !empty($data['leader_id']) ? (int)$data['leader_id'] : null,
        ]);
        AuditLog::record('update', 'ministries', null, 'ministries', $id);
        $this->redirectWithSuccess('/ministerios/' . $id, 'Ministerio actualizado.');
    }
}


