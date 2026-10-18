<?php
/**
 * Attendance.php — Modelo de Asistencia
 * @package App\Models
 */
namespace App\Modules\Events\Models;

use App\Models\Model;

class Attendance extends Model
{
    protected string $table = 'event_attendance';

    /** Registrar asistencia de un miembro a un evento */
    public function register(int $eventId, ?int $memberId, ?string $guestName, int $checkinBy, bool $viaQr = false): int
    {
        return $this->create([
            'event_id'    => $eventId,
            'member_id'   => $memberId,
            'guest_name'  => $guestName,
            'checkin_by'  => $checkinBy,
            'via_qr'      => (int)$viaQr,
            'checkin_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    /** Verificar si un miembro ya está registrado en un evento */
    public function alreadyCheckedIn(int $eventId, int $memberId): bool
    {
        $result = $this->db->queryOne(
            "SELECT id FROM event_attendance WHERE event_id = ? AND member_id = ? LIMIT 1",
            [$eventId, $memberId]
        );
        return (bool)$result;
    }

    /** Asistencia de un miembro (historial) */
    public function getMemberHistory(int $memberId, int $limit = 20): array
    {
        return $this->db->query(
            "SELECT ea.*, e.title, e.start_datetime
             FROM event_attendance ea
             JOIN events e ON e.id = ea.event_id
             WHERE ea.member_id = ?
             ORDER BY ea.checkin_at DESC LIMIT ?",
            [$memberId, $limit]
        );
    }
}

