<?php /** Vista: Gestión de Voluntarios */ ?>
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-white"><?= e($title) ?></h2>
            <p class="text-gray-400 text-sm mt-1">Programa y gestiona los equipos de servicio para los próximos eventos.</p>
        </div>
        <?php if ($auth_can('events.edit')): ?>
        <button type="button" onclick="document.getElementById('modal-schedule').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-500 text-white font-medium py-2 px-4 rounded-xl transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Asignar Voluntario
        </button>
        <?php endif; ?>
    </div>

    <?php if (empty($upcoming)): ?>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-12 text-center">
            <div class="w-16 h-16 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="text-lg font-medium text-white mb-2">No hay eventos próximos</h3>
            <p class="text-gray-400 max-w-sm mx-auto">No hay eventos programados en el futuro cercano para asignar voluntarios.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 gap-6">
            <?php foreach ($upcoming as $event): ?>
                <?php 
                $hasTeams = isset($eventTeams[$event['id']]); 
                $teams = $hasTeams ? $eventTeams[$event['id']]['teams'] : [];
                $stats = $hasTeams ? $eventTeams[$event['id']]['stats'] : ['asignado' => 0, 'confirmado' => 0, 'ausente' => 0];
                ?>
                <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden shadow-sm">
                    <div class="p-6 border-b border-gray-800 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="bg-brand-500/10 text-brand-400 text-xs font-semibold px-2.5 py-0.5 rounded-full border border-brand-500/20">
                                    <?= e($event['type']) ?>
                                </span>
                                <h3 class="text-lg font-semibold text-white"><?= e($event['title']) ?></h3>
                            </div>
                            <div class="flex items-center gap-4 text-sm text-gray-400 mt-2">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <?= date('d M Y, h:i A', strtotime($event['start_datetime'])) ?>
                                </span>
                                <?php if ($event['location']): ?>
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <?= e($event['location']) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 text-sm bg-gray-800/50 p-3 rounded-xl border border-gray-700">
                            <div class="text-center px-3 border-r border-gray-700">
                                <span class="block text-xl font-bold text-white"><?= $stats['asignado'] ?></span>
                                <span class="text-xs text-gray-400 uppercase tracking-wider">Asignados</span>
                            </div>
                            <div class="text-center px-3 border-r border-gray-700">
                                <span class="block text-xl font-bold text-green-400"><?= $stats['confirmado'] ?></span>
                                <span class="text-xs text-gray-400 uppercase tracking-wider">Confirmados</span>
                            </div>
                            <div class="text-center px-3">
                                <span class="block text-xl font-bold text-red-400"><?= $stats['ausente'] ?></span>
                                <span class="text-xs text-gray-400 uppercase tracking-wider">Ausentes</span>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($teams)): ?>
                        <div class="p-6 text-center text-gray-500 text-sm">
                            No hay voluntarios asignados a este evento todavía.
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-800/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-800">
                                        <th class="px-6 py-3 font-medium">Voluntario</th>
                                        <th class="px-6 py-3 font-medium">Rol / Función</th>
                                        <th class="px-6 py-3 font-medium">Estado</th>
                                        <th class="px-6 py-3 font-medium text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm divide-y divide-gray-800">
                                    <?php foreach ($teams as $team): ?>
                                    <tr class="hover:bg-gray-800/30 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-brand-500/20 text-brand-400 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                                    <?= strtoupper(substr($team['member_name'] ?? '?', 0, 1)) ?>
                                                </div>
                                                <span class="text-white font-medium"><?= e($team['member_name'] ?? 'Desconocido') ?></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-gray-300"><?= ucfirst(e($team['role'])) ?></span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if ($team['status'] === 'confirmado'): ?>
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-500/10 text-green-400 border border-green-500/20">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span> Confirmado
                                                </span>
                                            <?php elseif ($team['status'] === 'ausente'): ?>
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Ausente
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span> Asignado
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <?php if ($auth_can('events.edit')): ?>
                                            <form action="/voluntarios/confirmar" method="POST" class="inline-block">
                                                <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">
                                                <input type="hidden" name="team_id" value="<?= $team['id'] ?>">
                                                <select name="status" onchange="this.form.submit()" class="bg-gray-800 border border-gray-700 text-white text-xs rounded-lg px-2 py-1 focus:ring-brand-500 focus:border-brand-500">
                                                    <option value="asignado" <?= $team['status'] === 'asignado' ? 'selected' : '' ?>>Marcar Asignado</option>
                                                    <option value="confirmado" <?= $team['status'] === 'confirmado' ? 'selected' : '' ?>>Marcar Confirmado</option>
                                                    <option value="ausente" <?= $team['status'] === 'ausente' ? 'selected' : '' ?>>Marcar Ausente</option>
                                                </select>
                                            </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Asignar Voluntario -->
<div id="modal-schedule" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="document.getElementById('modal-schedule').classList.add('hidden')"></div>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="relative bg-gray-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg w-full border border-gray-800">
            <div class="px-6 py-5 border-b border-gray-800 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Asignar Voluntario a Evento</h3>
                <button type="button" onclick="document.getElementById('modal-schedule').classList.add('hidden')" class="text-gray-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <form action="/voluntarios/programar" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Evento</label>
                    <select name="event_id" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Selecciona un evento...</option>
                        <?php foreach ($upcoming as $event): ?>
                            <option value="<?= $event['id'] ?>"><?= e($event['title']) ?> (<?= date('d/m/Y', strtotime($event['start_datetime'])) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Voluntario</label>
                    <select name="member_id" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Selecciona un miembro...</option>
                        <?php foreach ($members as $member): ?>
                            <option value="<?= $member['id'] ?>"><?= e($member['first_name'] . ' ' . $member['last_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Rol / Función</label>
                    <input type="text" name="role" required placeholder="Ej: Ujier, Alabanza, Maestro..." class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="pt-4 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-schedule').classList.add('hidden')" class="px-4 py-2 text-sm font-medium text-gray-400 hover:text-white transition-colors">Cancelar</button>
                    <button type="submit" class="bg-brand-600 hover:bg-brand-500 text-white font-medium py-2 px-6 rounded-xl text-sm transition-colors shadow-lg">Guardar Asignación</button>
                </div>
            </form>
        </div>
    </div>
</div>
