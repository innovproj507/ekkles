<?php
/**
 * Event.php — Modelo de Eventos
 * --------------------------------
 * Gestión de cultos, conferencias y eventos de la iglesia.
 *
 * @package App\Models
 */

namespace App\Modules\Events\Models;

use App\Models\Model;

use Ramsey\Uuid\Uuid;

class Event extends Model
{
    protected string $table = 'events';

    /** Duración que se asume para un evento sin hora de fin (para choques de salón y ocupación) */
    public const DEFAULT_DURATION_HOURS = 2;

    /**
     * Eventos que ya ocupan un salón en el horario indicado
     *
     * @param string|null $end Fin del nuevo evento; si falta se asume DEFAULT_DURATION_HOURS
     */
    public function findRoomConflicts(int $churchId, int $roomId, string $start, ?string $end, int $excludeId = 0): array
    {
        $start = date('Y-m-d H:i:s', strtotime($start));
        $end   = $end ? date('Y-m-d H:i:s', strtotime($end))
                      : date('Y-m-d H:i:s', strtotime($start) + self::DEFAULT_DURATION_HOURS * 3600);
        $hours = self::DEFAULT_DURATION_HOURS;

        return $this->db->query(
            "SELECT e.id, e.title, e.start_datetime, e.end_datetime
             FROM events e
             WHERE e.church_id = ? AND e.room_id = ? AND e.id <> ?
               AND e.deleted_at IS NULL AND e.status <> 'cancelado'
               AND e.start_datetime < ?
               AND COALESCE(e.end_datetime, e.start_datetime + INTERVAL {$hours} HOUR) > ?
             ORDER BY e.start_datetime",
            [$churchId, $roomId, $excludeId, $end, $start]
        );
    }

    /**
     * Eventos de un rango de fechas con su salón (para el reporte de ocupación)
     */
    public function getRoomSchedule(int $churchId, string $from, string $to): array
    {
        return $this->db->query(
            "SELECT e.id, e.title, e.start_datetime, e.end_datetime, e.food_contribution, e.room_id,
                    r.name as room_name, et.name as type, et.color as type_color
             FROM events e
             JOIN rooms r ON r.id = e.room_id
             LEFT JOIN event_types et ON et.id = e.event_type_id
             WHERE e.church_id = ? AND e.deleted_at IS NULL AND e.status <> 'cancelado'
               AND e.start_datetime >= ? AND e.start_datetime < ?
             ORDER BY r.name, e.start_datetime",
            [$churchId, $from, $to]
        );
    }

    /**
     * Obtener próximos eventos de una iglesia
     */
    public function getUpcoming(int $churchId, int $limit = 10): array
    {
        return $this->db->query(
            "SELECT e.*, m.name as ministry_name, COUNT(ea.id) as attendance_count, et.name as type, et.color as type_color, r.name as room_name
             FROM events e
             LEFT JOIN ministries m ON m.id = e.ministry_id
             LEFT JOIN event_types et ON et.id = e.event_type_id
             LEFT JOIN rooms r ON r.id = e.room_id
             LEFT JOIN event_attendance ea ON ea.event_id = e.id
             WHERE e.church_id = ?
               AND e.deleted_at IS NULL
               AND e.start_datetime >= NOW()
               AND e.status != 'cancelado'
             GROUP BY e.id
             ORDER BY e.start_datetime ASC
             LIMIT ?",
            [$churchId, $limit]
        );
    }

    /**
     * Obtener eventos del mes actual
     */
    public function getCurrentMonth(int $churchId): array
    {
        return $this->db->query(
            "SELECT e.*, m.name as ministry_name, et.name as type, et.color as type_color, r.name as room_name
             FROM events e
             LEFT JOIN ministries m ON m.id = e.ministry_id
             LEFT JOIN event_types et ON et.id = e.event_type_id
             LEFT JOIN rooms r ON r.id = e.room_id
             WHERE e.church_id = ?
               AND e.deleted_at IS NULL
               AND YEAR(e.start_datetime) = YEAR(NOW())
               AND MONTH(e.start_datetime) = MONTH(NOW())
             ORDER BY e.start_datetime ASC",
            [$churchId]
        );
    }

    /**
     * Obtener eventos por mes y año específico
     */
    public function getEventsByMonth(int $churchId, int $month, int $year): array
    {
        return $this->db->query(
            "SELECT e.*, m.name as ministry_name, m.color as ministry_color, et.name as type, et.color as type_color, r.name as room_name
             FROM events e
             LEFT JOIN ministries m ON m.id = e.ministry_id
             LEFT JOIN event_types et ON et.id = e.event_type_id
             LEFT JOIN rooms r ON r.id = e.room_id
             WHERE e.church_id = ?
               AND e.deleted_at IS NULL
               AND YEAR(e.start_datetime) = ?
               AND MONTH(e.start_datetime) = ?
               AND e.status != 'cancelado'
             ORDER BY e.start_datetime ASC",
            [$churchId, $year, $month]
        );
    }

    /**
     * Obtener evento por UUID (para QR)
     */
    public function findByUuid(string $uuid): ?array
    {
        return $this->db->queryOne(
            "SELECT e.*, m.name as ministry_name, et.name as type, et.color as type_color, r.name as room_name
             FROM events e
             LEFT JOIN ministries m ON m.id = e.ministry_id
             LEFT JOIN event_types et ON et.id = e.event_type_id
             LEFT JOIN rooms r ON r.id = e.room_id
             WHERE e.uuid = ? AND e.deleted_at IS NULL LIMIT 1",
            [$uuid]
        );
    }

    /**
     * Obtener evento por ID con relaciones
     */
    public function findById(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT e.*, m.name as ministry_name, et.name as type, et.color as type_color, r.name as room_name
             FROM events e
             LEFT JOIN ministries m ON m.id = e.ministry_id
             LEFT JOIN event_types et ON et.id = e.event_type_id
             LEFT JOIN rooms r ON r.id = e.room_id
             WHERE e.id = ? AND e.deleted_at IS NULL LIMIT 1",
            [$id]
        );
    }

    /**
     * Crear evento con UUID automático
     */
    public function createEvent(array $data): int
    {
        $data['uuid'] = Uuid::uuid4()->toString();
        return $this->create($data);
    }

    /**
     * Obtener asistencia de un evento
     */
    public function getAttendance(int $eventId): array
    {
        return $this->db->query(
            "SELECT ea.*, m.first_name, m.last_name, m.photo
             FROM event_attendance ea
             LEFT JOIN members m ON m.id = ea.member_id
             WHERE ea.event_id = ?
             ORDER BY ea.checkin_at DESC",
            [$eventId]
        );
    }

    /**
     * Estadísticas de asistencia semanal (últimas 8 semanas)
     */
    public function getWeeklyAttendance(int $churchId): array
    {
        return $this->db->query(
            "SELECT YEARWEEK(e.start_datetime, 1) as week_num,
                    MIN(e.start_datetime) as week_start,
                    COUNT(DISTINCT e.id) as events,
                    COUNT(ea.id) as attendees
             FROM events e
             LEFT JOIN event_types et ON et.id = e.event_type_id
             LEFT JOIN event_attendance ea ON ea.event_id = e.id
             WHERE e.church_id = ?
               AND e.deleted_at IS NULL
               AND e.start_datetime >= DATE_SUB(NOW(), INTERVAL 8 WEEK)
               AND et.name = 'Culto'
             GROUP BY YEARWEEK(e.start_datetime, 1)
             ORDER BY week_num DESC
             LIMIT 8",
            [$churchId]
        );
    }

    /**
     * Obtener equipos de servicio de un evento
     */
    public function getTeams(int $eventId): array
    {
        return $this->db->query(
            "SELECT et.*, m.first_name, m.last_name, m.photo, m.phone
             FROM event_teams et
             JOIN members m ON m.id = et.member_id
             WHERE et.event_id = ?
             ORDER BY et.role, m.first_name",
            [$eventId]
        );
    }

    /**
     * Buscar y paginar eventos
     */
    public function search(int $churchId, array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where  = ['e.church_id = ?', 'e.deleted_at IS NULL'];
        $params = [$churchId];

        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $where[] = '(e.title LIKE ? OR e.description LIKE ?)';
            $params  = array_merge($params, [$s, $s]);
        }

        if (!empty($filters['event_type_id'])) {
            $where[]  = 'e.event_type_id = ?';
            $params[] = $filters['event_type_id'];
        }

        if (!empty($filters['status'])) {
            $where[]  = 'e.status = ?';
            $params[] = $filters['status'];
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $total = (int)($this->db->queryOne(
            "SELECT COUNT(*) as total FROM events e WHERE {$whereStr}", $params
        )['total'] ?? 0);

        $data = $this->db->query(
            "SELECT e.*, m.name as ministry_name, COUNT(ea.id) as attendance_count, et.name as type, et.color as type_color, r.name as room_name
             FROM events e
             LEFT JOIN ministries m ON m.id = e.ministry_id
             LEFT JOIN event_types et ON et.id = e.event_type_id
             LEFT JOIN rooms r ON r.id = e.room_id
             LEFT JOIN event_attendance ea ON ea.event_id = e.id
             WHERE {$whereStr}
             GROUP BY e.id
             ORDER BY e.start_datetime DESC
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return [
            'data' => $data, 'total' => $total,
            'pages' => (int)ceil($total / $perPage), 'current' => $page,
        ];
    }
}

