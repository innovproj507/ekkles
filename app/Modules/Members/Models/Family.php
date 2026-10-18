<?php
/**
 * Family.php — Modelo de Familias
 * @package App\Models
 */
namespace App\Modules\Members\Models;

use App\Models\Model;

class Family extends Model
{
    protected string $table = 'families';

    public function getWithMembers(int $id): ?array
    {
        $family = $this->db->queryOne(
            "SELECT f.*, CONCAT(m.first_name, ' ', m.last_name) as head_name
             FROM families f LEFT JOIN members m ON m.id = f.head_id
             WHERE f.id = ? AND f.deleted_at IS NULL LIMIT 1",
            [$id]
        );
        if (!$family) return null;

        $family['members'] = $this->db->query(
            "SELECT fm.*, m.first_name, m.last_name, m.photo, m.status, m.is_converted, m.phone, m.birth_date
             FROM family_members fm
             JOIN members m ON m.id = fm.member_id
             WHERE fm.family_id = ? ORDER BY fm.relationship",
            [$id]
        );
        return $family;
    }

    /**
     * Secciones del listado de familias (condición sobre los agregados de cada familia).
     * Pareja = familia con al menos dos cónyuges.
     */
    public const SECTIONS = [
        'todas'          => '1 = 1',
        'parejas'        => 'spouse_count >= 2',
        'no_convertidos' => 'spouse_count >= 2 AND unconverted_count > 0',
    ];

    /** Subconsulta con los agregados de integrantes por familia */
    private function aggregatedSql(): string
    {
        return "SELECT f.*, CONCAT(h.first_name, ' ', h.last_name) as head_name,
                       COUNT(m.id) as member_count,
                       SUM(fm.relationship = 'conyuge') as spouse_count,
                       SUM(m.is_converted = 0 AND m.status <> 'visitante') as unconverted_count,
                       GROUP_CONCAT(CONCAT(m.first_name, ' ', m.last_name) ORDER BY m.first_name SEPARATOR ', ') as member_names,
                       GROUP_CONCAT(CASE WHEN m.is_converted = 0 AND m.status <> 'visitante'
                                         THEN CONCAT(m.first_name, ' ', m.last_name) END SEPARATOR ', ') as unconverted_names
                FROM families f
                LEFT JOIN members h ON h.id = f.head_id
                LEFT JOIN family_members fm ON fm.family_id = f.id
                LEFT JOIN members m ON m.id = fm.member_id AND m.deleted_at IS NULL
                WHERE f.church_id = ? AND f.deleted_at IS NULL
                GROUP BY f.id";
    }

    /**
     * Cantidad de familias por sección
     */
    public function getSectionCounts(int $churchId): array
    {
        $selects = [];
        foreach (self::SECTIONS as $key => $condition) {
            $selects[] = "SUM(CASE WHEN {$condition} THEN 1 ELSE 0 END) AS {$key}";
        }
        $row = $this->db->queryOne(
            'SELECT ' . implode(', ', $selects) . ' FROM (' . $this->aggregatedSql() . ') x',
            [$churchId]
        );
        return array_map('intval', array_merge(array_fill_keys(array_keys(self::SECTIONS), 0), $row ?? []));
    }

    public function getByChurch(int $churchId, int $page = 1, int $perPage = 25, string $section = 'todas'): array
    {
        $condition = self::SECTIONS[$section] ?? self::SECTIONS['todas'];
        $offset = ($page - 1) * $perPage;
        $base   = 'SELECT * FROM (' . $this->aggregatedSql() . ") x WHERE {$condition}";

        $total = (int)($this->db->queryOne("SELECT COUNT(*) as t FROM ({$base}) y", [$churchId])['t'] ?? 0);

        $data = $this->db->query("{$base} ORDER BY name ASC LIMIT ? OFFSET ?", [$churchId, $perPage, $offset]);

        return ['data' => $data, 'total' => $total, 'pages' => (int)ceil($total / $perPage), 'current' => $page];
    }
}

