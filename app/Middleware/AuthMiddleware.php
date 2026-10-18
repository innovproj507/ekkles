<?php
/**
 * AuthMiddleware.php — Middleware de Autenticación
 * --------------------------------------------------
 * Verifica que exista un usuario autenticado en la sesión.
 * Si no, redirige al login.
 *
 * @package App\Middleware
 */

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware
{
    /**
     * Verificar autenticación
     *
     * @return bool true si la petición puede continuar
     */
    public function handle(Request $request, Response $response): bool
    {
        if (!Auth::check()) {
            Session::flash('error', 'Debe iniciar sesión para acceder a esta página.');
            $response->redirect('/login');
            return false;
        }

        // Si MFA está pendiente y no estamos en la ruta de MFA, redirigir
        if (Auth::mfaPending() && $request->uri() !== '/mfa') {
            $response->redirect('/mfa');
            return false;
        }

        // Registrar actividad de la sesión (y expulsar si es necesario)
        (new \App\Middleware\SessionTrackerMiddleware())->handle();

        return true;
    }
}
