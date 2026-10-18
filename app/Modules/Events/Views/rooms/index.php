<?php /** Vista: Salones */ ?>
<div class="max-w-4xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white mb-1">Salones</h1>
            <p class="text-sm text-gray-400">Salones donde se realizan los eventos. Al asignar un salón a un evento se avisa si ya está ocupado.</p>
        </div>
        <div class="flex gap-3">
            <a href="/eventos" class="bg-gray-800 hover:bg-gray-700 text-white font-medium py-2.5 px-4 rounded-xl text-sm transition-colors">Volver a Eventos</a>
            <a href="/reportes/salones" class="bg-gray-800 hover:bg-gray-700 text-white font-medium py-2.5 px-4 rounded-xl text-sm transition-colors">Reporte de ocupación</a>
        </div>
    </div>

    <?php if ($auth_can('events.create')): ?>
    <!-- Nuevo salón -->
    <form method="POST" action="/salones" class="bg-gray-900 border border-gray-800 rounded-2xl p-4 mb-6 grid grid-cols-1 sm:grid-cols-4 gap-3">
        <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">
        <input type="text" name="name" required maxlength="100" placeholder="Nombre del salón (ej: Salón principal)"
               class="sm:col-span-2 bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-gray-500">
        <input type="number" name="capacity" min="1" placeholder="Capacidad (personas)"
               class="bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-gray-500">
        <button type="submit" class="bg-brand-600 hover:bg-brand-500 text-white font-medium py-2.5 px-4 rounded-xl text-sm transition-colors">+ Agregar salón</button>
    </form>
    <?php endif; ?>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-800 bg-gray-800/50">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Salón</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Capacidad</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Eventos</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Estado</th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                <?php if (empty($rooms)): ?>
                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500 text-sm">Aún no hay salones registrados.</td></tr>
                <?php endif; ?>
                <?php foreach ($rooms as $room): ?>
                <tr class="hover:bg-gray-800/30 transition-colors align-top">
                    <td class="px-6 py-4">
                        <p class="text-sm text-white font-medium"><?= e($room['name']) ?></p>
                        <?php if (!empty($room['description'])): ?><p class="text-xs text-gray-500"><?= e($room['description']) ?></p><?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-300"><?= $room['capacity'] ? (int)$room['capacity'] . ' personas' : '—' ?></td>
                    <td class="px-6 py-4 text-sm text-gray-300"><?= (int)$room['events_count'] ?></td>
                    <td class="px-6 py-4">
                        <?php if ($room['is_active']): ?>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-medium bg-emerald-500/10 text-emerald-400">Activo</span>
                        <?php else: ?>
                            <span class="text-xs px-2.5 py-0.5 rounded-full font-medium bg-red-500/10 text-red-400">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <?php if ($auth_can('events.edit')): ?>
                        <details class="inline-block text-left">
                            <summary class="cursor-pointer text-xs text-brand-400 hover:text-brand-300 list-none">Editar</summary>
                            <form method="POST" action="/salones/<?= (int)$room['id'] ?>" class="mt-3 space-y-2 w-64">
                                <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">
                                <input type="text" name="name" required maxlength="100" value="<?= e($room['name']) ?>"
                                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <input type="number" name="capacity" min="1" value="<?= e($room['capacity'] ?? '') ?>" placeholder="Capacidad"
                                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-gray-500">
                                <input type="text" name="description" maxlength="255" value="<?= e($room['description'] ?? '') ?>" placeholder="Descripción (opcional)"
                                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-gray-500">
                                <label class="flex items-center gap-2 text-xs text-gray-300">
                                    <input type="checkbox" name="is_active" value="1" <?= $room['is_active'] ? 'checked' : '' ?> class="rounded bg-gray-800 border-gray-600 text-brand-500"> Activo
                                </label>
                                <button type="submit" class="w-full bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold py-2 rounded-lg">Guardar</button>
                            </form>
                            <?php if ((int)$room['events_count'] === 0 && $auth_can('events.delete')): ?>
                            <form method="POST" action="/salones/<?= (int)$room['id'] ?>/eliminar" class="mt-2 w-64" onsubmit="return confirm('¿Eliminar este salón?')">
                                <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">
                                <button type="submit" class="w-full text-red-400 hover:text-red-300 text-xs py-1.5">Eliminar salón</button>
                            </form>
                            <?php endif; ?>
                        </details>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
