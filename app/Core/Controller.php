<?php
/**
 * Controller.php — Controlador Base
 * -----------------------------------
 * Clase base para todos los controladores. Proporciona helpers
 * para renderizar vistas, redirigir y validar.
 *
 * @package App\Core
 */

namespace App\Core;

abstract class Controller
{
    /** @var Request Petición HTTP actual */
    protected Request $request;

    /** @var Response Respuesta HTTP */
    protected Response $response;

    /**
     * Renderizar una vista dentro de un layout
     */
    protected function view(string $view, array $data = [], string $layout = 'app'): void
    {
        // Inyectar datos globales disponibles en todas las vistas
        $data['auth_user']   = Auth::user();
        $data['auth_can']    = fn(string $p) => Auth::can($p);
        $data['flash_success']= Session::getFlash('success');
        $data['flash_error']  = Session::getFlash('error');
        $data['flash_info']   = Session::getFlash('info');
        $data['csrf_token']   = Session::generateCsrfToken();
        $data['app_config']   = require dirname(__DIR__, 2) . '/config/app.php';

        View::render($view, $data, $layout);
    }

    /**
     * Redirigir con mensaje flash de éxito
     */
    protected function redirectWithSuccess(string $url, string $message): void
    {
        Session::flash('success', $message);
        $this->redirect($url);
    }

    /**
     * Redirigir con mensaje flash de error
     */
    protected function redirectWithError(string $url, string $message): void
    {
        Session::flash('error', $message);
        $this->redirect($url);
    }

    /**
     * Redirigir a una URL
     */
    protected function redirect(string $url): void
    {
        $response = new Response();
        $response->redirect($url);
    }

    /**
     * Responder con JSON
     */
    protected function json(mixed $data, int $status = 200): void
    {
        $response = new Response();
        $response->json($data, $status);
    }

    /**
     * Verificar el token CSRF en peticiones POST
     */
    protected function verifyCsrf(Request $request): bool
    {
        $token = $request->csrfToken();
        return $token && Session::verifyCsrfToken($token);
    }

    /**
     * Verificar CSRF y abortar si falla
     */
    protected function requireCsrf(Request $request): void
    {
        if (!$this->verifyCsrf($request)) {
            Session::flash('error', 'Token de seguridad inválido. Por favor intente de nuevo.');
            $response = new Response();
            $response->back();
        }
    }

    /**
     * Obtener el ID de la iglesia del usuario autenticado
     */
    protected function churchId(): int
    {
        return Auth::churchId();
    }

    /**
     * Obtener el usuario autenticado
     */
    protected function authUser(): ?array
    {
        return Auth::user();
    }

    /**
     * Requerir un permiso específico o abortar/redirigir
     */
    protected function requirePermission(string $permission): void
    {
        if (!Auth::can($permission)) {
            Session::flash('error', 'No tiene permisos para acceder a esta sección.');
            if (Auth::can('dashboard.view')) {
                $this->redirect('/dashboard');
            } elseif (Auth::can('events.view')) {
                $this->redirect('/eventos');
            } elseif (Auth::can('donations.view')) {
                $this->redirect('/ofrendas');
            } else {
                $this->redirect('/perfil');
            }
            exit;
        }
    }
}
