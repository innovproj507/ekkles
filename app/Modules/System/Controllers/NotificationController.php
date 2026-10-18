<?php
/**
 * NotificationController.php — Notificaciones
 * @package App\Controllers
 */
namespace App\Modules\System\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Modules\System\Models\Notification;

class NotificationController extends Controller
{
    public function index(Request $request, Response $response): void
    {
        $notifModel = new Notification();
        $notifs     = $notifModel->getUnread(Auth::id(), 50);
        $notifModel->markAllRead(Auth::id());

        $this->view('System::notifications/index', [
            'title'         => 'Notificaciones',
            'notifications' => $notifs,
        ]);
    }

    public function markRead(Request $request, Response $response): void
    {
        $notifModel = new Notification();
        $notifModel->markAllRead(Auth::id());
        $this->json(['success' => true]);
    }
}


