<?php
/**
 * PastoralFollow.php — Modelo de Seguimiento Pastoral
 * -------------------------------------------------------
 * CRM pastoral: pipeline de visitantes, discipulado y seguimiento.
 *
 * @package App\Models
 */

namespace App\Modules\Pastoral\Models;

use App\Models\Model;

class PastoralFollow extends Model
{
    protected string $table = 'pastoral_follows';

    /**
     * Obtener seguimientos pendientes asignados a un usuario
     */
    public function getPendingByUser(int $userId, int $churchId): array
    {
        return $this->db->query(
            "SELECT pf.*, CONCAT(m.first_name, ' ', m.last_name) as member_name, m.photo
             FROM pastoral_follows pf
             JOIN members m ON m.id = pf.member_id
             WHERE pf.assigned_to = ?
               AND pf.church_id = ?
               AND pf.status IN ('pendiente', 'en_proceso')
               AND pf.deleted_at IS NULL
             ORDER BY pf.priority DESC, pf.scheduled_at ASC",
            [$userId, $churchId]
        );
    }

    /**
     * Obtener el pipeline completo por etapa
     */
    public function getPipelineByStage(int $churchId): array
    {
        $stages = ['visitante', 'contactado', 'integrado', 'discipulado', 'miembro'];
        $result = [];

        foreach ($stages as $stage) {
            $result[$stage] = $this->db->query(
                "SELECT pf.*, CONCAT(m.first_name, ' ', m.last_name) as member_name,
                        m.photo, m.phone, m.email, m.status as member_status,
                        u.name as assigned_to_name
                 FROM pastoral_follows pf
                 JOIN members m ON m.id = pf.member_id
                 LEFT JOIN users u ON u.id = pf.assigned_to
                 WHERE pf.church_id = ?
                   AND pf.pipeline_stage = ?
                   AND pf.status NOT IN ('completado', 'cancelado')
                   AND pf.deleted_at IS NULL
                 ORDER BY pf.priority DESC, pf.created_at DESC",
                [$churchId, $stage]
            );
        }

        return $result;
    }

    /**
     * Seguimientos con tareas vencidas (scheduled_at < NOW())
     */
    public function getOverdue(int $churchId): array
    {
        return $this->db->query(
            "SELECT pf.*, CONCAT(m.first_name, ' ', m.last_name) as member_name,
                    u.name as assigned_to_name
             FROM pastoral_follows pf
             JOIN members m ON m.id = pf.member_id
             LEFT JOIN users u ON u.id = pf.assigned_to
             WHERE pf.church_id = ?
               AND pf.status = 'pendiente'
               AND pf.scheduled_at < NOW()
               AND pf.deleted_at IS NULL
             ORDER BY pf.scheduled_at ASC",
            [$churchId]
        );
    }

    /**
     * Resumen del pipeline (total por etapa)
     */
    public function getPipelineSummary(int $churchId): array
    {
        return $this->db->query(
            "SELECT pipeline_stage, COUNT(*) as total
             FROM pastoral_follows
             WHERE church_id = ?
               AND status NOT IN ('completado', 'cancelado')
               AND deleted_at IS NULL
             GROUP BY pipeline_stage",
            [$churchId]
        );
    }

    /**
     * Obtener historial de seguimientos de un miembro
     */
    public function getMemberHistory(int $memberId): array
    {
        return $this->db->query(
            "SELECT pf.*, u.name as assigned_to_name, cb.name as created_by_name
             FROM pastoral_follows pf
             LEFT JOIN users u ON u.id = pf.assigned_to
             LEFT JOIN users cb ON cb.id = pf.created_by
             WHERE pf.member_id = ? AND pf.deleted_at IS NULL
             ORDER BY pf.created_at DESC",
            [$memberId]
        );
    }

    /**
     * Buscar con paginación
     */
    public function search(int $churchId, array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where  = ['pf.church_id = ?', 'pf.deleted_at IS NULL'];
        $params = [$churchId];

        if (!empty($filters['status'])) {
            $where[]  = 'pf.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['pipeline_stage'])) {
            $where[]  = 'pf.pipeline_stage = ?';
            $params[] = $filters['pipeline_stage'];
        }

        if (!empty($filters['assigned_to'])) {
            $where[]  = 'pf.assigned_to = ?';
            $params[] = $filters['assigned_to'];
        }

        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $where[]  = '(m.first_name LIKE ? OR m.last_name LIKE ? OR pf.subject LIKE ?)';
            $params   = array_merge($params, [$s, $s, $s]);
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $total = (int)($this->db->queryOne(
            "SELECT COUNT(*) as total
             FROM pastoral_follows pf
             JOIN members m ON m.id = pf.member_id
             WHERE {$whereStr}",
            $params
        )['total'] ?? 0);

        $data = $this->db->query(
            "SELECT pf.*, CONCAT(m.first_name, ' ', m.last_name) as member_name,
                    m.photo, u.name as assigned_to_name
             FROM pastoral_follows pf
             JOIN members m ON m.id = pf.member_id
             LEFT JOIN users u ON u.id = pf.assigned_to
             WHERE {$whereStr}
             ORDER BY pf.created_at DESC
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return [
            'data' => $data, 'total' => $total,
            'pages' => (int)ceil($total / $perPage), 'current' => $page,
        ];
    }
}

