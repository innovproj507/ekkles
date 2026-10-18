<?php
/**
 * FamilyController.php — Gestión de Familias
 * @package App\Controllers
 */
namespace App\Modules\Members\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Modules\System\Models\AuditLog;
use App\Modules\Members\Models\Family;
use App\Modules\Members\Models\Member;

class FamilyController extends Controller
{
    private Family $familyModel;

    public function __construct()
    {
        $this->familyModel = new Family();
    }

    public function index(Request $request, Response $response): void
    {
        $page    = max(1, (int)$request->get('page', 1));
        $section = $request->get('section', 'todas');
        $section = isset(Family::SECTIONS[$section]) ? $section : 'todas';
        $result  = $this->familyModel->getByChurch($this->churchId(), $page, 25, $section);
        $this->view('Members::families/index', [
            'title'         => 'Grupos Familiares',
            'families'      => $result['data'],
            'pagination'    => $result,
            'section'       => $section,
            'sectionCounts' => $this->familyModel->getSectionCounts($this->churchId()),
        ]);
    }

    public function create(Request $request, Response $response): void
    {
        $members = (new Member())->getForSelect($this->churchId());
        $this->view('Members::families/create', ['title' => 'Crear Familia', 'members' => $members]);
    }

    public function store(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $data = $request->all();

        $id = $this->familyModel->create([
            'church_id' => $this->churchId(),
            'name'      => trim($data['name']),
            'head_id'   => !empty($data['head_id']) ? (int)$data['head_id'] : null,
            'address'   => $data['address'] ?? null,
            'notes'     => $data['notes'] ?? null,
        ]);

        // Agregar miembros de la familia
        if (!empty($data['members'])) {
            foreach ($data['members'] as $idx => $memberId) {
                $rel = $data['relationships'][$idx] ?? 'otro';
                \App\Core\Database::getInstance()->execute(
                    "INSERT INTO family_members (family_id, member_id, relationship) VALUES (?, ?, ?)",
                    [$id, (int)$memberId, $rel]
                );
            }
        }

        AuditLog::record('create', 'families', null, 'families', $id);
        $this->redirectWithSuccess('/familias/' . $id, 'Familia registrada exitosamente.');
    }

    public function show(Request $request, Response $response): void
    {
        $id     = (int)$request->param('id');
        $family = $this->familyModel->getWithMembers($id);
        if (!$family || $family['church_id'] != $this->churchId()) {
            $response->redirect('/familias');
            return;
        }
        $this->view('Members::families/show', ['title' => $family['name'], 'family' => $family]);
    }

    public function update(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $id   = (int)$request->param('id');
        $data = $request->all();
        $this->familyModel->update($id, [
            'name'    => trim($data['name']),
            'head_id' => !empty($data['head_id']) ? (int)$data['head_id'] : null,
            'address' => $data['address'] ?? null,
            'notes'   => $data['notes'] ?? null,
        ]);
        AuditLog::record('update', 'families', null, 'families', $id);
        $this->redirectWithSuccess('/familias/' . $id, 'Familia actualizada.');
    }
}


