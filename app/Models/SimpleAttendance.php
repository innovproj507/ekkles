<?php

namespace App\Models;

class SimpleAttendance extends Model
{
    protected string $table = 'simple_attendances';
    protected array $fillable = [
        'church_id',
        'date',
        'member_id',
        'registered_name',
        'adults_count',
        'children_count',
        'guests_count'
    ];

    /**
     * Get attendance aggregated by date for a church
     */
    public function getAggregatedByDate(int $churchId, string $startDate, string $endDate): array
    {
        return $this->db->query(
            "SELECT 
                date,
                SUM(adults_count) as adults,
                SUM(children_count) as children,
                SUM(guests_count) as guests,
                SUM(adults_count + children_count + guests_count) as total
             FROM {$this->table}
             WHERE church_id = ? AND date >= ? AND date <= ?
             GROUP BY date
             ORDER BY date DESC",
            [$churchId, $startDate, $endDate]
        );
    }
    
    /**
     * Get today's totals
     */
    public function getTodayTotals(int $churchId): array
    {
        $today = date('Y-m-d');
        $res = $this->db->queryOne(
            "SELECT 
                SUM(adults_count) as adults,
                SUM(children_count) as children,
                SUM(guests_count) as guests,
                SUM(adults_count + children_count + guests_count) as total
             FROM {$this->table}
             WHERE church_id = ? AND date = ?",
            [$churchId, $today]
        );
        
        return [
            'adults' => (int)($res['adults'] ?? 0),
            'children' => (int)($res['children'] ?? 0),
            'guests' => (int)($res['guests'] ?? 0),
            'total' => (int)($res['total'] ?? 0),
        ];
    }

    /**
     * Get recent individual records
     */
    public function getRecentRecords(int $churchId, int $limit = 100): array
    {
        return $this->db->query(
            "SELECT 
                a.*,
                m.first_name, 
                m.last_name
             FROM {$this->table} a
             LEFT JOIN members m ON m.id = a.member_id
             WHERE a.church_id = ?
             ORDER BY a.created_at DESC
             LIMIT ?",
            [$churchId, $limit]
        );
    }

    /**
     * Registro existente del día para un miembro o, si no hay miembro, para un nombre
     */
    public function findForDay(int $churchId, string $date, ?int $memberId, string $registeredName): ?array
    {
        $name = trim(preg_replace('/\s+/u', ' ', $registeredName));

        if ($memberId) {
            return $this->db->queryOne(
                "SELECT * FROM {$this->table}
                 WHERE church_id = ? AND date = ? AND (member_id = ? OR LOWER(TRIM(registered_name)) = LOWER(?))
                 LIMIT 1",
                [$churchId, $date, $memberId, $name]
            );
        }

        if ($name === '') {
            return null;
        }

        return $this->db->queryOne(
            "SELECT * FROM {$this->table}
             WHERE church_id = ? AND date = ? AND LOWER(TRIM(registered_name)) = LOWER(?)
             LIMIT 1",
            [$churchId, $date, $name]
        );
    }

    /**
     * Override find to ignore deleted_at since simple_attendances doesn't have it
     */
    public function find(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1",
            [$id]
        );
    }

    /**
     * Override delete to hard delete since simple_attendances doesn't have deleted_at
     */
    public function delete(int $id): int
    {
        return $this->db->execute(
            "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?",
            [$id]
        );
    }
}
