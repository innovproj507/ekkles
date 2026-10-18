<?php
/**
 * Partial: Alertas Flash
 * Muestra mensajes de éxito, error e información.
 */
?>
<?php if (!empty($flash_success)): ?>
<div class="mx-6 mt-4 flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-4 py-3 animate-slideIn" role="alert">
    <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <p class="text-emerald-300 text-sm"><?= e($flash_success) ?></p>
    <button onclick="this.parentElement.remove()" class="ml-auto text-emerald-400 hover:text-emerald-200">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
<?php endif; ?>

<?php if (!empty($flash_error)): ?>
<div class="mx-6 mt-4 flex items-center gap-3 bg-red-500/10 border border-red-500/20 rounded-xl px-4 py-3 animate-slideIn" role="alert">
    <svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <p class="text-red-300 text-sm"><?= e($flash_error) ?></p>
    <button onclick="this.parentElement.remove()" class="ml-auto text-red-400 hover:text-red-200">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
<?php endif; ?>

<?php if (!empty($flash_info)): ?>
<div class="mx-6 mt-4 flex items-center gap-3 bg-blue-500/10 border border-blue-500/20 rounded-xl px-4 py-3 animate-slideIn" role="alert">
    <svg class="w-5 h-5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <p class="text-blue-300 text-sm"><?= e($flash_info) ?></p>
    <button onclick="this.parentElement.remove()" class="ml-auto text-blue-400 hover:text-blue-200">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
<?php endif; ?>
