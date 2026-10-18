<?php
/**
 * AuthController.php — Autenticación de Usuarios
 * ------------------------------------------------
 * Login, logout y verificación MFA.
 *
 * @package App\Controllers
 */

namespace App\Modules\Auth\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Helpers\Validator;

class AuthController extends Controller
{
    /**
     * Mostrar formulario de login
     */
    public function showLogin(Request $request, Response $response): void
    {
        // Si ya está autenticado, ir al dashboard
        if (Auth::check()) {
            $response->redirect('/dashboard');
            return;
        }

        $this->view('Auth::auth/login', [
            'title'        => 'Iniciar Sesión',
            'flash_error'  => Session::getFlash('error'),
            'flash_success'=> Session::getFlash('success'),
        ], 'auth');
    }

    /**
     * Procesar el formulario de login
     */
    public function login(Request $request, Response $response): void
    {
        $this->requireCsrf($request);

        $email    = trim($request->post('email', ''));
        $password = $request->post('password', '');

        // Validación básica
        $validator = Validator::make(
            ['email' => $email, 'password' => $password],
            ['email' => 'required|email', 'password' => 'required']
        );

        if ($validator->fails()) {
            Session::flash('error', 'Complete todos los campos correctamente.');
            $response->redirect('/login');
            return;
        }

        // Obtener church_id (para esta instalación es siempre 1, en multi-tenant vendría del dominio)
        $churchId = (int)($_ENV['DEFAULT_CHURCH_ID'] ?? 1);

        $result = Auth::attempt($email, $password, $churchId);

        if (!$result['success']) {
            Session::flash('error', $result['message']);
            $response->redirect('/login');
            return;
        }

        if ($result['requires_mfa'] ?? false) {
            $response->redirect('/mfa');
            return;
        }

        if (Auth::can('dashboard.view')) {
            $response->redirect('/dashboard');
        } elseif (Auth::can('events.view')) {
            $response->redirect('/eventos');
        } elseif (Auth::can('donations.view')) {
            $response->redirect('/ofrendas');
        } else {
            $response->redirect('/perfil');
        }
    }

    /**
     * Mostrar formulario de verificación MFA
     */
    public function showMfa(Request $request, Response $response): void
    {
        if (!Auth::mfaPending()) {
            $response->redirect('/login');
            return;
        }

        $this->view('Auth::auth/mfa', ['title' => 'Verificación en Dos Pasos'], 'auth');
    }

    /**
     * Verificar código TOTP de MFA
     */
    public function verifyMfa(Request $request, Response $response): void
    {
        $this->requireCsrf($request);

        $code = trim($request->post('code', ''));

        if (empty($code)) {
            Session::flash('error', 'Ingrese el código de verificación.');
            $response->redirect('/mfa');
            return;
        }

        if (!Auth::verifyMfa($code)) {
            Session::flash('error', 'Código inválido o expirado. Intente de nuevo.');
            $response->redirect('/mfa');
            return;
        }

        if (Auth::can('dashboard.view')) {
            $response->redirect('/dashboard');
        } elseif (Auth::can('events.view')) {
            $response->redirect('/eventos');
        } elseif (Auth::can('donations.view')) {
            $response->redirect('/ofrendas');
        } else {
            $response->redirect('/perfil');
        }
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request, Response $response): void
    {
        Auth::logout();
        Session::flash('success', 'Ha cerrado sesión exitosamente.');
        $response->redirect('/login');
    }
}


