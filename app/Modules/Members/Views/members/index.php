<?php /** Vista: Lista de Miembros */ ?>
<!-- Barra de herramientas -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <p class="text-gray-400 text-sm">
            <span class="text-white font-semibold"><?= number_format($pagination['total']) ?></span> personas encontradas
        </p>
    </div>
    <?php if ($auth_can('members.create')): ?>
    <a href="/miembros/crear"
       class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-500 hover:to-purple-500
              text-white font-medium py-2.5 px-5 rounded-xl text-sm transition-all shadow-lg shadow-brand-500/20">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        Registrar Miembro
    </a>
    <?php endif; ?>
</div>

<!-- Secciones: miembros convertidos, no convertidos, visitas -->
<?php $sections = ['miembros' => 'Miembros', 'no_convertidos' => 'No convertidos', 'visitas' => 'Visitas', 'todos' => 'Todos']; ?>
<div class="flex flex-wrap gap-2 mb-4">
    <?php foreach ($sections as $key => $label): $active = $filters['section'] === $key; ?>
    <a href="/miembros?section=<?= $key ?>"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border transition-colors
              <?= $active ? 'bg-brand-600 border-brand-500 text-white' : 'bg-gray-900 border-gray-800 text-gray-400 hover:text-white hover:bg-gray-800' ?>">
        <?= $label ?>
        <span class="text-xs px-2 py-0.5 rounded-full <?= $active ? 'bg-white/20' : 'bg-gray-800 text-gray-300' ?>"><?= number_format($sectionCounts[$key] ?? 0) ?></span>
    </a>
    <?php endforeach; ?>
</div>

<!-- Filtros de búsqueda -->
<form method="GET" action="/miembros" class="bg-gray-900 border border-gray-800 rounded-2xl p-4 mb-6">
    <input type="hidden" name="section" value="<?= e($filters['section']) ?>">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="lg:col-span-2">
            <input type="text" name="search" value="<?= e($filters['search']) ?>"
                   placeholder="Buscar por nombre, email, teléfono..."
                   class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-gray-500">
        </div>
        <select name="status"
                class="bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">Todos los estados</option>
            <?php foreach (['activo','inactivo','visitante','nuevo_creyente','trasladado'] as $s): ?>
            <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= memberStatusLabel($s) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="ministry_id"
                class="bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">Todos los ministerios</option>
            <?php foreach ($ministries as $m): ?>
            <option value="<?= $m['id'] ?>" <?= $filters['ministry_id'] == $m['id'] ? 'selected' : '' ?>><?= e($m['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <div class="flex gap-2">
            <button type="submit"
                    class="flex-1 bg-brand-600 hover:bg-brand-500 text-white font-medium py-2.5 px-4 rounded-xl text-sm transition-colors">
                Filtrar
            </button>
            <a href="/miembros?section=<?= e($filters['section']) ?>"
               class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 font-medium py-2.5 px-4 rounded-xl text-sm transition-colors text-center">
                Limpiar
            </a>
        </div>
    </div>
</form>

<!-- Acciones de exportación -->
<div class="flex gap-2 mb-4">
    <a href="/reportes/exportar/miembros?format=excel"
       class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-white bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-xl px-3 py-2 transition-colors">
        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Exportar Excel
    </a>
</div>

<!-- Tabla de miembros -->
<div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-800 bg-gray-800/50">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Miembro</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">Contacto</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Ministerio</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Estado</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Última Asistencia</th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                <?php if (empty($members)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <svg class="w-12 h-12 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <p class="text-gray-500 text-sm">No se encontraron miembros</p>
                            <?php if ($auth_can('members.create')): ?>
                            <a href="/miembros/crear" class="text-brand-400 hover:text-brand-300 text-sm font-medium">
                                Registrar el primer miembro →
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($members as $m): ?>
                <tr class="hover:bg-gray-800/30 transition-colors group">
                    <!-- Foto + Nombre -->
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <img src="<?= e(avatarUrl($m['photo'], $m['first_name'] . ' ' . $m['last_name'])) ?>"
                                 alt="" class="w-9 h-9 rounded-xl object-cover flex-shrink-0">
                            <div class="min-w-0">
                                <a href="/miembros/<?= $m['id'] ?>"
                                   class="text-sm font-medium text-white hover:text-brand-400 transition-colors truncate block">
                                    <?= e($m['first_name'] . ' ' . $m['last_name']) ?>
                                </a>
                                <p class="text-xs text-gray-500"><?= e($m['code']) ?></p>
                            </div>
                        </div>
                    </td>
                    <!-- Contacto -->
                    <td class="px-6 py-4 hidden md:table-cell">
                        <p class="text-sm text-gray-300 truncate max-w-[160px]"><?= e($m['email'] ?? '—') ?></p>
                        <p class="text-xs text-gray-500"><?= e($m['phone'] ?? '') ?></p>
                    </td>
                    <!-- Ministerio -->
                    <td class="px-6 py-4 hidden lg:table-cell">
                        <span class="text-sm text-gray-400"><?= e($m['ministry_name'] ?? '—') ?></span>
                    </td>
                    <!-- Estado -->
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= memberStatusBadge($m['status']) ?>">
                            <?= memberStatusLabel($m['status']) ?>
                        </span>
                        <?php if (empty($m['is_converted']) && $m['status'] !== 'visitante'): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-500/15 text-amber-300 mt-1">No convertido</span>
                        <?php endif; ?>
                    </td>
                    <!-- Última asistencia -->
                    <td class="px-6 py-4 hidden lg:table-cell">
                        <span class="text-xs text-gray-400"><?= formatDate($m['last_attendance_at']) ?></span>
                    </td>
                    <!-- Acciones -->
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="/miembros/<?= $m['id'] ?>"
                               class="text-gray-400 hover:text-brand-400 p-1.5 rounded-lg hover:bg-brand-500/10 transition-all" title="Ver perfil">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <?php if ($auth_can('members.edit')): ?>
                            <a href="/miembros/<?= $m['id'] ?>/editar"
                               class="text-gray-400 hover:text-amber-400 p-1.5 rounded-lg hover:bg-amber-500/10 transition-all" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </a>
                            <?php endif; ?>
                            <?php if ($auth_can('members.delete')): ?>
                            <form method="POST" action="/miembros/<?= $m['id'] ?>/eliminar"
                                  onsubmit="return confirm('¿Eliminar a <?= e(addslashes($m['first_name'])) ?>?')">
                                <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">
                                <button type="submit" class="text-gray-400 hover:text-red-400 p-1.5 rounded-lg hover:bg-red-500/10 transition-all" title="Eliminar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
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

    <!-- Paginación -->
    <?php if ($pagination['pages'] > 1): ?>
    <div class="border-t border-gray-800 px-6 py-4 flex items-center justify-between">
        <p class="text-xs text-gray-500">
            Página <?= $pagination['current'] ?> de <?= $pagination['pages'] ?>
            — <?= number_format($pagination['total']) ?> registros
        </p>
        <div class="flex gap-1">
            <?php for ($p = max(1, $pagination['current'] - 2); $p <= min($pagination['pages'], $pagination['current'] + 2); $p++): ?>
            <a href="?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>"
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
