<?php
/**
 * Finance.php — Modelo de Finanzas
 * ----------------------------------
 * Transacciones financieras con soft-delete obligatorio.
 * Ninguna transacción puede eliminarse físicamente.
 *
 * @package App\Models
 */

namespace App\Modules\Finance\Models;

use App\Models\Model;

use Ramsey\Uuid\Uuid;

class Finance extends Model
{
    protected string $table = 'finances';

    /**
     * Crear transacción financiera con UUID
     */
    public function createTransaction(array $data): int
    {
        $data['uuid'] = Uuid::uuid4()->toString();
        return $this->create($data);
    }

    /**
     * Resumen del mes actual: ingresos y egresos
     */
    public function getCurrentMonthSummary(int $churchId): array
    {
        $row = $this->getAnnualCashFlow($churchId, (int)date('Y'), (int)date('n'))[0] ?? [];

        $ingresos = (float)($row['ingresos'] ?? 0);
        $egresos  = (float)($row['egresos'] ?? 0);

        return [
            'ingresos'       => $ingresos,
            'egresos'        => $egresos,
            'balance'        => round($ingresos - $egresos, 2),
            'transacciones'  => (int)($row['transacciones'] ?? 0),
        ];
    }

    /**
     * Flujo de caja anual (ingresos vs egresos por mes), o de un solo mes.
     * Ingresos = donaciones/ofrendas aprobadas (tabla donations, las tres cuentas)
     *          + ingresos registrados manualmente en finances.
     * Egresos  = egresos aprobados de finances.
     */
    public function getAnnualCashFlow(int $churchId, int $year, ?int $month = null): array
    {
        $monthFilter = $month ? ' AND period_month = ?' : '';
        $params      = $month ? [$churchId, $year, $month, $churchId, $year, $month] : [$churchId, $year, $churchId, $year];

        return $this->db->query(
            "SELECT period_month,
                    SUM(ingresos) as ingresos,
                    SUM(egresos)  as egresos,
                    SUM(transacciones) as transacciones
             FROM (
                 SELECT period_month, SUM(amount) as ingresos, 0 as egresos, COUNT(*) as transacciones
                 FROM donations
                 WHERE church_id = ? AND period_year = ? AND status = 'aprobado'{$monthFilter}
                 GROUP BY period_month
                 UNION ALL
                 SELECT period_month,
                        SUM(CASE WHEN type = 'ingreso' THEN amount ELSE 0 END),
                        SUM(CASE WHEN type = 'egreso'  THEN amount ELSE 0 END),
                        COUNT(*)
                 FROM finances
                 WHERE church_id = ? AND period_year = ? AND status = 'aprobado'{$monthFilter}
                 GROUP BY period_month
             ) flujo
             GROUP BY period_month
             ORDER BY period_month ASC",
            $params
        );
    }

    /**
     * Resumen por categoría del mes
     */
    public function getMonthlyCategoryBreakdown(int $churchId, int $month, int $year): array
    {
        return $this->db->query(
            "SELECT fc.name as category, f.type, SUM(f.amount) as total
             FROM finances f
             JOIN finance_categories fc ON fc.id = f.category_id
             WHERE f.church_id = ? AND f.period_month = ? AND f.period_year = ? AND f.status = 'aprobado'
             GROUP BY fc.name, f.type
             ORDER BY f.type, total DESC",
            [$churchId, $month, $year]
        );
    }

    /**
     * Total y cantidad de egresos, opcionalmente por procedencia, año y mes
     *
     * @return array ['total' => float, 'count' => int]
     */
    public function getExpenseTotals(int $churchId, ?string $targetModule = null, ?int $year = null, ?int $month = null): array
    {
        $where  = "church_id = ? AND type = 'egreso' AND status != 'anulado'";
        $params = [$churchId];

        if ($targetModule) {
            $where   .= ' AND target_module = ?';
            $params[] = $targetModule;
        }
        if ($year) {
            $where   .= ' AND period_year = ?';
            $params[] = $year;
        }
        if ($month) {
            $where   .= ' AND period_month = ?';
            $params[] = $month;
        }

        $result = $this->db->queryOne(
            "SELECT SUM(amount) as total, COUNT(*) as count FROM finances WHERE {$where}",
            $params
        );

        return ['total' => (float)($result['total'] ?? 0), 'count' => (int)($result['count'] ?? 0)];
    }

    /**
     * Buscar transacciones con paginación
     */
    public function search(int $churchId, array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where  = ['f.church_id = ?'];
        $params = [$churchId];

        if (!empty($filters['type'])) {
            $where[]  = 'f.type = ?';
            $params[] = $filters['type'];
        }

        if (!empty($filters['target_module'])) {
            $where[]  = 'f.target_module = ?';
            $params[] = $filters['target_module'];
        }

        if (!empty($filters['month'])) {
            $where[]  = 'f.period_month = ?';
            $params[] = $filters['month'];
        }

        if (!empty($filters['year'])) {
            $where[]  = 'f.period_year = ?';
            $params[] = $filters['year'];
        }

        if (!empty($filters['status'])) {
            $where[]  = 'f.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['category_id'])) {
            $where[]  = 'f.category_id = ?';
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['target_module'])) {
            $where[]  = 'f.target_module = ?';
            $params[] = $filters['target_module'];
        }

        if (($filters['receipt'] ?? '') === 'con') {
            $where[] = "(f.attachments IS NOT NULL AND JSON_LENGTH(f.attachments) > 0)";
        } elseif (($filters['receipt'] ?? '') === 'sin') {
            $where[] = "(f.attachments IS NULL OR JSON_LENGTH(f.attachments) = 0)";
        }

        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $where[]  = '(f.description LIKE ? OR f.reference LIKE ?)';
            $params   = array_merge($params, [$s, $s]);
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $total = (int)($this->db->queryOne(
            "SELECT COUNT(*) as total FROM finances f WHERE {$whereStr}", $params
        )['total'] ?? 0);

        $data = $this->db->query(
            "SELECT f.*, fc.name as category_name, u.name as created_by_name, ua.name as approved_by_name
             FROM finances f
             JOIN finance_categories fc ON fc.id = f.category_id
             LEFT JOIN users u ON u.id = f.created_by
             LEFT JOIN users ua ON ua.id = f.approved_by
             WHERE {$whereStr}
             ORDER BY f.transaction_date DESC, f.id DESC
             LIMIT ? OFFSET ?",
            [...$params, $perPage, $offset]
        );

        return [
            'data' => $data, 'total' => $total,
            'pages' => (int)ceil($total / $perPage), 'current' => $page,
        ];
    }

    /**
     * Obtener categorías financieras de una iglesia
     */
    public function getCategories(int $churchId, ?string $type = null, ?string $targetModule = null): array
    {
        $params = [$churchId];
        $where  = 'church_id = ? AND is_active = 1';
        if ($type) {
            $where   .= ' AND type = ?';
            $params[] = $type;
        }
        if ($targetModule) {
            $where   .= ' AND target_module = ?';
            $params[] = $targetModule;
        }

        // Agrupadas: primero las que tienen grupo (alfabético), al final las sin grupo
        return $this->db->query(
            "SELECT * FROM finance_categories WHERE {$where} ORDER BY type, group_name IS NULL, group_name, name",
            $params
        );
    }

    /**
     * Nombres de grupo ya usados (para sugerirlos al clasificar categorías)
     */
    public function getCategoryGroups(int $churchId, string $type): array
    {
        return array_column($this->db->query(
            "SELECT DISTINCT group_name FROM finance_categories
             WHERE church_id = ? AND type = ? AND group_name IS NOT NULL AND is_active = 1
             ORDER BY group_name",
            [$churchId, $type]
        ), 'group_name');
    }
}


