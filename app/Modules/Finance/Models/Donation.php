<?php
/**
 * Donation.php — Modelo de Donaciones
 * --------------------------------------
 * Ofrendas y donaciones. Ningún registro se elimina físicamente.
 *
 * @package App\Models
 */

namespace App\Modules\Finance\Models;

use App\Models\Model;

use Ramsey\Uuid\Uuid;

class Donation extends Model
{
    protected string $table = 'donations';

    /**
     * Generar siguiente número de recibo
     */
    public function generateReceiptNumber(int $churchId): string
    {
        $result = $this->db->queryOne(
            "SELECT value FROM settings WHERE church_id = ? AND `key` = 'receipt_counter'",
            [$churchId]
        );
        $prefix = $this->db->queryOne(
            "SELECT value FROM settings WHERE church_id = ? AND `key` = 'receipt_prefix'",
            [$churchId]
        );

        $counter = (int)($result['value'] ?? 1);
        $prefix  = $prefix['value'] ?? 'REC';

        // Incrementar el contador
        $this->db->execute(
            "UPDATE settings SET value = ? WHERE church_id = ? AND `key` = 'receipt_counter'",
            [$counter + 1, $churchId]
        );

        return $prefix . '-' . date('Y') . '-' . str_pad($counter, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Crear donación con UUID y número de recibo automáticos
     */
    public function createDonation(array $data, int $churchId): int
    {
        $data['uuid'] = Uuid::uuid4()->toString();
        if (empty($data['receipt_number'])) {
            $data['receipt_number'] = $this->generateReceiptNumber($churchId);
        }
        
        $date = $data['donation_date'] ?? date('Y-m-d');
        $data['period_month']   = (int)date('n', strtotime($date));
        $data['period_year']    = (int)date('Y', strtotime($date));
        return $this->create($data);
    }

    /**
     * Encontrar una donación por ID con sus relaciones
     */
    public function find(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT d.*, fc.name as category_name,
                    COALESCE(CONCAT(m.first_name, ' ', m.last_name), d.donor_name) as full_donor_name,
                    m.photo as member_photo
             FROM donations d
             LEFT JOIN members m ON m.id = d.member_id
             LEFT JOIN finance_categories fc ON fc.id = d.category_id
             WHERE d.id = ?",
            [$id]
        );
    }

    /**
     * Obtener donaciones con información del donante y categoría
     */
    public function search(int $churchId, array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where  = ['d.church_id = ?'];
        $params = [$churchId];

        if (!empty($filters['type'])) {
            if (is_array($filters['type'])) {
                $placeholders = implode(',', array_fill(0, count($filters['type']), '?'));
                $where[]  = "d.type IN ($placeholders)";
                $params   = array_merge($params, $filters['type']);
            } else {
                $where[]  = 'd.type = ?';
                $params[] = $filters['type'];
            }
        }

        if (!empty($filters['category_id'])) {
            $where[]  = 'd.category_id = ?';
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['member_id'])) {
            $where[]  = 'd.member_id = ?';
            $params[] = $filters['member_id'];
        }

        if (!empty($filters['month'])) {
            $where[]  = 'd.period_month = ?';
            $params[] = $filters['month'];
        }

        if (!empty($filters['year'])) {
            $where[]  = 'd.period_year = ?';
            $params[] = $filters['year'];
        }

        if (!empty($filters['status'])) {
            $where[]  = 'd.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $where[]  = '(d.receipt_number LIKE ? OR d.donor_name LIKE ? OR m.first_name LIKE ? OR m.last_name LIKE ? OR d.ruc LIKE ? OR d.detail LIKE ?)';
            $params   = array_merge($params, [$s, $s, $s, $s, $s, $s]);
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $total = (int)($this->db->queryOne(
            "SELECT COUNT(*) as total FROM donations d LEFT JOIN members m ON m.id = d.member_id WHERE {$whereStr}",
            $params
        )['total'] ?? 0);

        $data = $this->db->query(
            "SELECT d.*, fc.name as category_name,
                    COALESCE(CONCAT(m.first_name, ' ', m.last_name), d.donor_name) as full_donor_name,
                    m.photo as member_photo
             FROM donations d
             LEFT JOIN members m ON m.id = d.member_id
             LEFT JOIN finance_categories fc ON fc.id = d.category_id
             WHERE {$whereStr}
             ORDER BY d.donation_date DESC, d.id DESC
             LIMIT ? OFFSET ?",
             [...$params, $perPage, $offset]
        );

        return [
            'data' => $data, 'total' => $total,
            'pages' => (int)ceil($total / $perPage), 'current' => $page,
        ];
    }

    /**
     * Total de donaciones del mes actual
     */
    public function getCurrentMonthTotal(int $churchId, mixed $type = null): float
    {
        $params = [
            $churchId,
            (int)date('n'),
            (int)date('Y'),
        ];
        $extra = '';
        if ($type) {
            if (is_array($type)) {
                $placeholders = implode(',', array_fill(0, count($type), '?'));
                $extra    = " AND type IN ($placeholders)";
                $params   = array_merge($params, $type);
            } else {
                $extra    = ' AND type = ?';
                $params[] = $type;
            }
        }

        $result = $this->db->queryOne(
            "SELECT SUM(amount) as total FROM donations
             WHERE church_id = ? AND period_month = ? AND period_year = ? AND status = 'aprobado'{$extra}",
            $params
        );

        return (float)($result['total'] ?? 0);
    }

    /**
     * Historial de donaciones de un miembro
     */
    public function getMemberHistory(int $memberId): array
    {
        return $this->db->query(
            "SELECT * FROM donations
             WHERE member_id = ?
             ORDER BY created_at DESC",
            [$memberId]
        );
    }

    /**
     * Resumen mensual (últimos 12 meses)
     */
    public function getMonthlySummary(int $churchId): array
    {
        return $this->db->query(
            "SELECT period_year, period_month, type, SUM(amount) as total, COUNT(*) as count
             FROM donations
             WHERE church_id = ?
               AND status = 'aprobado'
               AND STR_TO_DATE(CONCAT(period_year, '-', period_month, '-01'), '%Y-%m-%d') >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY period_year, period_month, type
             ORDER BY period_year DESC, period_month DESC",
            [$churchId]
        );
    }

    /**
     * Top donantes del mes
     */
    public function getTopDonors(int $churchId, int $month, int $year, int $limit = 10): array
    {
        return $this->db->query(
            "SELECT d.member_id, d.donor_name,
                    COALESCE(CONCAT(m.first_name, ' ', m.last_name), d.donor_name) as full_name,
                    SUM(d.amount) as total_donated, COUNT(d.id) as donation_count
             FROM donations d
             LEFT JOIN members m ON m.id = d.member_id
             WHERE d.church_id = ? AND d.period_month = ? AND d.period_year = ?
               AND d.is_anonymous = 0 AND d.status = 'aprobado'
             GROUP BY d.member_id, d.donor_name, full_name
             ORDER BY total_donated DESC
             LIMIT ?",
            [$churchId, $month, $year, $limit]
        );
    }

    /**
     * Total de donaciones de un año específico
     */
    public function getYearlyTotal(int $churchId, mixed $type = null, ?int $year = null): float
    {
        $year = $year ?? (int)date('Y');
        $params = [$churchId, $year];
        $extra = '';
        if ($type) {
            if (is_array($type)) {
                $placeholders = implode(',', array_fill(0, count($type), '?'));
                $extra    = " AND type IN ($placeholders)";
                $params   = array_merge($params, $type);
            } else {
                $extra    = ' AND type = ?';
                $params[] = $type;
            }
        }

        $result = $this->db->queryOne(
            "SELECT SUM(amount) as total FROM donations
             WHERE church_id = ? AND period_year = ? AND status = 'aprobado'{$extra}",
            $params
        );

        return (float)($result['total'] ?? 0);
    }

    /**
     * Total de un periodo: un mes de un año, o el año completo si $month es null
     */
    public function getPeriodTotal(int $churchId, mixed $type, int $year, ?int $month = null): float
    {
        $where  = "church_id = ? AND period_year = ? AND status = 'aprobado'";
        $params = [$churchId, $year];

        if ($month) {
            $where   .= ' AND period_month = ?';
            $params[] = $month;
        }

        $types = (array)$type;
        if ($types) {
            $where .= ' AND type IN (' . implode(',', array_fill(0, count($types), '?')) . ')';
            $params = array_merge($params, $types);
        }

        $result = $this->db->queryOne("SELECT SUM(amount) as total FROM donations WHERE {$where}", $params);

        return (float)($result['total'] ?? 0);
    }

    /**
     * Balance de una cuenta hasta una fecha (inclusive):
     * ingresos aprobados de sus tipos − egresos no anulados de su módulo
     */
    public function getBalance(int $churchId, array $incomeTypes, string $expenseModule, string $untilDate): float
    {
        $placeholders = implode(',', array_fill(0, count($incomeTypes), '?'));
        $income = (float)($this->db->queryOne(
            "SELECT SUM(amount) as total FROM donations
             WHERE church_id = ? AND status = 'aprobado' AND donation_date <= ? AND type IN ({$placeholders})",
            array_merge([$churchId, $untilDate], $incomeTypes)
        )['total'] ?? 0);

        $expense = (float)($this->db->queryOne(
            "SELECT SUM(amount) as total FROM finances
             WHERE church_id = ? AND type = 'egreso' AND status <> 'anulado' AND transaction_date <= ? AND target_module = ?",
            [$churchId, $untilDate, $expenseModule]
        )['total'] ?? 0);

        return round($income - $expense, 2);
    }

    /**
     * Total acumulado histórico
     */
    public function getAccumulatedTotal(int $churchId, mixed $type = null): float
    {
        $params = [$churchId];
        $extra = '';
        if ($type) {
            if (is_array($type)) {
                $placeholders = implode(',', array_fill(0, count($type), '?'));
                $extra    = " AND type IN ($placeholders)";
                $params   = array_merge($params, $type);
            } else {
                $extra    = ' AND type = ?';
                $params[] = $type;
            }
        }

        $result = $this->db->queryOne(
            "SELECT SUM(amount) as total FROM donations
             WHERE church_id = ? AND status = 'aprobado'{$extra}",
            $params
        );

        return (float)($result['total'] ?? 0);
    }
}

