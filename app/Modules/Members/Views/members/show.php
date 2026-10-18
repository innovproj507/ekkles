<?php
/**
 * Vista: Perfil detallado de un Miembro
 */
?>
<div class="max-w-5xl mx-auto">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/miembros" class="hover:text-white transition-colors">Miembros</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-white"><?= e($member['first_name'] . ' ' . $member['last_name']) ?></span>
    </div>

    <!-- Cabecera del perfil -->
    <div class="bg-gradient-to-br from-gray-900 to-gray-900/50 border border-gray-800 rounded-2xl p-6 mb-6">
        <div class="flex flex-col sm:flex-row items-start gap-6">
            <div class="relative flex-shrink-0">
                <img src="<?= e(avatarUrl($member['photo'], $member['first_name'] . ' ' . $member['last_name'])) ?>"
                     alt="Foto" class="w-24 h-24 rounded-2xl object-cover border-2 border-gray-700">
                <span class="absolute -bottom-2 -right-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?= memberStatusBadge($member['status']) ?>">
                    <?= memberStatusLabel($member['status']) ?>
                </span>
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-white">
                            <?= e($member['first_name'] . ' ' . $member['last_name']) ?>
                            <?php if (empty($member['is_converted']) && $member['status'] !== 'visitante'): ?>
                            <span class="align-middle ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-500/15 text-amber-300">No convertido</span>
                            <?php endif; ?>
                        </h1>
                        <p class="text-gray-400 text-sm mt-1">
                            Código: <span class="font-mono text-gray-300"><?= e($member['code']) ?></span>
                            <?php if ($member['birth_date']): ?>
                            · <?= calcAge($member['birth_date']) ?> años
                            <?php endif; ?>
                            <?php if ($member['ministry_name']): ?>
                            · <span style="color: <?= e($member['ministry_color'] ?? '#6366f1') ?>">
                                <?= e($member['ministry_name']) ?>
                              </span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <?php if ($auth_can('members.edit')): ?>
                    <div class="flex gap-2">
                        <a href="/miembros/<?= $member['id'] ?>/editar"
                           class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-500 text-white font-medium py-2 px-4 rounded-xl text-sm transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                            Editar
                        </a>
                        <?php if ($auth_can('pastoral.create')): ?>
                        <a href="/pastoral/crear?member_id=<?= $member['id'] ?>"
                           class="inline-flex items-center gap-2 bg-gray-700 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-xl text-sm transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Seguimiento
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Datos de contacto rápido -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4">
                    <?php if ($member['email']): ?>
                    <a href="mailto:<?= e($member['email']) ?>" class="flex items-center gap-2 text-xs text-gray-400 hover:text-white transition-colors">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <?= e(truncate($member['email'], 25)) ?>
                    </a>
                    <?php endif; ?>
                    <?php if ($member['phone']): ?>
                    <a href="tel:<?= e($member['phone']) ?>" class="flex items-center gap-2 text-xs text-gray-400 hover:text-white transition-colors">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <?= e($member['phone']) ?>
                    </a>
                    <?php endif; ?>
                    <?php if ($member['address']): ?>
                    <span class="flex items-center gap-2 text-xs text-gray-400 col-span-2">
                        <svg class="w-3.5 h-3.5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <?= e($member['address'] . ($member['city'] ? ', ' . $member['city'] : '')) ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Métricas de membresía -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5 pt-5 border-t border-gray-800">
            <div class="text-center">
                <p class="text-lg font-bold text-white"><?= formatDate($member['membership_date']) ?></p>
                <p class="text-xs text-gray-500 mt-0.5">Fecha Membresía</p>
            </div>
            <div class="text-center">
                <p class="text-lg font-bold text-white"><?= formatDate($member['baptism_date']) ?></p>
                <p class="text-xs text-gray-500 mt-0.5">Bautismo</p>
            </div>
            <div class="text-center">
                <p class="text-lg font-bold text-white"><?= formatDate($member['last_attendance_at']) ?></p>
                <p class="text-xs text-gray-500 mt-0.5">Última Asistencia</p>
            </div>
            <div class="text-center">
                <p class="text-lg font-bold text-white"><?= count($attendance) ?></p>
                <p class="text-xs text-gray-500 mt-0.5">Total Asistencias</p>
            </div>
        </div>
    </div>

    <!-- Tabs de historial -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Notas de Discipulado -->
        <?php if ($auth_can('members.view_notes') && $member['pastoral_notes']): ?>
        <div class="lg:col-span-1 bg-amber-500/5 border border-amber-500/20 rounded-2xl p-5">
            <h3 class="text-sm font-semibold text-amber-400 mb-3 flex items-center gap-2">
                🔒 Notas de Discipulado (Confidencial)
            </h3>
            <p class="text-sm text-gray-300 leading-relaxed"><?= nl2br(e($member['pastoral_notes'])) ?></p>
        </div>
        <?php endif; ?>

        <!-- Historial de Asistencia -->
        <div class="<?= ($auth_can('members.view_notes') && $member['pastoral_notes']) ? '' : 'lg:col-span-2' ?> bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <h3 class="text-sm font-semibold text-white mb-4">Últimas Asistencias</h3>
            <?php if (empty($attendance)): ?>
            <p class="text-gray-500 text-sm">Sin registros de asistencia</p>
            <?php else: ?>
            <div class="space-y-2">
                <?php foreach (array_slice($attendance, 0, 8) as $att): ?>
                <div class="flex items-center justify-between py-2 border-b border-gray-800 last:border-0">
                    <p class="text-sm text-gray-300"><?= e($att['title'] ?? 'Evento') ?></p>
                    <span class="text-xs text-gray-500"><?= formatDateTime($att['checkin_at']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Donaciones del miembro -->
        <?php if ($auth_can('donations.view') && !empty($donations)): ?>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <h3 class="text-sm font-semibold text-white mb-4">Historial de Donaciones</h3>
            <div class="space-y-2">
                <?php foreach (array_slice($donations, 0, 6) as $don): ?>
                <div class="flex items-center justify-between py-2 border-b border-gray-800 last:border-0">
                    <div>
                        <p class="text-sm text-gray-300"><?= donationTypeLabel($don['type']) ?></p>
                        <p class="text-xs text-gray-500"><?= formatDate($don['created_at']) ?></p>
                    </div>
                    <span class="text-sm font-semibold text-emerald-400"><?= formatCurrency((float)$don['amount']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Seguimiento de Discipulado -->
        <?php if ($auth_can('pastoral.view') && !empty($pastoral)): ?>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <h3 class="text-sm font-semibold text-white mb-4">Seguimiento de Discipulado</h3>
            <div class="space-y-2">
                <?php foreach (array_slice($pastoral, 0, 5) as $pf): ?>
                <div class="py-2 border-b border-gray-800 last:border-0">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-300"><?= e($pf['subject'] ?? ucfirst($pf['type'])) ?></p>
                        <span class="text-xs px-2 py-0.5 rounded-full <?= $pf['status'] === 'completado' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' ?>">
                            <?= ucfirst($pf['status']) ?>
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5"><?= formatDate($pf['created_at']) ?> · <?= e($pf['assigned_to_name'] ?? '') ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
