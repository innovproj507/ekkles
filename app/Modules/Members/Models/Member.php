<?php
/**
 * Member.php — Modelo de Miembros
 * ---------------------------------
 * Gestión completa del perfil de miembros de la iglesia.
 *
 * @package App\Models
 */

namespace App\Modules\Members\Models;

use App\Models\Model;

class Member extends Model
{
    protected string $table = 'members';

    /**
     * Secciones del listado: condición SQL de cada una.
     * "Miembros" cuenta solo a los convertidos que no son visitas.
     */
    public const SECTIONS = [
        'miembros'       => "m.is_converted = 1 AND m.status <> 'visitante'",
        'no_convertidos' => "m.is_converted = 0 AND m.status <> 'visitante'",
        'visitas'        => "m.status = 'visitante'",
        'todos'          => '1 = 1',
    ];

    /**
     * Cantidad de personas por sección
     *
     * @return array ['miembros' => int, 'no_convertidos' => int, 'visitas' => int, 'todos' => int]
     */
    public function getSectionCounts(int $churchId): array
    {
        $selects = [];
        foreach (self::SECTIONS as $key => $condition) {
            $selects[] = "SUM(CASE WHEN {$condition} THEN 1 ELSE 0 END) AS {$key}";
        }
        $row = $this->db->queryOne(
            'SELECT ' . implode(', ', $selects) . ' FROM members m WHERE m.church_id = ? AND m.deleted_at IS NULL',
            [$churchId]
        );
        return array_map('intval', array_merge(array_fill_keys(array_keys(self::SECTIONS), 0), $row ?? []));
    }

    /**
     * Búsqueda con filtros y paginación
     */
    public function search(int $churchId, array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where  = ['m.church_id = ?', 'm.deleted_at IS NULL'];
        $params = [$churchId];

        if (!empty($filters['section']) && isset(self::SECTIONS[$filters['section']])) {
            $where[] = '(' . self::SECTIONS[$filters['section']] . ')';
        }

        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $where[] = '(m.first_name LIKE ? OR m.last_name LIKE ? OR m.email LIKE ? OR m.document_number LIKE ? OR m.phone LIKE ?)';
            $params = array_merge($params, [$s, $s, $s, $s, $s]);
        }

        if (!empty($filters['status'])) {
            $where[] = 'm.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['ministry_id'])) {
            $where[] = 'm.ministry_id = ?';
            $params[] = $filters['ministry_id'];
        }

        if (!empty($filters['gender'])) {
            $where[] = 'm.gender = ?';
            $params[] = $filters['gender'];
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $total = (int)($this->db->queryOne(
            "SELECT COUNT(*) as total FROM members m WHERE {$whereStr}",
            $params
        )['total'] ?? 0);

        $data = $this->db->query(
            "SELECT m.*, mn.name as ministry_name
             FROM members m
             LEFT JOIN ministries mn ON mn.id = m.ministry_id
             WHERE {$whereStr}
             ORDER BY m.first_name, m.last_name
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return [
            'data'    => $data,
            'total'   => $total,
            'pages'   => (int)ceil($total / $perPage),
            'current' => $page,
            'perPage' => $perPage,
        ];
    }

    /**
     * Obtener perfil completo de un miembro con relaciones
     */
    public function getProfile(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT m.*, mn.name as ministry_name, mn.color as ministry_color,
                    f.name as family_name, f.id as family_id,
                    fm.relationship as family_relationship
             FROM members m
             LEFT JOIN ministries mn ON mn.id = m.ministry_id
             LEFT JOIN family_members fm ON fm.member_id = m.id
             LEFT JOIN families f ON f.id = fm.family_id
             WHERE m.id = ? AND m.deleted_at IS NULL
             LIMIT 1",
            [$id]
        );
    }

    /**
     * Generar el siguiente código de miembro para una iglesia
     */
    public function generateCode(int $churchId): string
    {
        $result = $this->db->queryOne(
            "SELECT MAX(CAST(SUBSTRING(code, 4) AS UNSIGNED)) as max_num
             FROM members WHERE church_id = ?",
            [$churchId]
        );
        $nextNum = (int)($result['max_num'] ?? 0) + 1;
        return 'MBR' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Detectar miembros inactivos (más de N semanas sin asistir)
     */
    public function getInactive(int $churchId, int $weeks = 4): array
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$weeks} weeks"));
        return $this->db->query(
            "SELECT m.*, mn.name as ministry_name
             FROM members m
             LEFT JOIN ministries mn ON mn.id = m.ministry_id
             WHERE m.church_id = ?
               AND m.status = 'activo'
               AND m.deleted_at IS NULL
               AND (m.last_attendance_at IS NULL OR m.last_attendance_at < ?)
             ORDER BY m.last_attendance_at ASC",
            [$churchId, $cutoffDate]
        );
    }

    /**
     * Obtener miembros cuyo cumpleaños es hoy
     */
    public function getBirthdaysToday(int $churchId): array
    {
        return $this->db->query(
            "SELECT * FROM members
             WHERE church_id = ?
               AND deleted_at IS NULL
               AND status IN ('activo', 'nuevo_creyente')
               AND DATE_FORMAT(birth_date, '%m-%d') = DATE_FORMAT(NOW(), '%m-%d')",
            [$churchId]
        );
    }

    /**
     * Estadísticas de membresía por estado
     */
    public function getStatsByStatus(int $churchId): array
    {
        return $this->db->query(
            "SELECT status, COUNT(*) as total
             FROM members
             WHERE church_id = ? AND deleted_at IS NULL
             GROUP BY status",
            [$churchId]
        );
    }

    /**
     * Crecimiento mensual (nuevos miembros por mes, últimos 12 meses)
     */
    public function getMonthlyGrowth(int $churchId): array
    {
        return $this->db->query(
            "SELECT YEAR(membership_date) as year, MONTH(membership_date) as month, COUNT(*) as total
             FROM members
             WHERE church_id = ?
               AND deleted_at IS NULL
               AND membership_date IS NOT NULL
               AND membership_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY YEAR(membership_date), MONTH(membership_date)
             ORDER BY year, month",
            [$churchId]
        );
    }

    /**
     * Actualizar la fecha de última asistencia
     */
    public function updateLastAttendance(int $memberId, string $date): void
    {
        $this->db->execute(
            "UPDATE members SET last_attendance_at = ? WHERE id = ?",
            [$date, $memberId]
        );
    }

    /**
     * Obtener lista para selectores (id, nombre)
     */
    public function getForSelect(int $churchId): array
    {
        return $this->db->query(
            "SELECT id, CONCAT(first_name, ' ', last_name) as full_name, photo, document_number
             FROM members
             WHERE church_id = ? AND deleted_at IS NULL AND status != 'fallecido'
             ORDER BY first_name, last_name",
            [$churchId]
        );
    }
}

