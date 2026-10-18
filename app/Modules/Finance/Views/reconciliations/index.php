<?php /** Vista: Conciliaciones Bancarias */ ?>
<div class="max-w-7xl mx-auto">
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white"><?= e($title) ?></h1>
            <p class="text-gray-400 text-sm mt-1">Cuadre exacto entre ingresos, egresos y el saldo en bancos.</p>
        </div>
        <?php if ($auth_can('finance.create')): ?>
        <a href="/conciliaciones/crear" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-500 text-white font-medium py-2 px-4 rounded-xl text-sm transition-colors shadow-lg shadow-brand-500/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nueva Conciliación
        </a>
        <?php endif; ?>
    </div>

    <!-- Filtros -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 mb-6">
        <form method="GET" action="/conciliaciones" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <select name="account_name" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" onchange="this.form.submit()">
                    <option value="">Todas las Cuentas</option>
                    <option value="ofrendas" <?= $filters['account_name'] === 'ofrendas' ? 'selected' : '' ?>>Ofrendas</option>
                    <option value="construccion" <?= $filters['account_name'] === 'construccion' ? 'selected' : '' ?>>Construcción</option>
                    <option value="misiones" <?= $filters['account_name'] === 'misiones' ? 'selected' : '' ?>>Obras Misioneras y Donaciones</option>
                </select>
            </div>
            <div class="w-full sm:w-48">
                <select name="status" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" onchange="this.form.submit()">
                    <option value="">Todos los Estados</option>
                    <option value="borrador" <?= $filters['status'] === 'borrador' ? 'selected' : '' ?>>Borrador</option>
                    <option value="cerrada" <?= $filters['status'] === 'cerrada' ? 'selected' : '' ?>>Cerrada</option>
                </select>
            </div>
            <?php if (!empty(array_filter($filters))): ?>
            <a href="/conciliaciones" class="flex items-center justify-center px-4 py-2 text-sm text-gray-400 hover:text-white bg-gray-800 hover:bg-gray-700 rounded-xl transition-colors">
                Limpiar
            </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Lista de Conciliaciones -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-400">
                <thead class="bg-gray-800/50 text-xs uppercase font-medium text-gray-500">
                    <tr>
                        <th class="px-6 py-4">ID / Cuenta</th>
                        <th class="px-6 py-4">Periodo</th>
                        <th class="px-6 py-4 text-right">Saldo Inicial</th>
                        <th class="px-6 py-4 text-right">Movimientos</th>
                        <th class="px-6 py-4 text-right">Saldo Real</th>
                        <th class="px-6 py-4 text-center">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No se encontraron conciliaciones.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($records as $r): ?>
                    <tr class="hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="text-white font-medium">#<?= str_pad($r['id'], 5, '0', STR_PAD_LEFT) ?></span>
                            <div class="text-xs mt-0.5"><?= e(ucfirst($r['account_name'])) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-gray-300"><?= formatDate($r['period_start']) ?></span>
                            <span class="text-gray-500 mx-1">→</span>
                            <span class="text-gray-300"><?= formatDate($r['period_end']) ?></span>
                        </td>
                        <td class="px-6 py-4 text-right font-medium text-gray-300">
                            $<?= number_format($r['starting_balance'], 2) ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="text-emerald-400 text-xs">+ $<?= number_format($r['total_income'], 2) ?></div>
                            <div class="text-red-400 text-xs">- $<?= number_format($r['total_expense'], 2) ?></div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold text-white">$<?= number_format($r['actual_balance'], 2) ?></span>
                            <?php if ($r['difference'] != 0): ?>
                            <div class="text-red-400 text-[10px] mt-0.5">Dif: $<?= number_format($r['difference'], 2) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if ($r['status'] === 'cerrada'): ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    Cerrada
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                    Borrador
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="/conciliaciones/<?= $r['id'] ?>" class="text-brand-400 hover:text-brand-300 font-medium transition-colors">
                                Detalles
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <?php if ($pagination['pages'] > 1): ?>
        <div class="px-6 py-4 border-t border-gray-800 flex items-center justify-between">
            <span class="text-sm text-gray-500">
                Página <?= $pagination['current'] ?> de <?= $pagination['pages'] ?>
            </span>
            <div class="flex gap-2">
                <?php if ($pagination['current'] > 1): ?>
                <a href="?page=<?= $pagination['current'] - 1 ?>&<?= http_build_query($filters) ?>" class="px-3 py-1 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-sm transition-colors">Anterior</a>
                <?php endif; ?>
                <?php if ($pagination['current'] < $pagination['pages']): ?>
                <a href="?page=<?= $pagination['current'] + 1 ?>&<?= http_build_query($filters) ?>" class="px-3 py-1 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-sm transition-colors">Siguiente</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
