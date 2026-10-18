<?php
/**
 * View.php — Motor de Vistas
 * ---------------------------
 * Renderiza archivos PHP como vistas, soportando layouts y variables.
 *
 * @package App\Core
 */

namespace App\Core;

class View
{
    /** @var string Ruta base de las vistas */
    private static string $viewsPath = '';

    /**
     * Renderizar una vista con variables opcionales
     *
     * @param string $view  Ruta relativa de la vista (ej: 'dashboard/index')
     * @param array  $data  Variables disponibles en la vista
     * @param string $layout Layout a usar (ej: 'app' o 'auth')
     */
    public static function render(string $view, array $data = [], string $layout = 'app'): void
    {
        // Extraer variables para que estén disponibles en la vista
        extract($data, EXTR_SKIP);

        $baseViewsPath = dirname(__DIR__) . '/Views/';
        
        // Soporte modular: 'Finance::donations/index'
        if (str_contains($view, '::')) {
            [$module, $viewPath] = explode('::', $view);
            $viewsPath = dirname(__DIR__) . '/Modules/' . $module . '/Views/';
            $viewFile  = $viewsPath . str_replace('.', '/', $viewPath) . '.php';
        } else {
            $viewsPath = $baseViewsPath;
            $viewFile  = $viewsPath . str_replace('.', '/', $view) . '.php';
        }

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("Vista no encontrada: {$viewFile}");
        }

        // Capturar el contenido de la vista
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Renderizar dentro del layout
        $layoutFile = $baseViewsPath . 'layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Renderizar una vista parcial (sin layout)
     *
     * @param string $partial  Ruta relativa del parcial (ej: 'partials/alerts')
     * @param array  $data     Variables disponibles en el parcial
     */
    public static function partial(string $partial, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $baseViewsPath = dirname(__DIR__) . '/Views/';
        
        if (str_contains($partial, '::')) {
            [$module, $partialPath] = explode('::', $partial);
            $viewsPath = dirname(__DIR__) . '/Modules/' . $module . '/Views/';
            $partialFile = $viewsPath . str_replace('.', '/', $partialPath) . '.php';
        } else {
            $viewsPath = $baseViewsPath;
            $partialFile = $viewsPath . str_replace('.', '/', $partial) . '.php';
        }

        if (!file_exists($partialFile)) {
            throw new \RuntimeException("Parcial no encontrado: {$partialFile}");
        }

        require $partialFile;
    }

    /**
     * Renderizar una vista como string (para emails, etc.)
     */
    public static function make(string $view, array $data = []): string
    {
        extract($data, EXTR_SKIP);

        $baseViewsPath = dirname(__DIR__) . '/Views/';
        
        if (str_contains($view, '::')) {
            [$module, $viewPath] = explode('::', $view);
            $viewsPath = dirname(__DIR__) . '/Modules/' . $module . '/Views/';
            $viewFile  = $viewsPath . str_replace('.', '/', $viewPath) . '.php';
        } else {
            $viewsPath = $baseViewsPath;
            $viewFile  = $viewsPath . str_replace('.', '/', $view) . '.php';
        }

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("Vista no encontrada: {$viewFile}");
        }

        ob_start();
        require $viewFile;
        return ob_get_clean();
    }

    /**
     * Escapar HTML para prevenir XSS
     */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
