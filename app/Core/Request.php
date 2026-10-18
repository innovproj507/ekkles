<?php
/**
 * Request.php — Abstracción de la petición HTTP
 * ------------------------------------------------
 * Encapsula $_GET, $_POST, $_FILES, $_SERVER y $_COOKIE
 * con métodos de acceso seguros.
 *
 * @package App\Core
 */

namespace App\Core;

class Request
{
    /** @var array Parámetros de ruta dinámica ({id}, {uuid}, etc.) */
    private array $routeParams = [];

    /**
     * Obtener el método HTTP de la petición
     */
    public function method(): string
    {
        // Soporte para método sobrescrito via _method en formularios
        if (isset($_POST['_method'])) {
            return strtoupper($_POST['_method']);
        }
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Obtener la URI limpia (sin query string)
     */
    public function uri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Remover el segmento base de la aplicación si existe
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if ($basePath && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        // Remover query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        return '/' . trim($uri, '/') ?: '/';
    }

    /**
     * Obtener un parámetro GET
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Obtener un parámetro POST
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Obtener todos los parámetros POST
     */
    public function all(): array
    {
        return $_POST;
    }

    /**
     * Obtener un parámetro de ruta dinámica
     */
    public function param(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    /**
     * Establecer los parámetros de ruta (llamado por el Router)
     */
    public function setParams(array $params): void
    {
        $this->routeParams = $params;
    }

    /**
     * Obtener un archivo subido
     */
    public function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    /**
     * Verificar si la petición es AJAX
     */
    public function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    /**
     * Verificar si la petición es POST
     */
    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    /**
     * Obtener la IP del cliente
     */
    public function ip(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['HTTP_X_REAL_IP']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '0.0.0.0';
    }

    /**
     * Obtener el User Agent
     */
    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Obtener un parámetro POST con saneamiento básico
     */
    public function input(string $key, mixed $default = null): mixed
    {
        $value = $_POST[$key] ?? $default;
        if (is_string($value)) {
            return trim(htmlspecialchars_decode($value));
        }
        return $value;
    }

    /**
     * Verificar si existe un parámetro en la petición
     */
    public function has(string $key): bool
    {
        return isset($_POST[$key]) || isset($_GET[$key]);
    }

    /**
     * Obtener el token CSRF enviado en el formulario
     */
    public function csrfToken(): ?string
    {
        return $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    }
}
