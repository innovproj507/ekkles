<?php /** Vista: Detalles del Seguimiento */ ?>
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/pastoral" class="hover:text-white transition-colors">Seguimiento de Discipulado</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-white">Detalle Seguimiento #<?= $follow['id'] ?></span>
    </div>

    <div class="flex flex-col md:flex-row md:items-start gap-6">
        <!-- Columna Izquierda: Datos del Seguimiento -->
        <div class="flex-1 space-y-6">
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-white"><?= e($follow['subject']) ?></h2>
                    <?php if ($follow['status'] === 'completado'): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500/10 text-green-400 border border-green-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-400 mr-1.5"></span> Completado
                        </span>
                    <?php elseif ($follow['status'] === 'en_proceso'): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-400 mr-1.5"></span> En Proceso
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 mr-1.5"></span> Pendiente
                        </span>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm mb-6 border-b border-gray-800 pb-6">
                    <div>
                        <p class="text-gray-500 mb-1">Tipo de Seguimiento</p>
                        <p class="text-gray-300 capitalize"><?= e($follow['type']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 mb-1">Etapa del Pipeline</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-800 text-gray-400 border border-gray-700">
                            <?= str_replace('_', ' ', strtoupper($follow['pipeline_stage'])) ?>
                        </span>
                    </div>
                    <div>
                        <p class="text-gray-500 mb-1">Prioridad</p>
                        <p class="text-gray-300 capitalize"><?= e($follow['priority']) ?></p>
                    </div>
                    <div>
                        <p class="text-gray-500 mb-1">Fecha Programada</p>
                        <p class="text-gray-300">
                            <?= $follow['scheduled_at'] ? date('d M Y, h:i A', strtotime($follow['scheduled_at'])) : 'No asignada' ?>
                        </p>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-white mb-2">Notas:</h3>
                    <div class="bg-gray-800/50 rounded-xl p-4 text-sm text-gray-300 whitespace-pre-wrap border border-gray-700">
                        <?= e($follow['notes'] ?: 'No hay notas registradas.') ?>
                    </div>
                </div>
            </div>

            <!-- Formulario para actualizar estado si no está completado -->
            <?php if ($follow['status'] !== 'completado'): ?>
            <div class="bg-gray-900 border border-brand-500/30 rounded-2xl p-6 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-brand-500"></div>
                <h3 class="text-lg font-semibold text-white mb-4">Actualizar Estado</h3>
                <form action="/pastoral/<?= $follow['id'] ?>/editar" method="POST" class="space-y-4">
                    <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Nuevo Estado</label>
                            <select name="status" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-brand-500 focus:border-brand-500">
                                <option value="pendiente" <?= $follow['status'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                <option value="en_proceso" <?= $follow['status'] === 'en_proceso' ? 'selected' : '' ?>>En Proceso</option>
                                <option value="completado">Completado</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Nueva Etapa Pipeline</label>
                            <select name="pipeline_stage" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-brand-500 focus:border-brand-500">
                                <?php foreach (['visitante', 'nuevo_creyente', 'miembro_bautizado', 'lider_formacion'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $follow['pipeline_stage'] === $s ? 'selected' : '' ?>>
                                        <?= str_replace('_', ' ', ucfirst($s)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Añadir/Modificar Notas</label>
                        <textarea name="notes" rows="3" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-brand-500 focus:border-brand-500"><?= e($follow['notes'] ?? '') ?></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-brand-600 hover:bg-brand-500 text-white px-6 py-2 rounded-xl text-sm font-medium transition-colors">Guardar Actualización</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <!-- Columna Derecha: Información del Miembro -->
        <div class="w-full md:w-80">
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-sm text-center">
                <div class="w-20 h-20 bg-gray-800 rounded-full flex items-center justify-center text-gray-400 text-2xl font-bold border border-gray-700 mx-auto mb-4">
                    <?= strtoupper(substr($member['first_name'] ?? 'U', 0, 1)) ?>
                </div>
                <h3 class="text-lg font-bold text-white"><?= e(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) ?></h3>
                <p class="text-sm text-gray-400 mt-1"><?= e($member['email'] ?? 'Sin correo electrónico') ?></p>
                <p class="text-sm text-gray-400"><?= e($member['phone'] ?? 'Sin teléfono') ?></p>
                
                <hr class="border-gray-800 my-4">
                
                <a href="/miembros/<?= $member['id'] ?>" class="block w-full text-center bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium py-2 rounded-xl border border-gray-700 transition-colors">
                    Ver Ficha del Miembro
                </a>
            </div>
        </div>
    </div>
</div>
