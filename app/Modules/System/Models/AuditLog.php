<?php
/**
 * AuditLog.php — Modelo de Bitácora de Auditoría
 * -------------------------------------------------
 * Registra todas las acciones importantes del sistema.
 * Nunca se eliminan registros de auditoría.
 *
 * @package App\Models
 */

namespace App\Modules\System\Models;

use App\Models\Model;

use App\Core\Auth;
use App\Core\Database;

class AuditLog
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Registrar una acción en la bitácora
     *
     * @param string   $action       Nombre de la acción (ej: 'create', 'update', 'delete', 'login')
     * @param string   $module       Módulo donde ocurrió (ej: 'members', 'finance')
     * @param int|null $userId       ID del usuario que realizó la acción
     * @param string|null $resourceType Tipo de recurso afectado
     * @param int|null $resourceId   ID del recurso afectado
     * @param array|null $oldValues  Valores anteriores (para updates)
     * @param array|null $newValues  Valores nuevos (para creates/updates)
     */
    public static function record(
        string $action,
        string $module,
        ?int $userId = null,
        ?string $resourceType = null,
        ?int $resourceId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        $db       = Database::getInstance();
        $authUser = Auth::user();
        $churchId = Auth::churchId();

        // Si no se pasa userId, usar el autenticado
        if ($userId === null) {
            $userId = Auth::id();
        }

        $db->execute(
            "INSERT INTO `audit_logs`
                (`church_id`, `user_id`, `user_name`, `action`, `module`,
                 `resource_type`, `resource_id`, `old_values`, `new_values`,
                 `ip_address`, `user_agent`)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $churchId,
                $userId,
                $authUser['name'] ?? 'Sistema',
                $action,
                $module,
                $resourceType,
                $resourceId,
                $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                request_ip(),
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            ]
        );
    }

    /**
     * Obtener los últimos N registros de auditoría de una iglesia
     */
    public function getRecent(int $churchId, int $limit = 50): array
    {
        return Database::getInstance()->query(
            "SELECT al.*, u.name as user_name_current
             FROM audit_logs al
             LEFT JOIN users u ON u.id = al.user_id
             WHERE al.church_id = ?
             ORDER BY al.created_at DESC
             LIMIT ?",
            [$churchId, $limit]
        );
    }

    /**
     * Obtener auditoría por módulo y recurso
     */
    public function getByResource(string $resourceType, int $resourceId): array
    {
        return Database::getInstance()->query(
            "SELECT al.*, u.name as user_name_current
             FROM audit_logs al
             LEFT JOIN users u ON u.id = al.user_id
             WHERE al.resource_type = ? AND al.resource_id = ?
             ORDER BY al.created_at DESC",
            [$resourceType, $resourceId]
        );
    }
}

