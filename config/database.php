<?php
/**
 * Configuración de la Base de Datos
 * -----------------------------------
 * Parámetros de conexión PDO para MySQL/MariaDB.
 */

return [
    'driver'   => 'mysql',
    'host'     => $_ENV['DB_HOST']     ?? '127.0.0.1',
    'port'     => (int)($_ENV['DB_PORT']     ?? 3306),
    'database' => $_ENV['DB_NAME'] ?? 'ekkles',
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? '',
    'charset'  => $_ENV['DB_CHARSET']  ?? 'utf8mb4',
    'collation'=> 'utf8mb4_unicode_ci',

    // Opciones PDO
    'options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'",
    ],
];
