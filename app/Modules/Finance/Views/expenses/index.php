<?php /** Vista: Egresos (Gastos) */ ?>
<!-- KPIs de Egresos -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-gradient-to-br from-red-600/20 to-red-600/5 border border-red-500/20 rounded-2xl p-5">
        <p class="text-xs text-red-400 font-medium mb-1"><?= e($summary['period_label']) ?></p>
        <p class="text-2xl font-bold text-white"><?= formatCurrency($summary['period']) ?></p>
        <p class="text-xs text-gray-500 mt-1"><?= $summary['period_count'] ?> transacciones registradas</p>
    </div>
    <div class="bg-gradient-to-br from-amber-600/20 to-amber-600/5 border border-amber-500/20 rounded-2xl p-5">
        <p class="text-xs text-amber-400 font-medium mb-1"><?= e($summary['yearly_label']) ?></p>
        <p class="text-2xl font-bold text-white"><?= formatCurrency($summary['yearly']) ?></p>
    </div>
    <div class="bg-gradient-to-br from-brand-600/20 to-brand-600/5 border border-brand-500/20 rounded-2xl p-5">
        <p class="text-xs text-brand-400 font-medium mb-1"><?= e($summary['accumulated_label']) ?></p>
        <p class="text-2xl font-bold text-white"><?= formatCurrency($summary['accumulated']) ?></p>
    </div>
</div>

<!-- Toolbar -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <p class="text-gray-400 text-sm"><span class="text-white font-semibold"><?= number_format($pagination['total']) ?></span> transacciones de egreso</p>
    <?php if ($auth_can('finance.create')): ?>
    <a href="/egresos/crear" class="inline-flex items-center gap-2 bg-gradient-to-r from-red-600 to-brand-600 hover:from-red-500 hover:to-brand-500 text-white font-medium py-2.5 px-5 rounded-xl text-sm transition-all shadow-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Registrar Egreso
    </a>
    <?php endif; ?>
</div>

<!-- Filtros -->
<form method="GET" action="/egresos" class="bg-gray-900 border border-gray-800 rounded-2xl p-4 mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Filtro Mes -->
        <select name="month" class="bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">Cualquier mes</option>
            <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>" <?= $filters['month'] == $m ? 'selected' : '' ?>><?= monthName($m) ?></option>
            <?php endfor; ?>
        </select>

        <!-- Filtro Corresponde a -->
        <select name="target_module" class="bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">Cualquier procedencia</option>
            <option value="ofrendas" <?= $filters['target_module'] === 'ofrendas' ? 'selected' : '' ?>>Ofrendas</option>
            <option value="construccion" <?= $filters['target_module'] === 'construccion' ? 'selected' : '' ?>>Construcción</option>
            <option value="donaciones" <?= $filters['target_module'] === 'donaciones' ? 'selected' : '' ?>>Obras Misioneras y Donaciones</option>
        </select>
        
        <!-- Filtro Categoría -->
        <select name="category_id" class="bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">Todas las categorías</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $filters['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
        
        <!-- Filtro de Recibo -->
        <select name="receipt" class="bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">Con y sin recibo</option>
            <option value="con" <?= ($filters['receipt'] ?? '') === 'con' ? 'selected' : '' ?>>Con recibo</option>
            <option value="sin" <?= ($filters['receipt'] ?? '') === 'sin' ? 'selected' : '' ?>>Sin recibo</option>
        </select>

        <!-- Filtro de Búsqueda -->
        <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="Buscar detalle o proveedor..."
               class="bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-gray-500">
        
        <!-- Botón Filtrar -->
        <button type="submit" class="bg-brand-600 hover:bg-brand-500 text-white font-medium py-2.5 px-4 rounded-xl text-sm transition-colors">Filtrar</button>
    </div>
</form>

<!-- Tabla -->
<div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-800 bg-gray-800/50">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">Fecha</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">Factura</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">RUC / Cédula</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">Proveedor</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">Detalle del Gasto</th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">Monto</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">Categoría</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">Corresponde a</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">Autorizado Por</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">Estado</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">Recibo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                <?php if (empty($transactions)): ?>
                <tr><td colspan="11" class="px-6 py-12 text-center text-gray-500 text-sm">No se encontraron egresos registrados</td></tr>
                <?php else: ?>
                <?php foreach ($transactions as $t): ?>
                <tr class="hover:bg-gray-800/30 transition-colors text-sm">
                    <td class="px-6 py-4 whitespace-nowrap text-gray-300"><?= formatDate($t['transaction_date']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-white font-medium"><?= e($t['invoice_number'] ?? 'N/A') ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-400"><?= e($t['provider_ruc'] ?? 'N/A') ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-300"><?= e($t['provider_name'] ?? 'N/A') ?></td>
                    <td class="px-6 py-4 text-gray-300 min-w-[200px]"><?= e($t['description']) ?></td>
                    <td class="px-6 py-4 text-right whitespace-nowrap text-red-400 font-bold">-<?= formatCurrency((float)$t['amount']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-400"><?= e($t['category_name']) ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if (($t['target_module'] ?? '') === 'ofrendas'): ?>
                        <span class="inline-flex items-center text-xs px-2.5 py-0.5 rounded-full font-medium bg-emerald-500/10 text-emerald-400">Ofrendas</span>
                        <?php elseif (($t['target_module'] ?? '') === 'construccion'): ?>
                        <span class="inline-flex items-center text-xs px-2.5 py-0.5 rounded-full font-medium bg-amber-500/10 text-amber-400">Construcción</span>
                        <?php elseif (($t['target_module'] ?? '') === 'donaciones'): ?>
                        <span class="inline-flex items-center text-xs px-2.5 py-0.5 rounded-full font-medium bg-purple-500/10 text-purple-400">Obras Misioneras y Donaciones</span>
                        <?php else: ?>
                        <span class="inline-flex items-center text-xs px-2.5 py-0.5 rounded-full font-medium bg-gray-500/10 text-gray-400">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center gap-1.5 text-xs text-brand-300 bg-brand-500/10 px-2.5 py-1 rounded-lg">
                            👤 <?= e($t['approved_by_name'] ?? 'Sistema') ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center text-xs px-2.5 py-0.5 rounded-full font-medium bg-emerald-500/10 text-emerald-400 capitalize">
                            <?= $t['status'] ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php $receipts = json_decode((string)($t['attachments'] ?? ''), true) ?: []; ?>
                        <?php foreach ($receipts as $n => $r): ?>
                        <a href="/egresos/<?= (int)$t['id'] ?>/recibo?n=<?= $n ?>" target="_blank" title="<?= e($r['name'] ?? '') ?>"
                           class="inline-flex items-center gap-1 text-xs text-emerald-300 bg-emerald-500/10 hover:bg-emerald-500/20 px-2.5 py-1 rounded-lg mr-1">📎 Ver<?= count($receipts) > 1 ? ' ' . ($n + 1) : '' ?></a>
                        <?php endforeach; ?>
                        <?php if ($auth_can('finance.create')): ?>
                        <form method="POST" action="/egresos/<?= (int)$t['id'] ?>/recibo" enctype="multipart/form-data" class="inline">
                            <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">
                            <input type="hidden" name="back" value="<?= e($_SERVER['REQUEST_URI'] ?? '/egresos') ?>">
                            <label class="cursor-pointer inline-flex items-center gap-1 text-xs text-gray-400 hover:text-white bg-gray-800 hover:bg-gray-700 px-2.5 py-1 rounded-lg">
                                <?= $receipts ? '+' : '⬆ Subir' ?>
                                <input type="file" name="receipt" accept="application/pdf,image/jpeg,image/png,image/webp" class="hidden" onchange="this.form.submit()">
                            </label>
                        </form>
                        <?php endif; ?>
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
