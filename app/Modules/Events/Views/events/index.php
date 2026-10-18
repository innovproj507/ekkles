<?php /** Vista: Lista de Eventos */ ?>
<!-- Toolbar -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <p class="text-gray-400 text-sm"><span class="text-white font-semibold"><?= number_format($pagination['total']) ?></span> eventos</p>
    <div class="flex items-center gap-3">
        <a href="/eventos/calendario-pdf?month=<?= date('n') ?>&year=<?= date('Y') ?>" target="_blank" class="inline-flex items-center gap-2 bg-gray-800 border border-gray-700 hover:bg-gray-700 text-white font-medium py-2.5 px-4 rounded-xl text-sm transition-all">
            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Exportar Mes
        </a>
        <?php if ($auth_can('events.create')): ?>
        <a href="/eventos/tipos" class="inline-flex items-center gap-2 bg-gray-800 border border-gray-700 hover:bg-gray-700 text-white font-medium py-2.5 px-4 rounded-xl text-sm transition-all">
            <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            Tipos de Eventos
        </a>
        <a href="/salones" class="inline-flex items-center gap-2 bg-gray-800 border border-gray-700 hover:bg-gray-700 text-white font-medium py-2.5 px-4 rounded-xl text-sm transition-all">
            🏛 Salones
        </a>
        <a href="/eventos/crear" class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-500 hover:to-purple-500 text-white font-medium py-2.5 px-5 rounded-xl text-sm transition-all shadow-lg shadow-brand-500/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Crear Evento
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Próximos eventos destacados -->
<?php if (!empty($upcoming)): ?>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    <?php foreach (array_slice($upcoming, 0, 3) as $e): ?>
    <a href="/eventos/<?= $e['id'] ?>"
       class="<?= !empty($e['food_contribution'])
           ? 'bg-gradient-to-br from-amber-600/20 to-orange-600/10 border border-amber-500/40 hover:border-amber-500/70'
           : 'bg-gradient-to-br from-brand-600/10 to-purple-600/10 border border-brand-500/20 hover:border-brand-500/40' ?> rounded-2xl p-5 transition-all group">
        <div class="flex items-start justify-between mb-3">
            <div class="w-12 h-12 bg-brand-500/20 rounded-xl flex flex-col items-center justify-center">
                <span class="text-lg font-bold text-brand-400 leading-none"><?= date('d', strtotime($e['start_datetime'])) ?></span>
                <span class="text-xs text-brand-500 uppercase"><?= date('M', strtotime($e['start_datetime'])) ?></span>
            </div>
            <span class="text-xs bg-gray-800 text-gray-400 px-2 py-0.5 rounded-full"><?= e($e['type']) ?></span>
        </div>
        <h3 class="text-base font-semibold text-white group-hover:text-brand-400 transition-colors truncate"><?= e($e['title']) ?></h3>
        <p class="text-xs text-gray-400 mt-1"><?= date('H:i', strtotime($e['start_datetime'])) ?><?= $e['room_name'] ? ' · ' . e($e['room_name']) : ($e['location'] ? ' · ' . e(truncate($e['location'], 25)) : '') ?></p>
        <?php if (!empty($e['food_contribution'])): ?>
        <span class="inline-flex items-center mt-2 text-xs px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300">🍲 Aporte de comida</span>
        <?php endif; ?>
        <p class="text-xs text-gray-500 mt-2"><?= $e['attendance_count'] ?> asistentes registrados</p>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Filtros -->
<form method="GET" action="/eventos" class="bg-gray-900 border border-gray-800 rounded-2xl p-4 mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
        <input type="text" name="search" value="<?= e($filters['search']) ?>" placeholder="Buscar evento..."
               class="bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-gray-500">
        <select name="event_type_id" class="bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">Todos los tipos</option>
            <?php foreach ($eventTypes as $t): ?>
            <option value="<?= $t['id'] ?>" <?= ($filters['event_type_id'] ?? '') == $t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status" class="bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            <option value="">Todos los estados</option>
            <option value="publicado" <?= $filters['status'] === 'publicado' ? 'selected' : '' ?>>Publicados</option>
            <option value="borrador" <?= $filters['status'] === 'borrador' ? 'selected' : '' ?>>Borradores</option>
            <option value="cancelado" <?= $filters['status'] === 'cancelado' ? 'selected' : '' ?>>Cancelados</option>
        </select>
        <button type="submit" class="bg-brand-600 hover:bg-brand-500 text-white font-medium py-2.5 px-4 rounded-xl text-sm transition-colors">Filtrar</button>
    </div>
</form>

<!-- Lista de eventos -->
<div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-800 bg-gray-800/50">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Evento</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">Tipo</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Fecha</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Asistencia</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Estado</th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                <?php if (empty($events)): ?>
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 text-sm">No se encontraron eventos</td></tr>
                <?php else: ?>
                <?php foreach ($events as $ev): ?>
                <tr class="transition-colors group <?= !empty($ev['food_contribution']) ? 'bg-amber-500/5 hover:bg-amber-500/10 border-l-4 border-amber-500' : 'hover:bg-gray-800/30' ?>">
                    <td class="px-6 py-4">
                        <a href="/eventos/<?= $ev['id'] ?>" class="text-sm font-medium text-white hover:text-brand-400 transition-colors"><?= e($ev['title']) ?></a>
                        <?php if (!empty($ev['food_contribution'])): ?>
                        <span class="ml-1 inline-flex items-center text-xs px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300">🍲 Aporte de comida</span>
                        <?php endif; ?>
                        <?php if ($ev['room_name'] || $ev['location']): ?><p class="text-xs text-gray-500 mt-0.5"><?= e(trim(($ev['room_name'] ?? '') . ($ev['room_name'] && $ev['location'] ? ' · ' : '') . truncate($ev['location'] ?? '', 40))) ?></p><?php endif; ?>
                    </td>
                    <td class="px-6 py-4 hidden md:table-cell">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full" style="background-color: <?= e($ev['type_color'] ?? '#4f46e5') ?>"></span>
                            <span class="text-xs text-gray-400 capitalize"><?= e($ev['type']) ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4 hidden lg:table-cell"><span class="text-sm text-gray-300"><?= formatDateTime($ev['start_datetime']) ?></span></td>
                    <td class="px-6 py-4 hidden lg:table-cell"><span class="text-sm text-gray-300"><?= $ev['attendance_count'] ?> personas</span></td>
                    <td class="px-6 py-4">
                        <span class="text-xs px-2 py-0.5 rounded-full <?= match($ev['status']) {
                            'publicado' => 'bg-emerald-500/10 text-emerald-400',
                            'borrador'  => 'bg-yellow-500/10 text-yellow-400',
                            'cancelado' => 'bg-red-500/10 text-red-400',
                            default     => 'bg-gray-500/10 text-gray-400',
                        } ?>"><?= ucfirst($ev['status']) ?></span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="/eventos/<?= $ev['id'] ?>" class="text-gray-400 hover:text-brand-400 p-1.5 rounded-lg hover:bg-brand-500/10 transition-all" title="Ver">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <?php if ($auth_can('events.edit')): ?>
                            <a href="/eventos/<?= $ev['id'] ?>/editar" class="text-gray-400 hover:text-amber-400 p-1.5 rounded-lg hover:bg-amber-500/10 transition-all" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </a>
                            <?php endif; ?>
                            <?php if ($auth_can('events.delete')): ?>
                            <button type="button" onclick="openDeleteModal(<?= $ev['id'] ?>, '<?= e(addslashes($ev['title'])) ?>')" class="text-gray-400 hover:text-red-400 p-1.5 rounded-lg hover:bg-red-500/10 transition-all" title="Eliminar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
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
        <p class="text-xs text-gray-500">Página <?= $pagination['current'] ?> de <?= $pagination['pages'] ?></p>
        <div class="flex gap-1">
            <?php for ($p = max(1, $pagination['current'] - 2); $p <= min($pagination['pages'], $pagination['current'] + 2); $p++): ?>
            <a href="?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>"
               class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-medium transition-colors <?= $p === $pagination['current'] ? 'bg-brand-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800' ?>">
                <?= $p ?>
            </a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Eliminar -->
<div id="modal-delete" class="fixed inset-0 bg-gray-950/80 backdrop-blur-sm z-50 hidden opacity-0 transition-opacity duration-300">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20">
        <div class="bg-gray-900 border border-gray-800 rounded-3xl w-full max-w-md p-8 shadow-2xl transform scale-95 transition-transform duration-300" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-white">Eliminar Evento</h3>
                <button type="button" onclick="closeDeleteModal()" class="text-gray-400 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form id="delete-form" method="POST" class="space-y-4">
                <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">
                <p class="text-sm text-gray-300 mb-4">¿Estás seguro de que deseas eliminar el evento <strong id="delete-event-title"></strong>? Esta acción lo ocultará del sistema y dejará un registro en la bitácora.</p>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Causal de eliminación <span class="text-red-400">*</span></label>
                    <textarea name="reason" required rows="3" placeholder="Ej: Evento duplicado, Cancelado por lluvia..." class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
                </div>
                <div class="pt-6 border-t border-gray-800 flex justify-end gap-3">
                    <button type="button" onclick="closeDeleteModal()" class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-400 hover:text-white transition-colors">Cancelar</button>
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-medium px-5 py-2.5 rounded-xl text-sm transition-colors">Confirmar Eliminación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openDeleteModal(id, title) {
    const m = document.getElementById('modal-delete');
    document.getElementById('delete-form').action = '/eventos/' + id + '/eliminar';
    document.getElementById('delete-event-title').textContent = title;
    
    m.classList.remove('hidden');
    setTimeout(() => {
        m.classList.remove('opacity-0');
        m.querySelector('div > div').classList.remove('scale-95');
    }, 10);
}

function closeDeleteModal() {
    const m = document.getElementById('modal-delete');
    m.classList.add('opacity-0');
    m.querySelector('div > div').classList.add('scale-95');
    setTimeout(() => { m.classList.add('hidden'); }, 300);
}
</script>
