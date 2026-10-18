<?php /** Vista: Crear/Editar Seguimiento de Discipulado */ ?>
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/pastoral" class="hover:text-white">Seguimiento de Discipulado</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-white"><?= e($title) ?></span>
    </div>

    <form method="POST" action="/pastoral/<?= isset($mode) && $mode === 'edit' ? $follow['id'] . '/editar' : 'crear' ?>" class="space-y-6">
        <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Miembro <span class="text-red-400">*</span></label>
                    <select name="member_id" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Selecciona un miembro...</option>
                        <?php foreach ($members as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= ($follow['member_id'] ?? '') == $m['id'] ? 'selected' : '' ?>>
                                <?= e($m['first_name'] . ' ' . $m['last_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Asignado A</label>
                    <select name="assigned_to" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">(Asignarme a mí mismo)</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= ($follow['assigned_to'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                                <?= e($u['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Tipo de Seguimiento <span class="text-red-400">*</span></label>
                    <select name="type" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <?php foreach (['llamada', 'visita', 'mensaje', 'reunion', 'oracion'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($follow['type'] ?? 'llamada') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Etapa del Pipeline <span class="text-red-400">*</span></label>
                    <select name="pipeline_stage" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <?php foreach (['visitante', 'nuevo_creyente', 'miembro_bautizado', 'lider_formacion'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($follow['pipeline_stage'] ?? 'visitante') === $s ? 'selected' : '' ?>>
                                <?= str_replace('_', ' ', ucfirst($s)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Fecha Programada</label>
                    <input type="datetime-local" name="scheduled_at" value="<?= $follow['scheduled_at'] ?? '' ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Prioridad</label>
                    <select name="priority" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="alta" <?= ($follow['priority'] ?? '') === 'alta' ? 'selected' : '' ?>>Alta</option>
                        <option value="media" <?= ($follow['priority'] ?? 'media') === 'media' ? 'selected' : '' ?>>Media</option>
                        <option value="baja" <?= ($follow['priority'] ?? '') === 'baja' ? 'selected' : '' ?>>Baja</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Asunto o Motivo <span class="text-red-400">*</span></label>
                <input type="text" name="subject" required value="<?= e($follow['subject'] ?? '') ?>" placeholder="Ej: Llamada de bienvenida..."
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Notas adicionales</label>
                <textarea name="notes" rows="4" placeholder="Escribe aquí los detalles del seguimiento..."
                          class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"><?= e($follow['notes'] ?? '') ?></textarea>
            </div>

        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="/pastoral" class="px-6 py-2.5 text-sm font-medium text-gray-400 hover:text-white transition-colors">Cancelar</a>
            <button type="submit" class="bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-500 hover:to-purple-500 text-white font-medium py-2.5 px-8 rounded-xl text-sm transition-all shadow-lg">
                <?= isset($mode) && $mode === 'edit' ? 'Actualizar Seguimiento' : 'Guardar Seguimiento' ?>
            </button>
        </div>
    </form>
</div>
