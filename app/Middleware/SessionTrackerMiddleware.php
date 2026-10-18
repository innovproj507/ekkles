<?php
/**
 * SessionTrackerMiddleware.php
 * ----------------------------------------------------
 * Actualiza la actividad del usuario y verifica si ha sido
 * forzado a cerrar sesión por el administrador.
 *
 * @package App\Middleware
 */

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;

class SessionTrackerMiddleware
{
    public function handle(): void
    {
        if (!Auth::check()) {
            return;
        }

        $userId = Auth::id();
        $db = Database::getInstance();

        // Obtener estado de force_logout del usuario
        $userStatus = $db->query(
            "SELECT force_logout FROM users WHERE id = ?",
            [$userId]
        );

        if (!empty($userStatus) && $userStatus[0]['force_logout'] == 1) {
            // El administrador ha forzado el cierre de sesión
            // Restablecer la bandera a 0
            $db->execute(
                "UPDATE users SET force_logout = 0 WHERE id = ?",
                [$userId]
            );

            // Destruir sesión y redirigir con mensaje
            Auth::logout();
            Session::set('flash_error', 'Tu sesión ha sido cerrada por el Administrador.');
            header('Location: /login');
            exit;
        }

        // Si todo está bien, actualizar su última actividad (excepto en peticiones de polling)
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '/api/messages/check') === false && strpos($uri, '/api/users/active-status') === false) {
            $db->execute(
                "UPDATE users SET last_activity_at = NOW() WHERE id = ?",
                [$userId]
            );
        }
    }
}
