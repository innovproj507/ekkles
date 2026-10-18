<?php /** Vista: Detalle de Conciliación Bancaria */ ?>
<div class="max-w-4xl mx-auto">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/conciliaciones" class="hover:text-white transition-colors">Conciliaciones</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-white">Detalle #<?= str_pad($recon['id'], 5, '0', STR_PAD_LEFT) ?></span>
    </div>

    <!-- Cabecera -->
    <div class="bg-gradient-to-br from-gray-900 to-gray-900/50 border border-gray-800 rounded-2xl p-6 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Conciliación: <?= e(ucfirst($recon['account_name'])) ?></h1>
                <p class="text-gray-400 text-sm mt-1">Periodo: <span class="text-white"><?= formatDate($recon['period_start']) ?> al <?= formatDate($recon['period_end']) ?></span></p>
            </div>
            <div>
                <?php if ($recon['status'] === 'cerrada'): ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Cerrada
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Borrador
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-gray-800 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-500 mb-1">Saldo Inicial</p>
                <p class="text-lg font-bold text-gray-300">$<?= number_format($recon['starting_balance'], 2) ?></p>
            </div>
            <div class="bg-emerald-500/5 rounded-xl p-4 text-center border border-emerald-500/10">
                <p class="text-xs text-gray-500 mb-1">Ingresos Registrados</p>
                <p class="text-lg font-bold text-emerald-400">+$<?= number_format($recon['total_income'], 2) ?></p>
            </div>
            <div class="bg-red-500/5 rounded-xl p-4 text-center border border-red-500/10">
                <p class="text-xs text-gray-500 mb-1">Egresos Registrados</p>
                <p class="text-lg font-bold text-red-400">-$<?= number_format($recon['total_expense'], 2) ?></p>
            </div>
            <div class="bg-brand-500/10 rounded-xl p-4 text-center border border-brand-500/20">
                <p class="text-xs text-brand-300/80 mb-1">Saldo Esperado</p>
                <p class="text-xl font-black text-white">$<?= number_format($recon['expected_balance'], 2) ?></p>
            </div>
        </div>

        <div class="flex items-center justify-between p-5 bg-gray-800/50 rounded-xl border border-gray-700">
            <div>
                <p class="text-sm font-semibold text-white">Saldo Real en Banco</p>
                <?php if ($recon['difference'] != 0): ?>
                <p class="text-xs font-medium text-red-400 mt-0.5">Diferencia: $<?= number_format($recon['difference'], 2) ?></p>
                <?php else: ?>
                <p class="text-xs font-medium text-emerald-400 mt-0.5">Cuadre Perfecto</p>
                <?php endif; ?>
            </div>
            <div class="text-3xl font-black <?= $recon['difference'] == 0 ? 'text-emerald-400' : 'text-red-400' ?>">
                $<?= number_format($recon['actual_balance'], 2) ?>
            </div>
        </div>
    </div>

    <!-- Notas y Metadatos -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <h3 class="text-sm font-semibold text-white mb-3">Notas / Observaciones</h3>
            <?php if (!empty($recon['notes'])): ?>
            <p class="text-sm text-gray-300 leading-relaxed"><?= nl2br(e($recon['notes'])) ?></p>
            <?php else: ?>
            <p class="text-sm text-gray-500 italic">Ninguna nota adicional.</p>
            <?php endif; ?>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 space-y-4 text-sm">
            <div>
                <p class="text-gray-500 text-xs font-medium mb-1">Fecha de Registro</p>
                <p class="text-gray-300"><?= formatDateTime($recon['created_at']) ?></p>
            </div>
            <?php if ($recon['status'] === 'cerrada'): ?>
            <div>
                <p class="text-gray-500 text-xs font-medium mb-1">Cerrado Por</p>
                <p class="text-gray-300">Usuario ID: <?= e($recon['reconciled_by']) ?></p>
            </div>
            <div>
                <p class="text-gray-500 text-xs font-medium mb-1">Fecha de Cierre</p>
                <p class="text-gray-300"><?= formatDateTime($recon['reconciled_at']) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex justify-start">
        <a href="/conciliaciones" class="bg-gray-800 hover:bg-gray-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-colors">
            ← Volver al Listado
        </a>
    </div>
</div>
