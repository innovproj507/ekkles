<?php /** Vista: Notificaciones */ ?>
<div class="mb-6">
    <h1 class="text-2xl font-bold text-white"><?= e($title) ?></h1>
    <p class="text-gray-400 text-sm mt-1">Aquí verás los avisos importantes y pendientes de tu cuenta.</p>
</div>

<div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden shadow-sm">
    <?php if (empty($notifications)): ?>
        <div class="p-12 text-center text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p>No tienes notificaciones pendientes.</p>
        </div>
    <?php else: ?>
        <ul class="divide-y divide-gray-800">
            <?php foreach ($notifications as $n): ?>
            <li class="p-5 hover:bg-gray-800/50 transition-colors">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-brand-500/10 border border-brand-500/20 rounded-xl flex items-center justify-center text-brand-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-white"><?= e($n['title']) ?></h3>
                        <p class="text-sm text-gray-400 mt-1"><?= e($n['body']) ?></p>
                        <?php 
                        $notifData = !empty($n['data']) ? json_decode($n['data'], true) : [];
                        if (!empty($notifData['action_url']) && !empty($notifData['action_label'])):
                        ?>
                        <div class="mt-3">
                            <a href="<?= e($notifData['action_url']) ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-600 hover:bg-brand-500 text-white text-xs font-medium rounded-lg transition-colors">
                                <?= e($notifData['action_label']) ?>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                        <?php endif; ?>
                        <p class="text-xs text-gray-500 mt-2"><?= formatDateTime($n['created_at'] ?? '') ?></p>
                    </div>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Si queremos marcar como leídas vía AJAX de forma manual, pero en el controlador
    // actualmente ya se marca `markAllRead(Auth::id())` de manera inmediata al cargar la vista.
    // Esto es suficiente para resetear el contador.
});
</script>
