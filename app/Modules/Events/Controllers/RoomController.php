<?php
/**
 * RoomController.php — Gestión de Salones
 * -----------------------------------------
 * Catálogo de salones donde se realizan los eventos.
 *
 * @package App\Controllers
 */

namespace App\Modules\Events\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Helpers\Validator;
use App\Modules\Events\Models\Room;
use App\Modules\System\Models\AuditLog;

class RoomController extends Controller
{
    private Room $roomModel;

    public function __construct()
    {
        $this->roomModel = new Room();
    }

    public function index(Request $request, Response $response): void
    {
        $this->view('Events::rooms/index', [
            'title' => 'Salones',
            'rooms' => $this->roomModel->getAll($this->churchId()),
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $data = $request->all();

        if (Validator::make($data, ['name' => 'required|min:2|max:100'])->fails()) {
            $this->redirectWithError('/salones', 'El nombre del salón debe tener entre 2 y 100 caracteres.');
            return;
        }

        $id = $this->roomModel->create([
            'church_id'   => $this->churchId(),
            'name'        => trim($data['name']),
            'capacity'    => !empty($data['capacity']) ? (int)$data['capacity'] : null,
            'description' => trim($data['description'] ?? '') ?: null,
            'is_active'   => 1,
        ]);
        AuditLog::record('create', 'events', null, 'rooms', $id, null, ['name' => $data['name']]);

        $this->redirectWithSuccess('/salones', 'Salón creado correctamente.');
    }

    public function update(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $room = $this->findOwnRoom((int)$request->param('id'));
        if (!$room) {
            $this->redirectWithError('/salones', 'Salón no encontrado.');
            return;
        }

        $data = $request->all();
        if (Validator::make($data, ['name' => 'required|min:2|max:100'])->fails()) {
            $this->redirectWithError('/salones', 'El nombre del salón debe tener entre 2 y 100 caracteres.');
            return;
        }

        $newValues = [
            'name'        => trim($data['name']),
            'capacity'    => !empty($data['capacity']) ? (int)$data['capacity'] : null,
            'description' => trim($data['description'] ?? '') ?: null,
            'is_active'   => isset($data['is_active']) ? 1 : 0,
        ];
        $this->roomModel->update($room['id'], $newValues);
        AuditLog::record('update', 'events', null, 'rooms', $room['id'], $room, $newValues);

        $this->redirectWithSuccess('/salones', 'Salón actualizado correctamente.');
    }

    public function destroy(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $room = $this->findOwnRoom((int)$request->param('id'));
        if (!$room) {
            $this->redirectWithError('/salones', 'Salón no encontrado.');
            return;
        }

        $inUse = (int)\App\Core\Database::getInstance()->queryOne(
            "SELECT COUNT(*) as c FROM events WHERE room_id = ? AND deleted_at IS NULL",
            [$room['id']]
        )['c'];
        if ($inUse > 0) {
            $this->redirectWithError('/salones', 'No se puede eliminar: hay eventos asignados a este salón. Puede desactivarlo en su lugar.');
            return;
        }

        $this->roomModel->delete($room['id']);
        AuditLog::record('delete', 'events', null, 'rooms', $room['id'], $room, null);

        $this->redirectWithSuccess('/salones', 'Salón eliminado.');
    }

    /** Salón de la iglesia actual o null */
    private function findOwnRoom(int $id): ?array
    {
        $room = $this->roomModel->find($id);
        return ($room && (int)$room['church_id'] === $this->churchId()) ? $room : null;
    }
}
