<?php /** Vista: Donaciones, Ofrendas o Construcción */ ?>

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

<!-- Resumen del mes -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-gradient-to-br from-emerald-600/20 to-emerald-600/5 border border-emerald-500/20 rounded-2xl p-5">
        <p class="text-xs text-emerald-400 font-medium mb-1"><?= e($summary['card1_label']) ?></p>
        <p class="text-2xl font-bold text-white"><?= formatCurrency($summary['card1_val']) ?></p>
    </div>
    <div class="bg-gradient-to-br from-blue-600/20 to-blue-600/5 border border-blue-500/20 rounded-2xl p-5">
        <p class="text-xs text-blue-400 font-medium mb-1"><?= e($summary['card2_label']) ?></p>
        <p class="text-2xl font-bold text-white"><?= formatCurrency($summary['card2_val']) ?></p>
    </div>
    <div class="bg-gradient-to-br from-purple-600/20 to-purple-600/5 border border-purple-500/20 rounded-2xl p-5">
        <p class="text-xs text-purple-400 font-medium mb-1"><?= e($summary['card3_label']) ?></p>
        <p class="text-2xl font-bold text-white"><?= formatCurrency($summary['card3_val']) ?></p>
    </div>
</div>

<!-- Toolbar -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <p class="text-gray-400 text-sm"><span class="text-white font-semibold"><?= number_format($pagination['total']) ?></span> registros encontrados</p>
    <div class="flex gap-2">
        <a href="/reportes/exportar/donaciones?format=excel&<?= http_build_query(['type' => $filters['selected_type'] ?: $allowedTypes, 'month' => $filters['month'], 'year' => $filters['year']]) ?>" class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-white bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 transition-colors">
            📥 Exportar Excel
        </a>
        <?php if ($auth_can('donations.create')): ?>
        <a href="<?= e(($basePath ?? '/donaciones') . '/crear') ?>" class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-500 hover:to-purple-500 text-white font-medium py-2.5 px-5 rounded-xl text-sm transition-all shadow-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Registrar <?= e($title === 'Ofrendas' ? 'Ofrenda' : ($title === 'Construcción' ? 'Construcción' : 'Donación')) ?>
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Filtros -->
<form method="GET" action="<?= e($basePath ?? '/donaciones') ?>" class="bg-gray-900 border border-gray-800 rounded-2xl p-4 mb-6">
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-3">
        <select name="type" class="bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">Todos los tipos</option>
            <?php foreach ($allowedTypes as $t): ?>
            <option value="<?= $t ?>" <?= ($filters['selected_type'] ?? '') === $t ? 'selected' : '' ?>><?= donationTypeLabel($t) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="month" class="bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="" <?= $filters['month'] === '' ? 'selected' : '' ?>>Todos los meses</option>
            <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>" <?= $filters['month'] == $m ? 'selected' : '' ?>><?= monthName($m) ?></option>
            <?php endfor; ?>
        </select>
        <select name="year" class="bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
            <option value="<?= $y ?>" <?= $filters['year'] == $y ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
        </select>
        <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="Buscar donante..."
               class="bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-gray-500">
        <button type="submit" class="bg-brand-600 hover:bg-brand-500 text-white font-medium py-2.5 px-4 rounded-xl text-sm transition-colors">Filtrar</button>
    </div>
</form>

<!-- Tabla de donaciones -->
<div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-800 bg-gray-800/50">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Fecha</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Recibo</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">RUC</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Donante</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Categoría</th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Monto</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Estado</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Detalle</th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                <?php if (empty($donations)): ?>
                <tr><td colspan="8" class="px-6 py-12 text-center text-gray-500 text-sm">No se encontraron registros</td></tr>
                <?php else: ?>
                <?php foreach ($donations as $d): ?>
                <tr class="hover:bg-gray-800/30 transition-colors group">
                    <td class="px-6 py-4">
                        <span class="text-xs text-gray-400"><?= formatDate($d['donation_date']) ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="font-mono text-xs text-gray-400"><?= e($d['receipt_number']) ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs text-gray-400"><?= e($d['ruc'] ?? '—') ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-white">
                            <?= $d['is_anonymous'] ? 'Anónimo' : e($d['full_donor_name'] ?? $d['donor_name'] ?? '—') ?>
                        </p>
                        <?php if ($d['member_id'] && !$d['is_anonymous']): ?>
                        <a href="/miembros/<?= $d['member_id'] ?>" class="text-xs text-brand-400 hover:text-brand-300">Ver perfil →</a>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs text-gray-300"><?= e($d['category_name'] ?? donationTypeLabel($d['type'])) ?></span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <span class="text-sm font-bold text-emerald-400"><?= formatCurrency((float)$d['amount']) ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs px-2 py-0.5 rounded-full <?= match($d['status'] ?? 'aprobado') {
                            'aprobado'  => 'bg-emerald-500/10 text-emerald-400',
                            'pendiente' => 'bg-amber-500/10 text-amber-400 animate-pulse',
                            'rechazado' => 'bg-red-500/10 text-red-400',
                            default     => 'bg-gray-500/10 text-gray-400',
                        } ?>"><?= ucfirst($d['status'] ?? 'aprobado') ?></span>
                    </td>
                    <td class="px-6 py-4 hidden lg:table-cell">
                        <span class="text-xs text-gray-400" title="<?= e($d['detail']) ?>"><?= e(truncate($d['detail'] ?? '', 45)) ?></span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <?php if (($d['status'] ?? 'aprobado') === 'pendiente' && \App\Core\Auth::roleLevel() >= 7): ?>
                            <form method="POST" action="<?= e($basePath ?? '/donaciones') ?>/<?= $d['id'] ?>/aprobar" class="inline">
                                <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">
                                <button type="submit" class="text-emerald-400 hover:text-emerald-300 p-1.5 rounded-lg hover:bg-emerald-500/10 transition-all" title="Aprobar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </form>
                            <form method="POST" action="<?= e($basePath ?? '/donaciones') ?>/<?= $d['id'] ?>/rechazar" class="inline">
                                <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">
                                <button type="submit" class="text-red-400 hover:text-red-300 p-1.5 rounded-lg hover:bg-red-500/10 transition-all" title="Rechazar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </form>
                            <?php endif; ?>
                            <a href="<?= e(($basePath ?? '/donaciones') . '/' . $d['id'] . '/recibo') ?>" target="_blank"
                               class="text-gray-400 hover:text-emerald-400 p-1.5 rounded-lg hover:bg-emerald-500/10 transition-all" title="Generar Recibo PDF">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </a>
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
            <a href="?<?= http_build_query(array_merge($filters, ['page' => $p, 'type' => $filters['selected_type']])) ?>"
               class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-medium transition-colors <?= $p === $pagination['current'] ? 'bg-brand-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800' ?>"><?= $p ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
