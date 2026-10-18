<?php

namespace App\Modules\Events\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Helpers\Validator;
use App\Modules\Events\Models\EventType;

class EventTypeController extends Controller
{
    private EventType $eventTypeModel;

    public function __construct()
    {
        $this->eventTypeModel = new EventType();
    }

    public function index(Request $request, Response $response): void
    {
        $churchId = $this->churchId();
        $types = $this->eventTypeModel->getAll($churchId);

        $this->view('Events::events/types', [
            'title' => 'Tipos de Eventos',
            'types' => $types
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $data = $request->all();

        $validator = Validator::make($data, [
            'name' => 'required|min:2|max:100'
        ]);

        if ($validator->fails()) {
            Session::flash('error', 'El nombre es inválido (debe tener entre 2 y 100 caracteres).');
            $response->redirect('/eventos/tipos');
            return;
        }

        $this->eventTypeModel->create([
            'church_id' => $this->churchId(),
            'name'      => $data['name'],
            'color'     => $data['color'] ?? '#4f46e5',
            'is_active' => isset($data['is_active']) ? 1 : 0
        ]);

        Session::flash('success', 'Tipo de evento creado correctamente.');
        $response->redirect('/eventos/tipos');
    }

    public function update(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $id = (int)$request->param('id');
        $type = $this->eventTypeModel->find($id);

        if (!$type || (int)$type['church_id'] !== $this->churchId()) {
            $response->redirect('/eventos/tipos');
            return;
        }

        $data = $request->all();
        $this->eventTypeModel->update($id, [
            'name'      => $data['name'],
            'color'     => $data['color'] ?? '#4f46e5',
            'is_active' => isset($data['is_active']) ? 1 : 0
        ]);

        Session::flash('success', 'Tipo de evento actualizado correctamente.');
        $response->redirect('/eventos/tipos');
    }

    public function destroy(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $id = (int)$request->param('id');
        $type = $this->eventTypeModel->find($id);

        if (!$type || (int)$type['church_id'] !== $this->churchId()) {
            $response->redirect('/eventos/tipos');
            return;
        }

        // Avoid deleting if it's in use
        $db = \App\Core\Database::getInstance();
        $count = $db->queryOne("SELECT COUNT(*) as c FROM events WHERE event_type_id = ?", [$id])['c'];
        
        if ($count > 0) {
            Session::flash('error', 'No se puede eliminar este tipo de evento porque hay eventos que lo están usando. Puedes desactivarlo en su lugar.');
        } else {
            $this->eventTypeModel->delete($id);
            Session::flash('success', 'Tipo de evento eliminado.');
        }

        $response->redirect('/eventos/tipos');
    }
}
