<?php /** Vista: Lista de Grupos Familiares */ ?>
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <p class="text-gray-400 text-sm">
            <span class="text-white font-semibold"><?= number_format($pagination['total']) ?></span> familias registradas
        </p>
    </div>
    <?php if ($auth_can('families.create')): ?>
    <a href="/familias/crear"
       class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-500 hover:to-purple-500
              text-white font-medium py-2.5 px-5 rounded-xl text-sm transition-all shadow-lg shadow-brand-500/20">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Registrar Familia
    </a>
    <?php endif; ?>
</div>

<!-- Secciones: todas las familias, parejas, parejas con un cónyuge no convertido -->
<?php $sections = ['todas' => 'Todas', 'parejas' => 'Parejas', 'no_convertidos' => 'Parejas con un no convertido']; ?>
<div class="flex flex-wrap gap-2 mb-4">
    <?php foreach ($sections as $key => $label): $active = $section === $key; ?>
    <a href="/familias?section=<?= $key ?>"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border transition-colors
              <?= $active ? 'bg-brand-600 border-brand-500 text-white' : 'bg-gray-900 border-gray-800 text-gray-400 hover:text-white hover:bg-gray-800' ?>">
        <?= $label ?>
        <span class="text-xs px-2 py-0.5 rounded-full <?= $active ? 'bg-white/20' : 'bg-gray-800 text-gray-300' ?>"><?= number_format($sectionCounts[$key] ?? 0) ?></span>
    </a>
    <?php endforeach; ?>
</div>

<!-- Tabla de familias -->
<div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-800 bg-gray-800/50">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Nombre de Familia</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Cabeza de Familia</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">Dirección</th>
                    <th class="text-center px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Miembros</th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                <?php if (empty($families)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <svg class="w-12 h-12 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p class="text-gray-500 text-sm">No se encontraron familias registradas</p>
                            <?php if ($auth_can('families.create')): ?>
                            <a href="/familias/crear" class="text-brand-400 hover:text-brand-300 text-sm font-medium">
                                Registrar la primera familia →
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($families as $f): ?>
                <tr class="hover:bg-gray-800/30 transition-colors group">
                    <!-- Nombre -->
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-brand-500/10 border border-brand-500/20 rounded-xl flex items-center justify-center flex-shrink-0">
                                <span class="text-brand-400 font-bold text-sm">🏠</span>
                            </div>
                            <div>
                                <a href="/familias/<?= $f['id'] ?>"
                                   class="text-sm font-medium text-white hover:text-brand-400 transition-colors truncate block">
                                    <?= e($f['name']) ?>
                                </a>
                                <p class="text-xs text-gray-500 truncate max-w-[260px]"><?= e($f['member_names'] ?? '') ?></p>
                                <?php if (!empty($f['unconverted_names'])): ?>
                                <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-500/15 text-amber-300">
                                    No convertido: <?= e($f['unconverted_names']) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <!-- Cabeza -->
                    <td class="px-6 py-4">
                        <span class="text-sm text-gray-300"><?= e($f['head_name'] ?? '—') ?></span>
                    </td>
                    <!-- Dirección -->
                    <td class="px-6 py-4 hidden md:table-cell">
                        <span class="text-sm text-gray-400 truncate max-w-[200px] block"><?= e($f['address'] ?? '—') ?></span>
                    </td>
                    <!-- Conteo miembros -->
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-800 text-gray-300">
                            <?= (int)$f['member_count'] ?> integrantes
                        </span>
                    </td>
                    <!-- Acciones -->
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="/familias/<?= $f['id'] ?>"
                               class="text-gray-400 hover:text-brand-400 p-1.5 rounded-lg hover:bg-brand-500/10 transition-all" title="Ver Detalles">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
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

    <!-- Paginación -->
    <?php if ($pagination['pages'] > 1): ?>
    <div class="border-t border-gray-800 px-6 py-4 flex items-center justify-between">
        <p class="text-xs text-gray-500">
            Página <?= $pagination['current'] ?> de <?= $pagination['pages'] ?>
        </p>
        <div class="flex gap-1">
            <?php for ($p = max(1, $pagination['current'] - 2); $p <= min($pagination['pages'], $pagination['current'] + 2); $p++): ?>
            <a href="?section=<?= e($section) ?>&page=<?= $p ?>"
               class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-medium transition-colors
                      <?= $p === $pagination['current']
                          ? 'bg-brand-600 text-white'
                          : 'text-gray-400 hover:text-white hover:bg-gray-800' ?>">
                <?= $p ?>
            </a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
