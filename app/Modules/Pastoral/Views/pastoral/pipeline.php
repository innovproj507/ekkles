<?php /** Vista: Pipeline de Discipulado */ ?>
<div class="mb-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold text-white">Pipeline de Discipulado</h1>
            <p class="text-gray-400 text-sm mt-1">Seguimiento del proceso: Visitante → Miembro</p>
        </div>
        <?php if ($auth_can('pastoral.create')): ?>
        <a href="/pastoral/crear" class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-500 hover:to-purple-500 text-white font-medium py-2.5 px-5 rounded-xl text-sm transition-all shadow-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Nuevo Seguimiento
        </a>
        <?php endif; ?>
    </div>

    <!-- Métricas del pipeline -->
    <div class="grid grid-cols-5 gap-2 mb-6">
        <?php
        $stageConfig = [
            'visitante'   => ['label' => 'Visitantes',  'color' => 'blue'],
            'contactado'  => ['label' => 'Contactados', 'color' => 'yellow'],
            'integrado'   => ['label' => 'Integrados',  'color' => 'orange'],
            'discipulado' => ['label' => 'Discipulado', 'color' => 'purple'],
            'miembro'     => ['label' => 'Miembros',    'color' => 'emerald'],
        ];
        $pipelineMap = [];
        foreach ($summary as $s) {
            $pipelineMap[$s['pipeline_stage']] = (int)$s['total'];
        }
        ?>
        <?php foreach ($stageConfig as $stage => $cfg): ?>
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-3 text-center">
            <p class="text-2xl font-bold text-white"><?= $pipelineMap[$stage] ?? 0 ?></p>
            <p class="text-xs text-gray-400 mt-0.5"><?= $cfg['label'] ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Tablero Kanban -->
<div class="flex gap-4 overflow-x-auto pb-4" style="min-height: 400px;">
    <?php foreach ($stageConfig as $stage => $cfg): ?>
    <div class="pipeline-column flex flex-col gap-3 flex-shrink-0" style="min-width: 240px; max-width: 280px;">
        <!-- Encabezado de columna -->
        <div class="flex items-center gap-2 px-2">
            <span class="w-2 h-2 rounded-full bg-<?= $cfg['color'] ?>-400"></span>
            <h3 class="text-sm font-semibold text-white"><?= $cfg['label'] ?></h3>
            <span class="ml-auto text-xs bg-gray-800 text-gray-400 px-2 py-0.5 rounded-full">
                <?= count($pipeline[$stage] ?? []) ?>
            </span>
        </div>

        <!-- Tarjetas del pipeline -->
        <div class="flex flex-col gap-2">
            <?php if (empty($pipeline[$stage])): ?>
            <div class="bg-gray-900/50 border border-dashed border-gray-700 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-600">Sin registros</p>
            </div>
            <?php else: ?>
            <?php foreach ($pipeline[$stage] as $follow): ?>
            <div class="pipeline-card bg-gray-900 border border-gray-800 rounded-xl p-4 cursor-pointer hover:border-gray-700">
                <div class="flex items-center gap-2.5 mb-3">
                    <img src="<?= e(avatarUrl($follow['photo'] ?? null, $follow['member_name'])) ?>"
                         alt="" class="w-8 h-8 rounded-lg object-cover flex-shrink-0">
                    <div class="min-w-0">
                        <a href="/miembros/<?= $follow['member_id'] ?>"
                           class="text-sm font-medium text-white hover:text-brand-400 transition-colors truncate block">
                            <?= e($follow['member_name']) ?>
                        </a>
                        <p class="text-xs text-gray-500"><?= e($follow['phone'] ?? '') ?></p>
                    </div>
                </div>

                <?php if ($follow['subject']): ?>
                <p class="text-xs text-gray-400 mb-2 line-clamp-2"><?= e($follow['subject']) ?></p>
                <?php endif; ?>

                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">
                        <?= $follow['assigned_to_name'] ? '👤 ' . e(truncate($follow['assigned_to_name'], 15)) : '' ?>
                    </span>
                    <span class="text-xs px-2 py-0.5 rounded-full <?= match($follow['priority'] ?? 'media') {
                        'alta'  => 'bg-red-500/10 text-red-400',
                        'media' => 'bg-amber-500/10 text-amber-400',
                        'baja'  => 'bg-gray-500/10 text-gray-400',
                        default => 'bg-gray-500/10 text-gray-400',
                    } ?>">
                        <?= ucfirst($follow['priority'] ?? 'media') ?>
                    </span>
                </div>

                <?php if ($follow['next_follow_at'] && $follow['next_follow_at'] < date('Y-m-d H:i:s')): ?>
                <div class="mt-2 flex items-center gap-1 text-red-400">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-xs">Vencido: <?= formatDate($follow['next_follow_at']) ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
