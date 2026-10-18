<?php /** Vista: Crear/Editar Evento */
// datetime-local necesita el formato 'Y-m-d\TH:i' (la BD devuelve 'Y-m-d H:i:s')
$dtLocal = fn($v) => $v ? date('Y-m-d\TH:i', strtotime($v)) : '';
?>
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/eventos" class="hover:text-white">Eventos</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-white"><?= e($title) ?></span>
    </div>

    <form method="POST" action="/eventos/<?= isset($editing) ? $event['id'] . '/editar' : 'crear' ?>" class="space-y-6">
        <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">

        <?php if (!empty($errors)): ?>
        <div class="bg-red-500/10 border border-red-500/20 rounded-2xl p-4">
            <?php foreach ($errors as $fe): foreach ($fe as $err): ?>
            <p class="text-red-300 text-sm">• <?= e($err) ?></p>
            <?php endforeach; endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-4">
            <h2 class="text-base font-semibold text-white mb-2">Información del Evento</h2>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Título del Evento <span class="text-red-400">*</span></label>
                <input type="text" name="title" required value="<?= e($old['title'] ?? '') ?>"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                       placeholder="ej: Culto Dominical, Conferencia de Jóvenes...">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Tipo de Evento <span class="text-red-400">*</span></label>
                    <select name="event_type_id" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <?php foreach ($eventTypes as $t): ?>
                        <option value="<?= $t['id'] ?>" <?= ($old['event_type_id'] ?? '') == $t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Ministerio</label>
                    <select name="ministry_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Evento general</option>
                        <?php foreach ($ministries as $min): ?>
                        <option value="<?= $min['id'] ?>" <?= ($old['ministry_id'] ?? '') == $min['id'] ? 'selected' : '' ?>><?= e($min['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Inicio <span class="text-red-400">*</span></label>
                    <input type="datetime-local" name="start_datetime" required value="<?= e($dtLocal($old['start_datetime'] ?? '')) ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Fin</label>
                    <input type="datetime-local" name="end_datetime" value="<?= e($dtLocal($old['end_datetime'] ?? '')) ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Salón</label>
                    <select name="room_id" id="room_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Sin salón asignado</option>
                        <?php foreach ($rooms ?? [] as $room): ?>
                        <option value="<?= $room['id'] ?>" <?= ($old['room_id'] ?? '') == $room['id'] ? 'selected' : '' ?>>
                            <?= e($room['name']) ?><?= $room['capacity'] ? ' (' . (int)$room['capacity'] . ' personas)' : '' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <a href="/salones" class="text-xs text-brand-400 hover:text-brand-300 mt-1 inline-block">Administrar salones</a>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Lugar / dirección (opcional)</label>
                    <input type="text" name="location" value="<?= e($old['location'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                           placeholder="Auditorio principal, salón B...">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Cupos Máximos</label>
                    <input type="number" name="max_capacity" min="1" value="<?= e($old['max_capacity'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                           placeholder="Vacío = sin límite">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Estado</label>
                    <select name="status" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="publicado" <?= ($old['status'] ?? 'publicado') === 'publicado' ? 'selected' : '' ?>>Publicado</option>
                        <option value="borrador"  <?= ($old['status'] ?? '') === 'borrador' ? 'selected' : '' ?>>Borrador</option>
                        <option value="cancelado" <?= ($old['status'] ?? '') === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>
                <div class="flex items-center gap-3 pt-4">
                    <input type="checkbox" id="registration_open" name="registration_open" value="1"
                           <?= !empty($old['registration_open']) ? 'checked' : '' ?>
                           class="w-4 h-4 text-brand-600 bg-gray-800 border-gray-700 rounded focus:ring-brand-500">
                    <label for="registration_open" class="text-sm text-gray-300">Registro en línea habilitado</label>
                </div>
                <div class="flex items-center gap-3 pt-4">
                    <input type="checkbox" id="food_contribution" name="food_contribution" value="1"
                           <?= !empty($old['food_contribution']) ? 'checked' : '' ?>
                           class="w-4 h-4 text-amber-500 bg-gray-800 border-gray-700 rounded focus:ring-amber-500">
                    <label for="food_contribution" class="text-sm text-amber-300">🍲 Incluye aporte de comida de las familias</label>
                </div>
            </div>

            <!-- Alerta de salón ocupado (se llena al elegir salón/horario, y al volver del servidor) -->
            <div id="room-alert" class="<?= !empty($old['room_conflict']) ? '' : 'hidden' ?> bg-amber-500/10 border border-amber-500/30 rounded-xl p-4">
                <p class="text-amber-300 text-sm font-semibold mb-1">⚠ Este salón ya está ocupado en ese horario</p>
                <ul id="room-alert-list" class="text-amber-200 text-xs space-y-0.5 list-disc pl-4"></ul>
                <label class="flex items-center gap-2 mt-3 text-xs text-gray-300">
                    <input type="checkbox" name="confirm_room_conflict" value="1" class="rounded bg-gray-800 border-gray-600 text-amber-500">
                    Guardar de todos modos
                </label>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Descripción</label>
                <textarea name="description" rows="3"
                          class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"
                          placeholder="Descripción del evento..."><?= e($old['description'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="flex items-center justify-between pb-6">
            <a href="/eventos" class="text-gray-400 hover:text-white text-sm font-medium transition-colors">← Cancelar</a>
            <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-500 hover:to-purple-500 text-white font-semibold py-3 px-8 rounded-xl text-sm transition-all shadow-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <?= isset($editing) ? 'Actualizar Evento' : 'Crear Evento' ?>
            </button>
        </div>
    </form>
</div>

<script>
(function () {
    const room  = document.getElementById('room_id');
    const start = document.querySelector('[name="start_datetime"]');
    const end   = document.querySelector('[name="end_datetime"]');
    const alertBox = document.getElementById('room-alert');
    const list  = document.getElementById('room-alert-list');
    const exclude = <?= json_encode(isset($editing) ? (int)$event['id'] : 0) ?>;

    function check() {
        if (!room.value || !start.value) { alertBox.classList.add('hidden'); return; }
        const q = new URLSearchParams({ room_id: room.value, start: start.value, end: end.value, exclude });
        fetch('/eventos/disponibilidad?' + q)
            .then(r => r.json())
            .then(data => {
                list.innerHTML = '';
                (data.conflicts || []).forEach(c => {
                    const li = document.createElement('li');
                    li.textContent = c.title + ' — ' + c.when;
                    list.appendChild(li);
                });
                alertBox.classList.toggle('hidden', !(data.conflicts || []).length);
            })
            .catch(() => {});
    }

    [room, start, end].forEach(el => el && el.addEventListener('change', check));
    check();
})();
</script>
