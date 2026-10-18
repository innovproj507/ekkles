<?php
/**
 * RoleMiddleware.php — Middleware de Control de Roles RBAC
 * ----------------------------------------------------------
 * Verifica que el usuario autenticado tenga el permiso
 * necesario para acceder al recurso solicitado.
 *
 * @package App\Middleware
 */

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class RoleMiddleware
{
    /** @var string Permiso requerido para acceder a la ruta */
    private string $permission;

    public function __construct(string $permission)
    {
        $this->permission = $permission;
    }

    /**
     * Verificar permiso RBAC
     *
     * @return bool true si el usuario tiene el permiso
     */
    public function handle(Request $request, Response $response): bool
    {
        if (!Auth::can($this->permission)) {
            // Si es AJAX, responder con JSON
            if ($request->isAjax()) {
                $response->json([
                    'success' => false,
                    'message' => 'No tiene permisos para realizar esta acción.',
                ], 403);
                return false;
            }

            Session::flash('error', 'No tiene permisos para acceder a esta sección.');
            
            if (Auth::can('dashboard.view')) {
                $response->redirect('/dashboard');
            } elseif (Auth::can('events.view') && $request->uri() !== '/eventos') {
                $response->redirect('/eventos');
            } elseif (Auth::can('donations.view') && $request->uri() !== '/ofrendas') {
                $response->redirect('/ofrendas');
            } else {
                $response->redirect('/perfil');
            }
            return false;
        }

        return true;
    }
}
