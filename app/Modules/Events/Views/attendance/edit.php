<?php /** Vista: Editar Registro de Asistencia */ ?>
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white flex items-center gap-2">
                <span class="text-brand-400">📝</span> Editar Registro de Asistencia
            </h1>
            <p class="text-sm text-gray-400 mt-1">Modificar los datos de asistencia registrados.</p>
        </div>
        <a href="/asistencia" class="text-sm font-medium text-gray-400 hover:text-white transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Volver
        </a>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl shadow-xl overflow-hidden">
        <form action="/asistencia/<?= $record['id'] ?>/editar" method="POST" class="p-6 space-y-6">
            <input type="hidden" name="_csrf_token" value="<?= e($csrf_token ?? '') ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Información Inmutable -->
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Fecha de Registro</label>
                    <p class="text-white font-semibold bg-gray-800/50 p-3 rounded-xl border border-gray-700/50">
                        <?= formatDateTime($record['created_at'], 'd M Y, H:i') ?>
                    </p>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Tipo de Registro</label>
                    <p class="text-white font-semibold bg-gray-800/50 p-3 rounded-xl border border-gray-700/50">
                        <?php if ($record['first_name']): ?>
                            Miembro (<?= e(trim($record['first_name'] . ' ' . $record['last_name'])) ?>)
                        <?php elseif ($record['registered_name']): ?>
                            Manual (<?= e($record['registered_name']) ?>)
                        <?php else: ?>
                            Anónimo
                        <?php endif; ?>
                    </p>
                </div>
                
                <!-- Campos Editables -->
                <div class="md:col-span-2">
                    <label for="registered_name" class="block text-sm font-medium text-gray-300 mb-1">Nombre del Asistente (Opcional)</label>
                    <input type="text" id="registered_name" name="registered_name" value="<?= e($record['registered_name']) ?>" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors" placeholder="Nombre manual" <?= $record['member_id'] ? 'disabled title="No se puede cambiar el nombre porque está vinculado a un miembro oficial"' : '' ?>>
                    <?php if ($record['member_id']): ?>
                        <p class="text-xs text-brand-400 mt-1">Este registro está asociado al miembro oficial <?= e(trim($record['first_name'] . ' ' . $record['last_name'])) ?>.</p>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="adults_count" class="block text-sm font-medium text-gray-300 mb-1">Adultos</label>
                    <input type="number" id="adults_count" name="adults_count" value="<?= $record['adults_count'] ?>" min="0" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors">
                </div>

                <div>
                    <label for="children_count" class="block text-sm font-medium text-gray-300 mb-1">Niños</label>
                    <input type="number" id="children_count" name="children_count" value="<?= $record['children_count'] ?>" min="0" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors">
                </div>

                <div>
                    <label for="guests_count" class="block text-sm font-medium text-gray-300 mb-1">Invitados</label>
                    <input type="number" id="guests_count" name="guests_count" value="<?= $record['guests_count'] ?>" min="0" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-3 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition-colors">
                </div>
            </div>

            <div class="pt-6 border-t border-gray-800 flex items-center justify-end gap-3">
                <a href="/asistencia" class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-300 hover:text-white hover:bg-gray-800 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-500 text-white text-sm font-bold rounded-xl transition-all shadow-lg shadow-brand-500/25 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
