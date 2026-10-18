<?php
/**
 * Ministry.php — Modelo de Ministerios
 * ---------------------------------------
 * Gestión de ministerios y equipos de la iglesia.
 *
 * @package App\Models
 */

namespace App\Modules\Events\Models;

use App\Models\Model;

class Ministry extends Model
{
    protected string $table = 'ministries';

    /**
     * Obtener ministerios activos con conteo de miembros
     */
    public function getActiveWithCount(int $churchId): array
    {
        return $this->db->query(
            "SELECT mn.*, COUNT(mm.id) as member_count,
                    CONCAT(m.first_name, ' ', m.last_name) as leader_name
             FROM ministries mn
             LEFT JOIN ministry_members mm ON mm.ministry_id = mn.id AND mm.is_active = 1
             LEFT JOIN members m ON m.id = mn.leader_id
             WHERE mn.church_id = ? AND mn.deleted_at IS NULL AND mn.is_active = 1
             GROUP BY mn.id
             ORDER BY mn.name ASC",
            [$churchId]
        );
    }

    /**
     * Obtener miembros de un ministerio
     */
    public function getMembers(int $ministryId): array
    {
        return $this->db->query(
            "SELECT mm.*, m.first_name, m.last_name, m.photo, m.phone, m.email, m.status
             FROM ministry_members mm
             JOIN members m ON m.id = mm.member_id
             WHERE mm.ministry_id = ? AND mm.is_active = 1
             ORDER BY m.first_name, m.last_name",
            [$ministryId]
        );
    }

    /**
     * Agregar un miembro a un ministerio
     */
    public function addMember(int $ministryId, int $memberId, string $role = 'miembro'): void
    {
        // Verificar si ya existe (puede estar inactivo)
        $existing = $this->db->queryOne(
            "SELECT id, is_active FROM ministry_members WHERE ministry_id = ? AND member_id = ?",
            [$ministryId, $memberId]
        );

        if ($existing) {
            $this->db->execute(
                "UPDATE ministry_members SET is_active = 1, role = ?, left_at = NULL WHERE id = ?",
                [$role, $existing['id']]
            );
        } else {
            $this->db->execute(
                "INSERT INTO ministry_members (ministry_id, member_id, role, joined_at) VALUES (?, ?, ?, CURDATE())",
                [$ministryId, $memberId, $role]
            );
        }
    }

    /**
     * Remover un miembro de un ministerio (soft)
     */
    public function removeMember(int $ministryId, int $memberId): void
    {
        $this->db->execute(
            "UPDATE ministry_members SET is_active = 0, left_at = CURDATE() WHERE ministry_id = ? AND member_id = ?",
            [$ministryId, $memberId]
        );
    }

    /**
     * Para selectores (id, nombre, color)
     */
    public function getForSelect(int $churchId): array
    {
        return $this->db->query(
            "SELECT id, name, color, icon FROM ministries
             WHERE church_id = ? AND deleted_at IS NULL AND is_active = 1
             ORDER BY name ASC",
            [$churchId]
        );
    }
}

