<?php
/**
 * UserController.php — Gestión de Usuarios y Perfil
 * @package App\Controllers
 */
namespace App\Modules\System\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Helpers\Validator;
use App\Modules\System\Models\AuditLog;
use App\Modules\System\Models\User;
use OTPHP\TOTP;

class UserController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function index(Request $request, Response $response): void
    {
        $users = $this->userModel->getByChurch($this->churchId());
        $this->view('System::users/index', ['title' => 'Usuarios del Sistema', 'users' => $users]);
    }

    public function create(Request $request, Response $response): void
    {
        $this->view('System::users/create', ['title' => 'Nuevo Usuario', 'mode' => 'create']);
    }

    public function store(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $data = $request->all();

        $validator = Validator::make($data, [
            'name'     => 'required|min:2',
            'email'    => 'required|email',
            'password' => 'required|password',
            'role'     => 'required',
        ]);

        if ($validator->fails()) {
            Session::flash('error', implode(' ', $validator->allErrors()));
            $response->redirect('/usuarios/crear');
            return;
        }

        $id = $this->userModel->createUser([
            'church_id' => $this->churchId(),
            'name'      => trim($data['name']),
            'email'     => trim($data['email']),
            'password'  => $data['password'],
            'role'      => $data['role'],
            'phone'     => $data['phone'] ?? null,
            'is_active' => 1,
        ]);

        AuditLog::record('create', 'users', null, 'users', $id);
        $this->redirectWithSuccess('/usuarios', 'Usuario creado exitosamente.');
    }

    public function edit(Request $request, Response $response): void
    {
        $id   = (int)$request->param('id');
        $user = $this->userModel->find($id);
        if (!$user || $user['church_id'] != $this->churchId()) {
            $response->redirect('/usuarios');
            return;
        }
        $this->view('System::users/edit', ['title' => 'Editar Usuario', 'user' => $user, 'mode' => 'edit']);
    }

    public function update(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $id   = (int)$request->param('id');
        $data = $request->all();

        $updateData = [
            'name'      => trim($data['name']),
            'email'     => trim($data['email']),
            'role'      => $data['role'],
            'phone'     => $data['phone'] ?? null,
            'is_active' => isset($data['is_active']) ? 1 : 0,
        ];

        if (!empty($data['password'])) {
            $this->userModel->updatePassword($id, $data['password']);
        }

        $this->userModel->update($id, $updateData);
        AuditLog::record('update', 'users', null, 'users', $id);
        $this->redirectWithSuccess('/usuarios', 'Usuario actualizado.');
    }

    public function profile(Request $request, Response $response): void
    {
        $user = $this->userModel->find(Auth::id());
        $this->view('System::users/profile', ['title' => 'Mi Perfil', 'user' => $user, 'mode' => 'profile']);
    }

    public function updateProfile(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $data = $request->all();
        $id   = Auth::id();

        $this->userModel->update($id, [
            'name'  => trim($data['name']),
            'phone' => $data['phone'] ?? null,
        ]);

        if (!empty($data['password'])) {
            $this->userModel->updatePassword($id, $data['password']);
        }

        Session::set('auth_user', array_merge(Auth::user(), ['name' => trim($data['name'])]));
        $this->redirectWithSuccess('/perfil', 'Perfil actualizado correctamente.');
    }

    public function mfaSetup(Request $request, Response $response): void
    {
        $totp   = TOTP::create();
        $secret = $totp->getSecret();
        $user   = Auth::user();
        $totp->setLabel($user['email']);
        $totp->setIssuer('Ekkles');

        Session::set('mfa_setup_secret', $secret);
        $qrUri = $totp->getProvisioningUri();

        $this->view('System::users/mfaSetup', [
            'title'   => 'Configurar Autenticación 2FA',
            'qrUri'   => $qrUri,
            'secret'  => $secret,
            'mode'    => 'mfa_setup',
        ]);
    }

    public function mfaEnable(Request $request, Response $response): void
    {
        $this->requireCsrf($request);
        $code   = trim($request->post('code', ''));
        $secret = Session::get('mfa_setup_secret');

        if (!$secret) {
            $response->redirect('/perfil/mfa');
            return;
        }

        $totp = TOTP::create($secret);
        if (!$totp->verify($code)) {
            Session::flash('error', 'Código inválido. Intente de nuevo.');
            $response->redirect('/perfil/mfa');
            return;
        }

        $this->userModel->saveMfaSecret(Auth::id(), $secret);
        Session::forget('mfa_setup_secret');

        AuditLog::record('mfa_enabled', 'users', null, 'users', Auth::id());
        $this->redirectWithSuccess('/perfil', 'Autenticación en dos pasos activada exitosamente.');
    }

    public function forceLogout(Request $request, Response $response): void
    {
        // Solo AJAX
        if (!$request->isAjax()) return;

        $id = (int)$request->post('id');
        $force = (int)$request->post('force', 1);

        $db = \App\Core\Database::getInstance();
        $db->execute("UPDATE users SET force_logout = ? WHERE id = ? AND church_id = ?", [$force, $id, $this->churchId()]);

        $response->json(['success' => true]);
    }

    public function sendMessage(Request $request, Response $response): void
    {
        // Solo AJAX
        if (!$request->isAjax()) {
            return;
        }

        $userId = (int)$request->post('user_id');
        $message = trim($request->post('message', ''));

        if (empty($message)) {
            $response->json(['success' => false, 'error' => 'Mensaje vacío.']);
            return;
        }

        try {
            $db = \App\Core\Database::getInstance();
            $db->execute(
                "INSERT INTO notifications (church_id, user_id, type, title, body, created_at) VALUES (?, ?, 'interna', 'Mensaje de Administrador', ?, NOW())",
                [$this->churchId(), $userId, $message]
            );
        } catch (\Exception $e) {
            $response->json(['success' => false, 'error' => 'DB error: ' . $e->getMessage()]);
            return;
        }

        $response->json(['success' => true]);
    }

    public function checkMessages(Request $request, Response $response): void
    {
        if (!$request->isAjax()) return;

        $db = \App\Core\Database::getInstance();
        $userId = Auth::id();

        // Buscar mensajes no leídos
        $messages = $db->query(
            "SELECT id, body, created_at FROM notifications WHERE user_id = ? AND type = 'interna' AND read_at IS NULL ORDER BY created_at ASC",
            [$userId]
        );

        if (!empty($messages)) {
            // Marcar como leídos
            $ids = array_column($messages, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $db->execute("UPDATE notifications SET read_at = NOW() WHERE id IN ($placeholders)", $ids);
        }

        $response->json(['messages' => $messages]);
    }

    public function activeStatus(Request $request, Response $response): void
    {
        if (!$request->isAjax()) return;

        $users = $this->userModel->getByChurch($this->churchId());
        
        $statuses = [];
        $now = time();
        
        foreach ($users as $u) {
            $statusText = 'Offline';
            $statusColor = 'bg-red-500 shadow-red-500/50';
            
            if (!empty($u['last_activity_at'])) {
                $diff = $now - strtotime($u['last_activity_at']);
                $minutes = floor($diff / 60);
                if ($minutes < 2) {
                    $statusColor = 'bg-green-500 shadow-green-500/50';
                    $statusText = 'Online';
                } elseif ($minutes <= 15) {
                    $statusColor = 'bg-yellow-500 shadow-yellow-500/50';
                    $statusText = 'Ausente';
                }
            }
            
            $statuses[$u['id']] = [
                'text' => $statusText,
                'color' => $statusColor
            ];
        }

        $response->json(['statuses' => $statuses]);
    }
}


