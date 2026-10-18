<?php
/**
 * public/index.php — Front Controller de Ekkles
 * ------------------------------------------------
 * Punto de entrada único para todas las peticiones HTTP.
 * Apache redirige aquí vía .htaccess.
 *
 * @package Ekkles
 */

declare(strict_types=1);

// Configurar nivel de reporte de errores para evitar que avisos de deprecación o notices
// rompan el envío de cabeceras HTTP y sesiones (especialmente en PHP 8.2+)
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_NOTICE);

// Cabeceras de Seguridad HTTP (Security Headers)
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

// Cargar autoloader de Composer
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Cargar variables de entorno desde .env
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Inicializar y ejecutar la aplicación
try {
    $app = new \App\Core\App();
    $app->run();
} catch (\Throwable $e) {
    if (($_ENV['APP_ENV'] ?? 'production') === 'local') {
        // En entorno local, re-lanzar para ver el stack trace completo
        throw $e;
    } else {
        // En producción, registrar en error_log de PHP y mostrar página amigable
        error_log((string)$e);
        http_response_code(500);
        $code = 500;
        $title = 'Error del Servidor';
        $message = 'Ha ocurrido un error interno en el servidor. Nuestro equipo ha sido notificado.';
        require dirname(__DIR__) . '/app/Views/layouts/error.php';
        exit;
    }
}
