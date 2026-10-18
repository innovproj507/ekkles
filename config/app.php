<?php
/**
 * Configuración General de la Aplicación Ekkles
 * -----------------------------------------------
 * Carga las variables de entorno y define constantes globales.
 */

// Cargar variables del archivo .env
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

return [
    // Entorno de ejecución
    'env'      => $_ENV['APP_ENV']      ?? 'production',
    'name'     => $_ENV['APP_NAME']     ?? 'Ekkles',
    'url'      => $_ENV['APP_URL']      ?? 'http://localhost',
    'key'      => $_ENV['APP_KEY']      ?? '',
    'debug'    => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'America/Panama',
    'locale'   => $_ENV['APP_LOCALE']   ?? 'es',

    // Configuración de sesiones
    'session' => [
        'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 120),
        'secure'   => filter_var($_ENV['SESSION_SECURE']   ?? false, FILTER_VALIDATE_BOOLEAN),
        'httponly' => filter_var($_ENV['SESSION_HTTPONLY']  ?? true,  FILTER_VALIDATE_BOOLEAN),
    ],

    // Subida de archivos
    'upload' => [
        'max_size' => (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 5242880), // 5MB
        'path'     => $_ENV['UPLOAD_PATH'] ?? '../public/uploads',
        'allowed'  => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    ],

    // Multi-tenant: ID de iglesia por defecto
    'default_church_id' => (int)($_ENV['DEFAULT_CHURCH_ID'] ?? 1),

    // Versión del sistema
    'version' => '1.0.0',

    // Paginación por defecto
    'per_page' => 25,

    // Correo
    'mail' => [
        'driver'     => $_ENV['MAIL_DRIVER']       ?? 'smtp',
        'host'       => $_ENV['MAIL_HOST']          ?? 'localhost',
        'port'       => (int)($_ENV['MAIL_PORT']    ?? 587),
        'username'   => $_ENV['MAIL_USERNAME']      ?? '',
        'password'   => $_ENV['MAIL_PASSWORD']      ?? '',
        'encryption' => $_ENV['MAIL_ENCRYPTION']    ?? 'tls',
        'from' => [
            'address' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@ekkles.app',
            'name'    => $_ENV['MAIL_FROM_NAME']    ?? 'Ekkles',
        ],
    ],
];
