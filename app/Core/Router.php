<?php
/**
 * Router.php — Enrutador HTTP
 * ----------------------------
 * Registra y despacha rutas HTTP (GET, POST) hacia controladores.
 * Soporta parámetros dinámicos ({id}, {uuid}, etc.) y middleware.
 *
 * @package App\Core
 */

namespace App\Core;

class Router
{
    /** @var array Lista de rutas registradas */
    private array $routes = [];

    /**
     * Registrar una ruta GET
     */
    public function get(string $path, string $action, array $middleware = []): void
    {
        $this->add('GET', $path, $action, $middleware);
    }

    /**
     * Registrar una ruta POST
     */
    public function post(string $path, string $action, array $middleware = []): void
    {
        $this->add('POST', $path, $action, $middleware);
    }

    /**
     * Agregar una ruta a la tabla de rutas
     */
    private function add(string $method, string $path, string $action, array $middleware): void
    {
        // Convertir {param} en expresión regex nombrada
        $pattern = preg_replace('/\{([a-z_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method'     => $method,
            'path'       => $path,
            'pattern'    => $pattern,
            'action'     => $action,
            'middleware' => $middleware,
        ];
    }

    /**
     * Despachar la petición HTTP actual
     */
    public function dispatch(Request $request, Response $response): void
    {
        $method = $request->method();
        $uri    = $request->uri();

        foreach ($this->routes as $route) {
            // Verificar método HTTP
            if ($route['method'] !== $method) {
                continue;
            }

            // Verificar si el URI coincide con el patrón
            if (!preg_match($route['pattern'], $uri, $matches)) {
                continue;
            }

            // Extraer parámetros dinámicos (excluir índices numéricos)
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $request->setParams($params);

            // Ejecutar middleware
            if (!$this->runMiddleware($route['middleware'], $request, $response)) {
                return; // Middleware detuvo la ejecución
            }

            // Resolver controlador y método
            [$controllerName, $methodName] = explode('@', $route['action']);
            
            if (str_contains($controllerName, '\\')) {
                // Sintaxis modular: 'Finance\DonationController@index' -> App\Modules\Finance\Controllers\DonationController
                $parts = explode('\\', $controllerName);
                $module = array_shift($parts);
                $class  = implode('\\', $parts);
                $controllerClass = "App\\Modules\\{$module}\\Controllers\\{$class}";
            } else {
                // Compatibilidad hacia atrás
                $controllerClass = "App\\Controllers\\{$controllerName}";
            }

            if (!class_exists($controllerClass)) {
                $response->abort(500, "Controlador {$controllerClass} no encontrado.");
                return;
            }

            $controller = new $controllerClass();

            if (!method_exists($controller, $methodName)) {
                $response->abort(500, "Método {$methodName} no encontrado en {$controllerClass}.");
                return;
            }

            $controller->$methodName($request, $response);
            return;
        }

        // Ninguna ruta coincidió
        $response->abort(404, 'Página no encontrada.');
    }

    /**
     * Ejecutar middleware asociado a la ruta
     *
     * @return bool true si todos los middleware pasan, false si alguno detiene
     */
    private function runMiddleware(array $middleware, Request $request, Response $response): bool
    {
        foreach ($middleware as $mw) {
            // Formato: 'auth' o 'role:permiso.accion'
            if ($mw === 'auth') {
                $handler = new \App\Middleware\AuthMiddleware();
            } elseif (str_starts_with($mw, 'role:')) {
                $permission = substr($mw, 5);
                $handler = new \App\Middleware\RoleMiddleware($permission);
            } else {
                continue;
            }

            if (!$handler->handle($request, $response)) {
                return false;
            }
        }

        return true;
    }
}
