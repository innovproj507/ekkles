<?php
/**
 * Reconciliation.php — Modelo de Conciliaciones Bancarias
 * ---------------------------------------------------------
 * Gestión de cierres de caja y conciliaciones de cuentas.
 *
 * @package App\Models
 */

namespace App\Modules\Finance\Models;

use App\Models\Model;

class Reconciliation extends Model
{
    protected string $table = 'reconciliations';

    /**
     * Map de account_name hacia tipos de donaciones para ingresos
     */
    private array $incomeMapping = [
        'ofrendas'     => ['diezmo', 'ofrenda_general', 'ofrenda_especial', 'promesa'],
        'construccion' => ['construccion', 'proyecto'],
        'misiones'     => ['misiones', 'donacion'],
    ];

    /**
     * Map de account_name hacia target_module de egresos
     */
    private array $expenseMapping = [
        'ofrendas'     => 'ofrendas',
        'construccion' => 'construccion',
        'misiones'     => 'donaciones', // En expenses está como 'donaciones'
    ];

    /**
     * Calcular el saldo inicial para un periodo y cuenta.
     * Si existe una conciliación previa cerrada, tomamos su saldo real.
     * Si no, sumamos todos los ingresos y restamos todos los egresos hasta el día anterior.
     */
    public function getStartingBalance(int $churchId, string $accountName, string $startDate): float
    {
        // 1. Buscar la última conciliación cerrada antes del startDate
        $lastRecon = $this->db->queryOne(
            "SELECT actual_balance, period_end 
             FROM reconciliations 
             WHERE church_id = ? AND account_name = ? AND status = 'cerrada' AND period_end < ?
             ORDER BY period_end DESC LIMIT 1",
            [$churchId, $accountName, $startDate]
        );

        if ($lastRecon) {
            // Si hay una conciliación, partimos de su saldo y sumamos/restamos los movimientos entre period_end y startDate - 1
            $baseBalance = (float)$lastRecon['actual_balance'];
            $calcStart = date('Y-m-d', strtotime($lastRecon['period_end'] . ' +1 day'));
            $calcEnd = date('Y-m-d', strtotime($startDate . ' -1 day'));

            if ($calcStart <= $calcEnd) {
                $income = $this->getIncomeInPeriod($churchId, $accountName, $calcStart, $calcEnd);
                $expense = $this->getExpenseInPeriod($churchId, $accountName, $calcStart, $calcEnd);
                return $baseBalance + $income - $expense;
            }

            return $baseBalance;
        }

        // Si no hay conciliación previa, calcular desde el inicio de los tiempos hasta startDate - 1
        $calcEnd = date('Y-m-d', strtotime($startDate . ' -1 day'));
        $income = $this->getIncomeInPeriod($churchId, $accountName, '2000-01-01', $calcEnd);
        $expense = $this->getExpenseInPeriod($churchId, $accountName, '2000-01-01', $calcEnd);
        
        return $income - $expense;
    }

    /**
     * Obtener ingresos en un periodo para una cuenta
     */
    public function getIncomeInPeriod(int $churchId, string $accountName, string $startDate, string $endDate): float
    {
        $types = $this->incomeMapping[$accountName] ?? [];
        if (empty($types)) return 0.00;

        $inClause = implode(',', array_fill(0, count($types), '?'));
        
        $params = array_merge([$churchId, $startDate, $endDate], $types);

        $result = $this->db->queryOne(
            "SELECT SUM(amount) as total FROM donations 
             WHERE church_id = ? AND status != 'anulado' 
             AND donation_date >= ? AND donation_date <= ? 
             AND type IN ($inClause)",
            $params
        );

        return (float)($result['total'] ?? 0);
    }

    /**
     * Obtener egresos en un periodo para una cuenta
     */
    public function getExpenseInPeriod(int $churchId, string $accountName, string $startDate, string $endDate): float
    {
        $targetModule = $this->expenseMapping[$accountName] ?? '';
        if (!$targetModule) return 0.00;

        $result = $this->db->queryOne(
            "SELECT SUM(amount) as total FROM finances 
             WHERE church_id = ? AND type = 'egreso' AND status != 'anulado'
             AND transaction_date >= ? AND transaction_date <= ?
             AND target_module = ?",
            [$churchId, $startDate, $endDate, $targetModule]
        );

        return (float)($result['total'] ?? 0);
    }

    /**
     * Cerrar conciliación y bloquear registros
     */
    public function closeReconciliation(array $data, int $userId): int
    {
        $id = $this->create(array_merge($data, [
            'status' => 'cerrada',
            'reconciled_by' => $userId,
            'reconciled_at' => date('Y-m-d H:i:s')
        ]));

        // Marcar donaciones e ingresos como conciliados
        $types = $this->incomeMapping[$data['account_name']] ?? [];
        if (!empty($types)) {
            $inClause = implode(',', array_fill(0, count($types), '?'));
            $params = array_merge([$data['church_id'], $data['period_start'], $data['period_end']], $types);
            
            // Si la tabla finances tiene donaciones conectadas, se deberían marcar ahí.
            // Para Ekkles, 'finances' contiene egresos, y algunos ingresos (conectados a donation_id).
            // Marcamos 'is_reconciled' = 1 en finances
            $targetModule = $this->expenseMapping[$data['account_name']] ?? '';
            $this->db->execute(
                "UPDATE finances SET is_reconciled = 1 
                 WHERE church_id = ? AND transaction_date >= ? AND transaction_date <= ?
                 AND target_module = ?",
                [$data['church_id'], $data['period_start'], $data['period_end'], $targetModule]
            );
        }

        return $id;
    }

    /**
     * Listado paginado de conciliaciones
     */
    public function search(int $churchId, array $filters = [], int $page = 1, int $perPage = 25): array
    {
        $where  = ['r.church_id = ?'];
        $params = [$churchId];

        if (!empty($filters['account_name'])) {
            $where[] = 'r.account_name = ?';
            $params[] = $filters['account_name'];
        }

        if (!empty($filters['status'])) {
            $where[] = 'r.status = ?';
            $params[] = $filters['status'];
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $total = (int)($this->db->queryOne(
            "SELECT COUNT(*) as total FROM reconciliations r WHERE {$whereStr}",
            $params
        )['total'] ?? 0);

        $data = $this->db->query(
            "SELECT r.*, u.name as reconciled_by_name
             FROM reconciliations r
             LEFT JOIN users u ON u.id = r.reconciled_by
             WHERE {$whereStr}
             ORDER BY r.period_end DESC, r.created_at DESC
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
}
