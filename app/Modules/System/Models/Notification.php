<?php
/**
 * Notification.php — Modelo de Notificaciones
 * @package App\Models
 */
namespace App\Modules\System\Models;

use App\Models\Model;

class Notification extends Model
{
    protected string $table = 'notifications';

    /** Crear notificación interna para un usuario */
    public function notify(int $churchId, ?int $userId, string $title, string $body, array $data = []): int
    {
        return $this->create([
            'church_id' => $churchId,
            'user_id'   => $userId,
            'type'      => 'interna',
            'title'     => $title,
            'body'      => $body,
            'data'      => $data ? json_encode($data) : null,
        ]);
    }

    /** Obtener notificaciones no leídas de un usuario */
    public function getUnread(int $userId, int $limit = 20): array
    {
        return $this->db->query(
            "SELECT * FROM notifications WHERE user_id = ? AND read_at IS NULL
             ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }

    /** Marcar como leídas */
    public function markAllRead(int $userId): void
    {
        $this->db->execute(
            "UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL",
            [$userId]
        );
    }

    /** Contar no leídas */
    public function countUnread(int $userId): int
    {
        return (int)($this->db->queryOne(
            "SELECT COUNT(*) as c FROM notifications WHERE user_id = ? AND read_at IS NULL",
            [$userId]
        )['c'] ?? 0);
    }
}

