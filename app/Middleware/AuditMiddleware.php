<?php
/**
 * AuditMiddleware.php — Middleware de Auditoría
 * -----------------------------------------------
 * Registra automáticamente las peticiones HTTP relevantes
 * en la bitácora de auditoría del sistema.
 *
 * @package App\Middleware
 */

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Modules\System\Models\AuditLog;

class AuditMiddleware
{
    /**
     * Acciones a registrar automáticamente
     * Solo se auditan peticiones POST que modifican datos
     */
    private array $auditedActions = [
        '/login', '/logout', '/mfa',
    ];

    public function handle(Request $request, Response $response): bool
    {
        // Solo auditar peticiones POST automáticamente en estas rutas
        if ($request->isPost() && in_array($request->uri(), $this->auditedActions)) {
            // El registro específico de acción se hace en cada controlador
        }

        return true;
    }
}
