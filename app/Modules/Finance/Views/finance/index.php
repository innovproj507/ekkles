<?php /** Vista: Finanzas */ ?>

<?php if (isset($pendingCount) && $pendingCount > 0): ?>
<div class="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-4 mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="p-2 bg-amber-500/20 rounded-lg">
            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div>
            <h3 class="text-sm font-semibold text-amber-400">Transacciones Pendientes de Aprobación</h3>
            <p class="text-xs text-amber-500/80 mt-0.5">Hay <?= $pendingCount ?> registro(s) esperando revisión.</p>
        </div>
    </div>
    <a href="?status=pendiente" class="text-xs font-medium text-amber-400 hover:text-amber-300 bg-amber-500/10 hover:bg-amber-500/20 px-3 py-1.5 rounded-lg transition-colors">
        Ver Pendientes
    </a>
</div>
<?php endif; ?>

<!-- KPI del mes -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-gradient-to-br from-emerald-600/20 to-emerald-600/5 border border-emerald-500/20 rounded-2xl p-5">
        <p class="text-xs text-emerald-400 font-medium mb-1">Ingresos del Mes</p>
        <p class="text-2xl font-bold text-white"><?= formatCurrency($summary['ingresos']) ?></p>
        <p class="text-xs text-gray-500 mt-1"><?= $summary['transacciones'] ?> transacciones</p>
    </div>
    <div class="bg-gradient-to-br from-red-600/20 to-red-600/5 border border-red-500/20 rounded-2xl p-5">
        <p class="text-xs text-red-400 font-medium mb-1">Egresos del Mes</p>
        <p class="text-2xl font-bold text-white"><?= formatCurrency($summary['egresos']) ?></p>
    </div>
    <div class="bg-gradient-to-br from-brand-600/20 to-brand-600/5 border border-brand-500/20 rounded-2xl p-5">
        <p class="text-xs text-brand-400 font-medium mb-1">Balance Neto</p>
        <p class="text-2xl font-bold <?= $summary['balance'] >= 0 ? 'text-emerald-400' : 'text-red-400' ?>">
            <?= formatCurrency($summary['balance']) ?>
        </p>
    </div>
</div>

<!-- Gráfica de flujo anual + Toolbar -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2 bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-white">Flujo de Caja <?= date('Y') ?></h2>
            <a href="/reportes/exportar/finanzas?format=excel" class="text-xs text-gray-400 hover:text-white bg-gray-800 border border-gray-700 rounded-xl px-3 py-1.5 transition-colors">📥 Excel</a>
        </div>
        <div class="h-48"><canvas id="chart-cashflow-full"></canvas></div>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-white">Por Categoría</h2>
        </div>
        <div class="h-36"><canvas id="chart-categories"></canvas></div>
    </div>
</div>

<!-- Toolbar -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <p class="text-gray-400 text-sm"><span class="text-white font-semibold"><?= number_format($pagination['total']) ?></span> transacciones</p>
    <?php if ($auth_can('finance.create')): ?>
    <a href="/finanzas/crear" class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-500 hover:to-purple-500 text-white font-medium py-2.5 px-5 rounded-xl text-sm transition-all shadow-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Nueva Transacción
    </a>
    <?php endif; ?>
</div>

<!-- Filtros -->
<form method="GET" action="/finanzas" class="bg-gray-900 border border-gray-800 rounded-2xl p-4 mb-6">
    <div class="grid grid-cols-2 sm:grid-cols-6 gap-3">
        <select name="type" class="bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">Todos</option>
            <option value="ingreso" <?= $filters['type'] === 'ingreso' ? 'selected' : '' ?>>Ingresos</option>
            <option value="egreso"  <?= $filters['type'] === 'egreso'  ? 'selected' : '' ?>>Egresos</option>
        </select>
        <select name="month" class="bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>" <?= $filters['month'] == $m ? 'selected' : '' ?>><?= monthName($m) ?></option>
            <?php endfor; ?>
        </select>
        <select name="category_id" class="bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">Todas las categorías</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $filters['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="target_module" class="bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">Todos los Tipos</option>
            <option value="ofrendas" <?= $filters['target_module'] === 'ofrendas' ? 'selected' : '' ?>>Ofrendas</option>
            <option value="construccion" <?= $filters['target_module'] === 'construccion' ? 'selected' : '' ?>>Construcción</option>
            <option value="donaciones" <?= $filters['target_module'] === 'donaciones' ? 'selected' : '' ?>>Obras Misioneras y Donaciones</option>
        </select>
        <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="Buscar descripción..."
               class="bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-gray-500">
        <button type="submit" class="bg-brand-600 hover:bg-brand-500 text-white font-medium py-2.5 px-4 rounded-xl text-sm transition-colors">Filtrar</button>
    </div>
</form>

<!-- Tabla -->
<div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-800 bg-gray-800/50">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Tipo</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Descripción</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">Categoría</th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Monto</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Fecha</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Estado</th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                <?php if (empty($transactions)): ?>
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 text-sm">No se encontraron transacciones</td></tr>
                <?php else: ?>
                <?php foreach ($transactions as $t): ?>
                <tr class="hover:bg-gray-800/30 transition-colors">
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1 text-xs px-2.5 py-0.5 rounded-full font-medium
                            <?= $t['type'] === 'ingreso' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' ?>">
                            <?= $t['type'] === 'ingreso' ? '↑' : '↓' ?> <?= ucfirst($t['type']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-white"><?= e(truncate($t['description'], 50)) ?></p>
                        <?php if ($t['reference']): ?><p class="text-xs text-gray-500"><?= e($t['reference']) ?></p><?php endif; ?>
                    </td>
                    <td class="px-6 py-4 hidden md:table-cell"><span class="text-xs text-gray-400"><?= e($t['category_name']) ?></span></td>
                    <td class="px-6 py-4 text-right">
                        <span class="text-sm font-bold <?= $t['type'] === 'ingreso' ? 'text-emerald-400' : 'text-red-400' ?>">
                            <?= $t['type'] === 'egreso' ? '-' : '' ?><?= formatCurrency((float)$t['amount']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 hidden lg:table-cell"><span class="text-xs text-gray-400"><?= formatDate($t['transaction_date']) ?></span></td>
                    <td class="px-6 py-4 hidden lg:table-cell">
                        <span class="text-xs px-2 py-0.5 rounded-full <?= match($t['status'] ?? 'aprobado') {
                            'aprobado'  => 'bg-emerald-500/10 text-emerald-400',
                            'pendiente' => 'bg-amber-500/10 text-amber-400 animate-pulse',
                            'rechazado' => 'bg-red-500/10 text-red-400',
                            default     => 'bg-gray-500/10 text-gray-400',
                        } ?>"><?= ucfirst($t['status'] ?? 'aprobado') ?></span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <?php if (($t['status'] ?? 'aprobado') === 'pendiente' && \App\Core\Auth::roleLevel() >= 7): ?>
                            <form method="POST" action="/finanzas/<?= $t['id'] ?>/aprobar" class="inline">
                                <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">
                                <button type="submit" class="text-emerald-400 hover:text-emerald-300 p-1.5 rounded-lg hover:bg-emerald-500/10 transition-all" title="Aprobar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </form>
                            <form method="POST" action="/finanzas/<?= $t['id'] ?>/rechazar" class="inline">
                                <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">
                                <button type="submit" class="text-red-400 hover:text-red-300 p-1.5 rounded-lg hover:bg-red-500/10 transition-all" title="Rechazar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($pagination['pages'] > 1): ?>
    <div class="border-t border-gray-800 px-6 py-4 flex items-center justify-between">
        <p class="text-xs text-gray-500">Página <?= $pagination['current'] ?> de <?= $pagination['pages'] ?></p>
        <div class="flex gap-1">
            <?php for ($p = max(1, $pagination['current'] - 2); $p <= min($pagination['pages'], $pagination['current'] + 2); $p++): ?>
            <a href="?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>"
               class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-medium transition-colors <?= $p === $pagination['current'] ? 'bg-brand-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800' ?>"><?= $p ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
<?php
$cfLabels = [];
$cfIn  = [];
$cfOut = [];
foreach ($cashFlow as $cf) {
    $cfLabels[] = monthName((int)$cf['period_month']);
    $cfIn[]     = (float)$cf['ingresos'];
    $cfOut[]    = (float)$cf['egresos'];
}
?>
// Gráfica flujo anual
new Chart(document.getElementById('chart-cashflow-full'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($cfLabels) ?>,
        datasets: [
            { label: 'Ingresos', data: <?= json_encode($cfIn) ?>, backgroundColor: 'rgba(16,185,129,.25)', borderColor: 'rgba(16,185,129,1)', borderWidth: 1.5, borderRadius: 6 },
            { label: 'Egresos',  data: <?= json_encode($cfOut) ?>, backgroundColor: 'rgba(239,68,68,.25)', borderColor: 'rgba(239,68,68,1)', borderWidth: 1.5, borderRadius: 6 },
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { labels: { color: 'rgba(156,163,175,1)', boxWidth: 12 } } },
        scales: {
            x: { grid: { display: false }, ticks: { color: 'rgba(107,114,128,1)' } },
            y: { grid: { color: 'rgba(55,65,81,.5)' }, beginAtZero: true, ticks: { color: 'rgba(107,114,128,1)' } }
        }
    }
});
</script>
