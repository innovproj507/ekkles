<?php
/**
 * MemberController.php — Gestión de Miembros
 * ---------------------------------------------
 * CRUD completo de perfiles de miembros con foto e historial.
 *
 * @package App\Controllers
 */

namespace App\Modules\Members\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Helpers\Upload;
use App\Helpers\Validator;
use App\Modules\System\Models\AuditLog;
use App\Modules\Events\Models\Attendance;
use App\Modules\Finance\Models\Donation;
use App\Modules\Members\Models\Member;
use App\Modules\Events\Models\Ministry;
use App\Modules\Pastoral\Models\PastoralFollow;
use Ramsey\Uuid\Uuid;

class MemberController extends Controller
{
    private Member $memberModel;
    private Ministry $ministryModel;

    public function __construct()
    {
        $this->memberModel   = new Member();
        $this->ministryModel = new Ministry();
    }

    /**
     * Listado con búsqueda y filtros
     */
    public function index(Request $request, Response $response): void
    {
        $churchId  = $this->churchId();
        $page      = max(1, (int)$request->get('page', 1));
        $perPage   = 25;
        $section = $request->get('section', 'miembros');
        $filters   = [
            'section'     => isset(Member::SECTIONS[$section]) ? $section : 'miembros',
            'search'      => $request->get('search', ''),
            'status'      => $request->get('status', ''),
            'ministry_id' => $request->get('ministry_id', ''),
            'gender'      => $request->get('gender', ''),
        ];

        $pagination = $this->memberModel->search($churchId, $filters, $page, $perPage);
        $ministries = $this->ministryModel->getForSelect($churchId);

        $this->view('Members::members/index', [
            'title'      => 'Miembros',
            'members'    => $pagination['data'],
            'pagination' => $pagination,
            'filters'    => $filters,
            'ministries' => $ministries,
            'sectionCounts' => $this->memberModel->getSectionCounts($churchId),
        ]);
    }

    /**
     * Formulario de creación
     */
    public function create(Request $request, Response $response): void
    {
        $ministries = $this->ministryModel->getForSelect($this->churchId());
        $this->view('Members::members/create', [
            'title'      => 'Registrar Miembro',
            'ministries' => $ministries,
            'old'        => Session::getFlash('old') ?? [],
            'errors'     => Session::getFlash('errors') ?? [],
        ]);
    }

    /**
     * Guardar nuevo miembro
     */
    public function store(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $churchId = $this->churchId();
        $data     = $request->all();

        $validator = Validator::make($data, [
            'first_name' => 'required|min:2|max:80',
            'last_name'  => 'required|min:2|max:80',
            'email'      => 'email|max:150',
            'phone'      => 'phone',
            'birth_date' => 'date',
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', $data);
            $response->redirect('/miembros/crear');
            return;
        }

        // Gestionar foto de perfil
        $photo = null;
        if (!empty($_FILES['photo']['name'])) {
            $uploader = new Upload();
            $result   = $uploader->uploadImage($_FILES['photo'], 'members');
            if ($result['success']) {
                $photo = $result['filename'];
            }
        }

        $memberId = $this->memberModel->create([
            'church_id'       => $churchId,
            'uuid'            => Uuid::uuid4()->toString(),
            'code'            => $this->memberModel->generateCode($churchId),
            'first_name'      => trim($data['first_name']),
            'last_name'       => trim($data['last_name']),
            'email'           => $data['email'] ?? null,
            'phone'           => $data['phone'] ?? null,
            'phone_alt'       => $data['phone_alt'] ?? null,
            'birth_date'      => $data['birth_date'] ?: null,
            'gender'          => $data['gender'] ?? 'M',
            'marital_status'  => $data['marital_status'] ?? null,
            'document_type'   => $data['document_type'] ?? null,
            'document_number' => $data['document_number'] ?? null,
            'address'         => $data['address'] ?? null,
            'city'            => $data['city'] ?? null,
            'baptism_date'    => $data['baptism_date'] ?: null,
            'membership_date' => $data['membership_date'] ?: null,
            'conversion_date' => $data['conversion_date'] ?: null,
            'status'          => $data['status'] ?? 'visitante',
            'is_converted'    => (int)($data['is_converted'] ?? 1) === 1 ? 1 : 0,
            'ministry_id'     => !empty($data['ministry_id']) ? (int)$data['ministry_id'] : null,
            'pastoral_notes'  => $data['pastoral_notes'] ?? null,
            'occupation'      => $data['occupation'] ?? null,
            'how_did_you_know'=> $data['how_did_you_know'] ?? null,
            'emergency_contact'=> $data['emergency_contact'] ?? null,
            'emergency_phone' => $data['emergency_phone'] ?? null,
            'photo'           => $photo,
            'created_by'      => Auth::id(),
        ]);

        AuditLog::record('create', 'members', null, 'members', $memberId, null, [
            'name' => $data['first_name'] . ' ' . $data['last_name'],
        ]);

        $this->redirectWithSuccess('/miembros/' . $memberId, 'Miembro registrado exitosamente.');
    }

    /**
     * Ver perfil de un miembro
     */
    public function show(Request $request, Response $response): void
    {
        $id = (int)$request->param('id');
        $member = $this->memberModel->getProfile($id);

        if (!$member || $member['church_id'] != $this->churchId()) {
            Session::flash('error', 'Miembro no encontrado.');
            $response->redirect('/miembros');
            return;
        }

        // Historial relacionado
        $attendanceModel = new Attendance();
        $donationModel   = new Donation();
        $pastoralModel   = new PastoralFollow();

        $this->view('Members::members/show', [
            'title'      => $member['first_name'] . ' ' . $member['last_name'],
            'member'     => $member,
            'attendance' => $attendanceModel->getMemberHistory($id, 10),
            'donations'  => $donationModel->getMemberHistory($id),
            'pastoral'   => $pastoralModel->getMemberHistory($id),
        ]);
    }

    /**
     * Formulario de edición
     */
    public function edit(Request $request, Response $response): void
    {
        $id     = (int)$request->param('id');
        $member = $this->memberModel->find($id);

        if (!$member || $member['church_id'] != $this->churchId()) {
            Session::flash('error', 'Miembro no encontrado.');
            $response->redirect('/miembros');
            return;
        }

        $ministries = $this->ministryModel->getForSelect($this->churchId());
        $this->view('Members::members/edit', [
            'title'      => 'Editar: ' . $member['first_name'],
            'member'     => $member,
            'ministries' => $ministries,
            'errors'     => Session::getFlash('errors') ?? [],
        ]);
    }

    /**
     * Actualizar miembro
     */
    public function update(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $id      = (int)$request->param('id');
        $member  = $this->memberModel->find($id);

        if (!$member || $member['church_id'] != $this->churchId()) {
            $response->redirect('/miembros');
            return;
        }

        $data = $request->all();
        $validator = Validator::make($data, [
            'first_name' => 'required|min:2|max:80',
            'last_name'  => 'required|min:2|max:80',
            'email'      => 'email|max:150',
        ]);

        if ($validator->fails()) {
            Session::flash('errors', $validator->errors());
            $response->redirect("/miembros/{$id}/editar");
            return;
        }

        // Gestionar foto
        $photo = $member['photo'];
        if (!empty($_FILES['photo']['name'])) {
            $uploader = new Upload();
            $result   = $uploader->uploadImage($_FILES['photo'], 'members');
            if ($result['success']) {
                // Eliminar foto anterior si existe
                if ($photo) {
                    $uploader->delete($photo);
                }
                $photo = $result['filename'];
            }
        }

        $oldValues = $member;
        $updateData = [
            'first_name'      => trim($data['first_name']),
            'last_name'       => trim($data['last_name']),
            'email'           => $data['email'] ?? null,
            'phone'           => $data['phone'] ?? null,
            'phone_alt'       => $data['phone_alt'] ?? null,
            'birth_date'      => $data['birth_date'] ?: null,
            'gender'          => $data['gender'] ?? 'M',
            'marital_status'  => $data['marital_status'] ?? null,
            'document_type'   => $data['document_type'] ?? null,
            'document_number' => $data['document_number'] ?? null,
            'address'         => $data['address'] ?? null,
            'city'            => $data['city'] ?? null,
            'baptism_date'    => $data['baptism_date'] ?: null,
            'membership_date' => $data['membership_date'] ?: null,
            'status'          => $data['status'] ?? $member['status'],
            'is_converted'    => (int)($data['is_converted'] ?? $member['is_converted']) === 1 ? 1 : 0,
            'ministry_id'     => !empty($data['ministry_id']) ? (int)$data['ministry_id'] : null,
            'pastoral_notes'  => $data['pastoral_notes'] ?? null,
            'occupation'      => $data['occupation'] ?? null,
            'emergency_contact'=> $data['emergency_contact'] ?? null,
            'emergency_phone' => $data['emergency_phone'] ?? null,
            'photo'           => $photo,
        ];

        $this->memberModel->update($id, $updateData);
        AuditLog::record('update', 'members', null, 'members', $id, $oldValues, $updateData);

        $this->redirectWithSuccess("/miembros/{$id}", 'Miembro actualizado correctamente.');
    }

    /**
     * Eliminación lógica de un miembro
     */
    public function destroy(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $id     = (int)$request->param('id');
        $member = $this->memberModel->find($id);

        if (!$member || $member['church_id'] != $this->churchId()) {
            $response->redirect('/miembros');
            return;
        }

        $this->memberModel->delete($id);
        AuditLog::record('delete', 'members', null, 'members', $id, $member, null);

        $this->redirectWithSuccess('/miembros', 'Miembro eliminado correctamente.');
    }
}


