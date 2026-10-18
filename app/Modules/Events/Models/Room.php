<?php
/**
 * Room.php — Modelo de Salones
 * ------------------------------
 * Salones físicos de la iglesia donde se realizan los eventos.
 *
 * @package App\Models
 */

namespace App\Modules\Events\Models;

use App\Models\Model;

class Room extends Model
{
    protected string $table = 'rooms';

    /**
     * Todos los salones (activos e inactivos) con la cantidad de eventos asignados
     */
    public function getAll(int $churchId): array
    {
        return $this->db->query(
            "SELECT r.*, COUNT(e.id) as events_count
             FROM rooms r
             LEFT JOIN events e ON e.room_id = r.id AND e.deleted_at IS NULL
             WHERE r.church_id = ? AND r.deleted_at IS NULL
             GROUP BY r.id
             ORDER BY r.name",
            [$churchId]
        );
    }

    /**
     * Salones activos para los selectores
     */
    public function getActive(int $churchId): array
    {
        return $this->db->query(
            "SELECT id, name, capacity FROM rooms
             WHERE church_id = ? AND is_active = 1 AND deleted_at IS NULL
             ORDER BY name",
            [$churchId]
        );
    }

    /**
     * Ocupación de cada salón en un rango de fechas: eventos y horas reservadas
     */
    public function getOccupancy(int $churchId, string $from, string $to): array
    {
        return $this->db->query(
            "SELECT r.id, r.name, r.capacity,
                    COUNT(e.id) as events_count,
                    COALESCE(SUM(TIMESTAMPDIFF(MINUTE, e.start_datetime,
                        COALESCE(e.end_datetime, e.start_datetime + INTERVAL " . \App\Modules\Events\Models\Event::DEFAULT_DURATION_HOURS . " HOUR))), 0) / 60 as hours
             FROM rooms r
             LEFT JOIN events e ON e.room_id = r.id
                  AND e.deleted_at IS NULL AND e.status <> 'cancelado'
                  AND e.start_datetime >= ? AND e.start_datetime < ?
             WHERE r.church_id = ? AND r.deleted_at IS NULL
             GROUP BY r.id
             ORDER BY r.name",
            [$from, $to, $churchId]
        );
    }
}
