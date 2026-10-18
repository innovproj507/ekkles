<?php /** Vista: Detalle de Evento con QR y Asistencia */ ?>
<div class="max-w-5xl mx-auto">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/eventos" class="hover:text-white">Eventos</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-white"><?= e($event['title']) ?></span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Info del evento -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <h1 class="text-xl font-bold text-white"><?= e($event['title']) ?></h1>
                        <p class="text-gray-400 text-sm mt-1 capitalize"><?= e(str_replace('_', ' ', $event['type'])) ?></p>
                        <?php if (!empty($event['food_contribution'])): ?>
                        <span class="inline-flex items-center mt-2 text-xs px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">🍲 Incluye aporte de comida de las familias</span>
                        <?php endif; ?>
                    </div>
                    <span class="text-xs px-3 py-1 rounded-full flex-shrink-0 <?= match($event['status']) {
                        'publicado' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
                        'borrador'  => 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20',
                        'cancelado' => 'bg-red-500/10 text-red-400 border border-red-500/20',
                        default     => 'bg-gray-500/10 text-gray-400',
                    } ?>"><?= ucfirst($event['status']) ?></span>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="flex items-center gap-2 text-sm text-gray-300">
                        <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <?= formatDateTime($event['start_datetime']) ?>
                    </div>
                    <?php if (!empty($event['room_name'])): ?>
                    <div class="flex items-center gap-2 text-sm text-gray-300">🏛 <?= e($event['room_name']) ?></div>
                    <?php endif; ?>
                    <?php if ($event['location']): ?>
                    <div class="flex items-center gap-2 text-sm text-gray-300">
                        <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <?= e($event['location']) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($event['description']): ?>
                <p class="text-gray-300 text-sm leading-relaxed"><?= nl2br(e($event['description'])) ?></p>
                <?php endif; ?>

                <!-- Acciones -->
                <div class="flex flex-wrap gap-2 mt-5 pt-5 border-t border-gray-800">
                    <?php if ($auth_can('events.edit')): ?>
                    <a href="/eventos/<?= $event['id'] ?>/editar" class="inline-flex items-center gap-2 bg-gray-700 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-xl text-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        Editar
                    </a>
                    <?php endif; ?>
                    <a href="/asistencia/checkin/<?= $event['uuid'] ?>" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-500 text-white font-medium py-2 px-4 rounded-xl text-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Check-in Manual
                    </a>
                    <?php if ($auth_can('events.delete')): ?>
                    <button type="button" onclick="openDeleteModal()" class="inline-flex items-center gap-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 font-medium py-2 px-4 rounded-xl text-sm transition-colors ml-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Eliminar
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Lista de asistencia -->
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-white">
                        Asistencia
                        <span class="ml-2 bg-brand-500/10 text-brand-400 text-xs px-2 py-0.5 rounded-full"><?= count($attendance) ?></span>
                    </h2>
                </div>
                <div class="divide-y divide-gray-800 max-h-80 overflow-y-auto">
                    <?php if (empty($attendance)): ?>
                    <div class="px-6 py-8 text-center text-gray-500 text-sm">
                        Sin asistentes registrados aún
                    </div>
                    <?php else: ?>
                    <?php foreach ($attendance as $att): ?>
                    <div class="flex items-center gap-3 px-6 py-3">
                        <img src="<?= e(avatarUrl($att['photo'] ?? null, $att['first_name'] ?? ($att['guest_name'] ?? 'V'))) ?>"
                             alt="" class="w-8 h-8 rounded-lg object-cover flex-shrink-0">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-white truncate">
                                <?= $att['first_name'] ? e($att['first_name'] . ' ' . $att['last_name']) : e($att['guest_name'] ?? 'Visitante') ?>
                            </p>
                            <p class="text-xs text-gray-500"><?= formatDateTime($att['checkin_at']) ?> <?= $att['via_qr'] ? '· QR' : '' ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Equipos de servicio -->
            <?php if (!empty($teams)): ?>
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-800">
                    <h2 class="text-base font-semibold text-white">Equipos de Servicio</h2>
                </div>
                <?php
                $teamsByRole = [];
                foreach ($teams as $t) { $teamsByRole[$t['role']][] = $t; }
                ?>
                <div class="divide-y divide-gray-800">
                    <?php foreach ($teamsByRole as $role => $roleTeams): ?>
                    <div class="px-6 py-4">
                        <p class="text-xs font-semibold text-gray-400 uppercase mb-3"><?= ucfirst(str_replace('_', ' ', $role)) ?></p>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($roleTeams as $t): ?>
                            <div class="flex items-center gap-2 bg-gray-800 rounded-xl px-3 py-1.5">
                                <img src="<?= e(avatarUrl($t['photo'] ?? null, $t['first_name'] . ' ' . $t['last_name'])) ?>" alt="" class="w-6 h-6 rounded-lg object-cover">
                                <span class="text-xs text-gray-300"><?= e($t['first_name'] . ' ' . $t['last_name']) ?></span>
                                <span class="w-1.5 h-1.5 rounded-full <?= match($t['status']) { 'confirmado' => 'bg-emerald-400', 'ausente' => 'bg-red-400', default => 'bg-yellow-400' } ?>"></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar: QR -->
        <div class="space-y-6">
            <?php if ($qrUrl): ?>
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 text-center">
                <h3 class="text-sm font-semibold text-white mb-4">Código QR de Check-in</h3>
                <div class="bg-white rounded-2xl p-4 inline-block mb-4">
                    <img src="<?= e($qrUrl) ?>" alt="QR Check-in" class="w-40 h-40 mx-auto">
                </div>
                <p class="text-xs text-gray-400 mb-4">Escanear para registrar asistencia</p>
                <a href="<?= e($qrUrl) ?>" download="qr-<?= e($event['uuid']) ?>.png"
                   class="inline-flex items-center gap-2 bg-gray-700 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-xl text-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Descargar QR
                </a>
            </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                <h3 class="text-sm font-semibold text-white mb-4">Estadísticas</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400">Total asistentes</span>
                        <span class="text-sm font-bold text-white"><?= count($attendance) ?></span>
                    </div>
                    <?php if ($event['max_capacity']): ?>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400">Cupos máximos</span>
                        <span class="text-sm font-bold text-white"><?= $event['max_capacity'] ?></span>
                    </div>
                    <div class="mt-2">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>Ocupación</span>
                            <span><?= round((count($attendance) / $event['max_capacity']) * 100) ?>%</span>
                        </div>
                        <div class="bg-gray-800 rounded-full h-2">
                            <div class="bg-brand-500 h-2 rounded-full transition-all"
                                 style="width: <?= min(100, round((count($attendance) / $event['max_capacity']) * 100)) ?>%"></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-400">Via QR</span>
                        <span class="text-sm font-bold text-white"><?= count(array_filter($attendance, fn($a) => $a['via_qr'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Eliminar -->
<div id="modal-delete" class="fixed inset-0 bg-gray-950/80 backdrop-blur-sm z-50 hidden opacity-0 transition-opacity duration-300">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20">
        <div class="bg-gray-900 border border-gray-800 rounded-3xl w-full max-w-md p-8 shadow-2xl transform scale-95 transition-transform duration-300" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-white">Eliminar Evento</h3>
                <button type="button" onclick="closeDeleteModal()" class="text-gray-400 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form action="/eventos/<?= $event['id'] ?>/eliminar" method="POST" class="space-y-4">
                <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">
                <p class="text-sm text-gray-300 mb-4">¿Estás seguro de que deseas eliminar el evento <strong><?= e($event['title']) ?></strong>? Esta acción lo ocultará del sistema y dejará un registro en la bitácora.</p>
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
function openDeleteModal() {
    const m = document.getElementById('modal-delete');
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
