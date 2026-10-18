<?php
/**
 * Response.php — Helper de respuestas HTTP
 * ------------------------------------------
 * Métodos para redirigir, abortar con error y enviar JSON.
 *
 * @package App\Core
 */

namespace App\Core;

class Response
{
    /**
     * Redirigir a otra URL
     */
    public function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Redirigir a la página anterior (Referer)
     */
    public function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/dashboard';
        $this->redirect($referer);
    }

    /**
     * Responder con JSON
     */
    public function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Abortar con una página de error
     */
    public function abort(int $code, string $message = ''): void
    {
        http_response_code($code);

        $titles = [
            400 => 'Solicitud Incorrecta',
            401 => 'No Autorizado',
            403 => 'Acceso Denegado',
            404 => 'Página No Encontrada',
            500 => 'Error Interno del Servidor',
        ];

        $title = $titles[$code] ?? 'Error';

        require dirname(__DIR__) . '/Views/layouts/error.php';
        exit;
    }

    /**
     * Establecer el código de estado HTTP
     */
    public function setStatus(int $code): self
    {
        http_response_code($code);
        return $this;
    }

    /**
     * Agregar una cabecera HTTP
     */
    public function header(string $name, string $value): self
    {
        header("{$name}: {$value}");
        return $this;
    }

    /**
     * Forzar la descarga de un archivo
     */
    public function download(string $filePath, string $fileName): void
    {
        if (!file_exists($filePath)) {
            $this->abort(404, 'Archivo no encontrado.');
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}
