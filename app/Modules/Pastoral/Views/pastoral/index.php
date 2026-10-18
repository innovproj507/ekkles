<?php /** Vista: Seguimiento de Discipulado (Lista) */ ?>
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-white"><?= e($title) ?></h2>
            <p class="text-gray-400 text-sm mt-1">Gestión de seguimientos, llamadas y visitas de discipulado.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="/pastoral/pipeline" class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 text-white font-medium py-2 px-4 rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                Ver Pipeline
            </a>
            <?php if ($auth_can('pastoral.edit')): ?>
            <a href="/pastoral/crear" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-500 text-white font-medium py-2 px-4 rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo Seguimiento
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 sm:p-6 mb-6">
        <form method="GET" action="/pastoral" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-gray-400 mb-1">Buscar Miembro</label>
                <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Nombre o asunto..."
                       class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-xl px-4 py-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="w-full sm:w-48">
                <label class="block text-xs text-gray-400 mb-1">Estado</label>
                <select name="status" class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-xl px-4 py-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">Todos</option>
                    <option value="pendiente" <?= ($filters['status'] ?? '') === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="en_proceso" <?= ($filters['status'] ?? '') === 'en_proceso' ? 'selected' : '' ?>>En Proceso</option>
                    <option value="completado" <?= ($filters['status'] ?? '') === 'completado' ? 'selected' : '' ?>>Completado</option>
                </select>
            </div>
            <div class="w-full sm:w-48">
                <label class="block text-xs text-gray-400 mb-1">Etapa</label>
                <select name="stage" class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-xl px-4 py-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">Todas</option>
                    <option value="visitante" <?= ($filters['pipeline_stage'] ?? '') === 'visitante' ? 'selected' : '' ?>>Visitante</option>
                    <option value="nuevo_creyente" <?= ($filters['pipeline_stage'] ?? '') === 'nuevo_creyente' ? 'selected' : '' ?>>Nuevo Creyente</option>
                    <option value="miembro_bautizado" <?= ($filters['pipeline_stage'] ?? '') === 'miembro_bautizado' ? 'selected' : '' ?>>Miembro Bautizado</option>
                </select>
            </div>
            <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white px-6 py-2 rounded-xl text-sm font-medium border border-gray-700 transition-colors">
                Filtrar
            </button>
        </form>
    </div>

    <!-- Lista -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden shadow-sm">
        <?php if (empty($follows)): ?>
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h3 class="text-lg font-medium text-white mb-2">No hay seguimientos</h3>
                <p class="text-gray-400">No se encontraron tareas de seguimiento con los filtros actuales.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-800/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-800">
                            <th class="px-6 py-3 font-medium">Miembro / Asunto</th>
                            <th class="px-6 py-3 font-medium">Tipo y Etapa</th>
                            <th class="px-6 py-3 font-medium">Fecha Asignada</th>
                            <th class="px-6 py-3 font-medium">Estado</th>
                            <th class="px-6 py-3 font-medium text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-800">
                        <?php foreach ($follows as $f): ?>
                        <tr class="hover:bg-gray-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <p class="text-white font-medium"><?= e($f['member_name'] ?? 'Desconocido') ?></p>
                                <p class="text-xs text-gray-400 mt-1 truncate max-w-xs"><?= e($f['subject'] ?? 'Sin asunto') ?></p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-gray-300 capitalize"><?= e($f['type']) ?></p>
                                <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded text-[10px] font-medium bg-gray-800 text-gray-400 border border-gray-700">
                                    <?= str_replace('_', ' ', strtoupper($f['pipeline_stage'])) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($f['scheduled_at']): ?>
                                    <span class="text-gray-300"><?= date('d M Y, h:i A', strtotime($f['scheduled_at'])) ?></span>
                                    <?php 
                                    $isOverdue = strtotime($f['scheduled_at']) < time() && $f['status'] !== 'completado';
                                    if ($isOverdue): 
                                    ?>
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-red-500/10 text-red-400 border border-red-500/20">Atrasado</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-gray-500 italic">No asignada</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($f['status'] === 'completado'): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-500/10 text-green-400 border border-green-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span> Completado
                                    </span>
                                <?php elseif ($f['status'] === 'en_proceso'): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span> En Proceso
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span> Pendiente
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="/pastoral/<?= $f['id'] ?>" class="text-brand-400 hover:text-brand-300 text-sm font-medium transition-colors">Ver Detalles</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <?php if ($pagination['pages'] > 1): ?>
            <div class="px-6 py-4 border-t border-gray-800 flex items-center justify-between">
                <span class="text-sm text-gray-500">
                    Mostrando <?= count($follows) ?> de <?= $pagination['total'] ?>
                </span>
                <div class="flex items-center gap-2">
                    <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($filters['search']) ?>&status=<?= urlencode($filters['status']) ?>&stage=<?= urlencode($filters['pipeline_stage']) ?>"
                       class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition-colors <?= $i === $pagination['page'] ? 'bg-brand-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-white' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>
                </div>
            </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>
