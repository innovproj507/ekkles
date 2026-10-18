<?php
/**
 * Session.php — Manejo de sesiones seguras
 * ------------------------------------------
 * Gestiona el inicio de sesión, mensajes flash y tokens CSRF.
 *
 * @package App\Core
 */

namespace App\Core;

class Session
{
    /**
     * Iniciar la sesión con configuración segura
     */
    public static function start(array $config = []): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Prevenir fijación de sesión (PHP 5.5.2+)
        ini_set('session.use_strict_mode', '1');

        // Detectar si la conexión es HTTPS para asegurar la cookie automáticamente
        $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

        // Configurar cookies de sesión de forma segura
        session_set_cookie_params([
            'lifetime' => ($config['lifetime'] ?? 120) * 60,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $config['secure'] ?? $isSecure,
            'httponly' => $config['httponly'] ?? true,
            'samesite' => 'Lax',
        ]);

        session_name('EKKLES_SESSION');
        session_start();

        // Regenerar ID de sesión para prevenir fijación de sesión
        if (!self::has('_session_started')) {
            session_regenerate_id(true);
            self::set('_session_started', true);
        }
    }

    /**
     * Establecer un valor en la sesión
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Obtener un valor de la sesión
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Verificar si existe una clave en la sesión
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Eliminar un valor de la sesión
     */
    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Destruir la sesión completamente
     */
    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }

    /**
     * Establecer un mensaje flash (disponible solo en la próxima petición)
     */
    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Obtener y eliminar un mensaje flash
     */
    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    /**
     * Verificar si existe un mensaje flash
     */
    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    /**
     * Generar un token CSRF y guardarlo en sesión
     */
    public static function generateCsrfToken(): string
    {
        if (!self::has('_csrf_token')) {
            self::set('_csrf_token', bin2hex(random_bytes(32)));
        }
        return self::get('_csrf_token');
    }

    /**
     * Verificar el token CSRF
     */
    public static function verifyCsrfToken(string $token): bool
    {
        $sessionToken = self::get('_csrf_token', '');
        return hash_equals($sessionToken, $token);
    }

    /**
     * Obtener todos los datos de sesión (excepto internos)
     */
    public static function all(): array
    {
        return array_filter($_SESSION, fn($key) => !str_starts_with($key, '_'), ARRAY_FILTER_USE_KEY);
    }
}
