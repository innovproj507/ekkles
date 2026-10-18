<?php /** Vista: Panel de Control de Asistencia Simple QR */ ?>
<div class="space-y-8">
    <!-- Encabezado / Descripción -->
    <div class="bg-gradient-to-r from-brand-600/10 to-purple-600/5 border border-brand-500/15 rounded-2xl p-6">
        <h1 class="text-xl font-bold text-white">Asistencia Rápida por Código QR</h1>
        <p class="text-sm text-gray-400 mt-1">
            Los miembros pueden registrar su propia asistencia escaneando el código QR con sus teléfonos móviles.
            No necesitan instalar nada, solo escanear e indicar cuántas personas asisten con ellos.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Columna Izquierda: El Código QR -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-gray-900 border border-gray-800 rounded-3xl p-8 text-center shadow-2xl relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-brand-500 to-purple-500"></div>
                
                <h2 class="text-lg font-bold text-white mb-2">Escanea para Registrarte</h2>
                <p class="text-xs text-gray-400 mb-6">Apunta la cámara de tu celular aquí</p>
                
                <div class="bg-white p-4 rounded-2xl inline-block shadow-lg mx-auto mb-6">
                    <img src="<?= $qrImage ?>" alt="Código QR de Asistencia" class="w-48 h-48">
                </div>
                
                <div class="border-t border-gray-800 pt-6">
                    <p class="text-xs text-gray-500 mb-2">¿No puedes escanear? Ingresa a este enlace:</p>
                    <div class="flex items-center gap-2 bg-gray-800/50 rounded-xl p-2 border border-gray-700/50">
                        <input type="text" readonly value="<?= e($qrUrl) ?>" class="bg-transparent border-none text-xs text-gray-300 w-full focus:ring-0 cursor-copy" onclick="this.select(); document.execCommand('copy');">
                        <button onclick="navigator.clipboard.writeText('<?= e($qrUrl) ?>')" class="text-brand-400 hover:text-brand-300 p-2" title="Copiar Enlace">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Estadísticas -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Resumen de Hoy -->
            <div>
                <h2 class="text-base font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="text-brand-400">⚡</span> Asistencia de Hoy (<?= date('d/m/Y') ?>)
                </h2>
                
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
                        <p class="text-xs text-gray-500 uppercase font-semibold tracking-wider mb-1">Total Hoy</p>
                        <p class="text-3xl font-bold text-white"><?= $todayTotals['total'] ?></p>
                    </div>
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
                        <p class="text-xs text-gray-500 uppercase font-semibold tracking-wider mb-1">Adultos</p>
                        <p class="text-3xl font-bold text-brand-400"><?= $todayTotals['adults'] ?></p>
                    </div>
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
                        <p class="text-xs text-gray-500 uppercase font-semibold tracking-wider mb-1">Niños</p>
                        <p class="text-3xl font-bold text-blue-400"><?= $todayTotals['children'] ?></p>
                    </div>
                    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
                        <p class="text-xs text-gray-500 uppercase font-semibold tracking-wider mb-1">Invitados</p>
                        <p class="text-3xl font-bold text-purple-400"><?= $todayTotals['guests'] ?></p>
                    </div>
                </div>
            </div>

            <!-- Registros Recientes -->
            <div class="pt-4">
                <h2 class="text-base font-semibold text-white mb-4 flex items-center gap-2">
                    <span class="text-gray-400">📝</span> Últimos Registros
                </h2>
                
                <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[600px]">
                            <thead>
                                <tr class="bg-gray-800/50 border-b border-gray-800">
                                    <th class="px-4 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Fecha / Hora</th>
                                    <th class="px-4 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Asistente</th>
                                    <th class="px-4 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Adultos</th>
                                    <th class="px-4 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Niños</th>
                                    <th class="px-4 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Invitados</th>
                                    <th class="px-4 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800">
                                <?php if (empty($recentRecords)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">No hay registros recientes.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($recentRecords as $record): ?>
                                    <tr class="hover:bg-gray-800/20 transition-colors">
                                        <td class="px-4 py-4 text-sm font-medium text-white"><?= formatDateTime($record['created_at'], 'd M, H:i') ?></td>
                                        <td class="px-4 py-4 text-sm text-gray-300">
                                            <?php if ($record['first_name']): ?>
                                                <?= e(trim($record['first_name'] . ' ' . $record['last_name'])) ?>
                                                <span class="inline-flex items-center rounded-full bg-brand-500/10 px-2 py-0.5 text-xs font-medium text-brand-400 ml-2">Miembro</span>
                                            <?php elseif ($record['registered_name']): ?>
                                                <?= e($record['registered_name']) ?>
                                            <?php else: ?>
                                                <span class="text-gray-500 italic">Anónimo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-400 text-center"><?= $record['adults_count'] ?></td>
                                        <td class="px-4 py-4 text-sm text-gray-400 text-center"><?= $record['children_count'] ?></td>
                                        <td class="px-4 py-4 text-sm text-gray-400 text-center"><?= $record['guests_count'] ?></td>
                                        <td class="px-4 py-4 text-sm font-bold text-white text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="/asistencia/<?= $record['id'] ?>/editar" class="text-brand-400 hover:text-brand-300 p-1" title="Editar">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                </a>
                                                <form action="/asistencia/<?= $record['id'] ?>/eliminar" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar este registro?');">
                                                    <input type="hidden" name="_csrf_token" value="<?= e($csrf_token ?? '') ?>">
                                                    <button type="submit" class="text-red-400 hover:text-red-300 p-1" title="Eliminar">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
