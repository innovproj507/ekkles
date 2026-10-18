<?php /** Vista: Detalles de Grupo Familiar */ ?>
<div class="max-w-6xl mx-auto">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/familias" class="hover:text-white">Familias</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-white"><?= e($family['name']) ?></span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna Izquierda: Información y Edición -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Detalles Generales / Formulario de Edición -->
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-semibold text-white">Información de la Familia</h2>
                    <span class="text-xs text-brand-400 font-medium bg-brand-500/10 px-2 py-0.5 rounded-full">🏠 Sede</span>
                </div>

                <?php if ($auth_can('families.edit')): ?>
                <!-- Formulario de Edición -->
                <form method="POST" action="/familias/<?= $family['id'] ?>/editar" class="space-y-4">
                    <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">

                    <!-- Nombre -->
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Nombre de Familia</label>
                        <input type="text" name="name" value="<?= e($family['name']) ?>" required
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <!-- Cabeza de Familia -->
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Cabeza de Familia</label>
                        <select name="head_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="">Ninguno</option>
                            <?php foreach ($family['members'] as $m): ?>
                            <option value="<?= $m['member_id'] ?>" <?= $family['head_id'] == $m['member_id'] ? 'selected' : '' ?>>
                                <?= e($m['first_name'] . ' ' . $m['last_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Dirección -->
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Dirección de Residencia</label>
                        <input type="text" name="address" value="<?= e($family['address'] ?? '') ?>"
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <!-- Notas -->
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Notas / Observaciones</label>
                        <textarea name="notes" rows="3"
                                  class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"><?= e($family['notes'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-brand-600 hover:bg-brand-500 text-white font-medium py-2.5 px-4 rounded-xl text-sm transition-colors shadow-lg shadow-brand-500/10">
                        💾 Guardar Cambios
                    </button>
                </form>
                <?php else: ?>
                <!-- Vista de Solo Lectura -->
                <div class="space-y-4">
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider block font-semibold">Nombre de Familia</span>
                        <p class="text-sm text-white mt-1"><?= e($family['name']) ?></p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider block font-semibold">Cabeza de Familia</span>
                        <p class="text-sm text-gray-300 mt-1"><?= e($family['head_name'] ?? 'No asignado') ?></p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider block font-semibold">Dirección</span>
                        <p class="text-sm text-gray-400 mt-1"><?= e($family['address'] ?? 'No especificada') ?></p>
                    </div>
                    <?php if ($family['notes']): ?>
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-wider block font-semibold">Notas</span>
                        <p class="text-sm text-gray-400 mt-1"><?= nl2br(e($family['notes'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Columna Derecha: Miembros Integrantes -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-base font-semibold text-white">Integrantes de la Familia</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Integrantes vinculados a esta familia</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <?php if (empty($family['members'])): ?>
                    <p class="text-gray-500 text-sm py-6 text-center">No hay integrantes registrados en esta familia</p>
                    <?php else: ?>
                    <?php foreach ($family['members'] as $m): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-950 border border-gray-800/80 rounded-2xl hover:border-gray-700/60 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Foto -->
                            <img src="<?= e(avatarUrl($m['photo'], $m['first_name'] . ' ' . $m['last_name'])) ?>"
                                 alt="" class="w-10 h-10 rounded-xl object-cover flex-shrink-0">
                            <!-- Datos -->
                            <div class="min-w-0">
                                <a href="/miembros/<?= $m['member_id'] ?>" class="text-sm font-semibold text-white hover:text-brand-400 transition-colors truncate block">
                                    <?= e($m['first_name'] . ' ' . $m['last_name']) ?>
                                    <?php if ($family['head_id'] == $m['member_id']): ?>
                                    <span class="text-[9px] bg-brand-500/20 text-brand-400 font-bold px-1.5 py-0.5 rounded-full ml-1.5 uppercase">Jefe de Hogar</span>
                                    <?php endif; ?>
                                </a>
                                <div class="flex items-center gap-2 mt-1">
                                    <!-- Parentesco -->
                                    <span class="text-xs text-gray-400 capitalize">
                                        Parentesco: <strong class="text-gray-300 font-medium"><?= e($m['relationship']) ?></strong>
                                    </span>
                                    <span class="text-gray-600">•</span>
                                    <!-- Edad -->
                                    <span class="text-xs text-gray-500">
                                        <?= $m['birth_date'] ? calcAge($m['birth_date']) . ' años' : '—' ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Status + Shortcut -->
                        <div class="flex items-center gap-4">
                            <?php if (empty($m['is_converted']) && $m['status'] !== 'visitante'): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-500/15 text-amber-300">No convertido</span>
                            <?php endif; ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= memberStatusBadge($m['status']) ?>">
                                <?= memberStatusLabel($m['status']) ?>
                            </span>

                            <a href="/miembros/<?= $m['member_id'] ?>" class="text-gray-400 hover:text-white p-2 hover:bg-gray-800 rounded-lg transition-colors" title="Ver Perfil">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
