<?php /** Vista: Reporte de Miembros */ ?>
<div class="space-y-6">
    <!-- Breadcrumb & Back Link -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="/reportes" class="hover:text-white transition-colors">Reportes</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-white">Reporte de Miembros</span>
        </div>
        
        <!-- Export buttons -->
        <div class="flex gap-2">
            <a href="/reportes/exportar/miembros?format=excel" 
               class="inline-flex items-center gap-2 bg-emerald-600/20 hover:bg-emerald-600/30 border border-emerald-500/20 text-emerald-400 font-medium py-2 px-4 rounded-xl text-sm transition-all shadow-md">
                📊 Exportar Excel
            </a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-brand-600/15 to-brand-650/5 border border-brand-500/20 rounded-2xl p-6 shadow-xl">
            <p class="text-xs text-brand-400 font-medium mb-1.5">Miembros (convertidos)</p>
            <p class="text-3xl font-extrabold text-white"><?= $sectionCounts['miembros'] ?></p>
        </div>
        <a href="/miembros?section=no_convertidos" class="bg-gray-900 border border-amber-500/20 rounded-2xl p-6 shadow-xl hover:border-amber-500/40 transition-colors">
            <p class="text-xs text-amber-400 font-medium mb-1.5">No convertidos</p>
            <p class="text-3xl font-extrabold text-white"><?= $sectionCounts['no_convertidos'] ?></p>
        </a>
        <a href="/miembros?section=visitas" class="bg-gray-900 border border-blue-500/20 rounded-2xl p-6 shadow-xl hover:border-blue-500/40 transition-colors">
            <p class="text-xs text-blue-400 font-medium mb-1.5">Visitas</p>
            <p class="text-3xl font-extrabold text-white"><?= $sectionCounts['visitas'] ?></p>
        </a>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-xl">
            <p class="text-xs text-gray-400 font-medium mb-1.5">Total de personas</p>
            <p class="text-3xl font-extrabold text-white"><?= $sectionCounts['todos'] ?></p>
        </div>
        <?php foreach ($stats as $row): ?>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-xl">
            <p class="text-xs text-gray-400 font-medium mb-1.5 uppercase"><?= e(memberStatusLabel($row['status'])) ?></p>
            <p class="text-3xl font-extrabold text-white"><?= (int)$row['total'] ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Miembros Inactivos -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl shadow-xl overflow-hidden mt-6">
        <div class="px-6 py-5 border-b border-gray-800">
            <h2 class="text-base font-semibold text-white">Miembros Inactivos (Últimos meses)</h2>
            <p class="text-xs text-gray-500">Miembros que no han registrado asistencia recientemente o no están participando activamente.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-800 bg-gray-800/30">
                        <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">Nombre</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">Email</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">Teléfono</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/80">
                    <?php if (empty($inactive)): ?>
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center text-gray-500 text-sm">
                            No se encontraron miembros inactivos.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($inactive as $m): ?>
                    <tr class="hover:bg-gray-800/20 transition-colors text-sm">
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-white"><?= e(($m['first_name'] ?? '') . ' ' . ($m['last_name'] ?? '')) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-400"><?= e($m['email'] ?? 'N/A') ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-400"><?= e($m['phone'] ?? 'N/A') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
