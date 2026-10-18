<?php
/**
 * App.php — Núcleo del Framework MVC Ekkles
 * -------------------------------------------
 * Bootstrap de la aplicación: carga config, rutas,
 * middlewares y despacha la petición al controlador.
 *
 * @package App\Core
 */

namespace App\Core;

class App
{
    private Router $router;

    public function __construct()
    {
        // Iniciar sesión PHP
        Session::start();

        // Cargar configuración global
        $this->loadConfig();

        // Instanciar router
        $this->router = new Router();
    }

    /**
     * Registrar todas las rutas desde routes/web.php
     */
    private function registerRoutes(): void
    {
        $router = $this->router;
        require dirname(__DIR__, 2) . '/routes/web.php';
    }

    /**
     * Cargar archivos de configuración en $_ENV
     */
    private function loadConfig(): void
    {
        // Configuraciones disponibles globalmente
        define('APP_CONFIG',   require dirname(__DIR__, 2) . '/config/app.php');
        define('ROLES_CONFIG', require dirname(__DIR__, 2) . '/config/roles.php');

        // Configurar zona horaria
        date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/Panama');

        // Nivel de error según entorno
        if (($_ENV['APP_ENV'] ?? 'production') === 'local') {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
    }

    /**
     * Ejecutar la aplicación
     */
    public function run(): void
    {
        $this->registerRoutes();

        $request  = new Request();
        $response = new Response();

        $this->router->dispatch($request, $response);
    }
}
