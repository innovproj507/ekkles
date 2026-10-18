<?php /** Vista: Reporte Financiero */ ?>
<div class="space-y-6">
    <!-- Breadcrumb & Back Link -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="/reportes" class="hover:text-white transition-colors">Reportes</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-white">Reporte Financiero</span>
        </div>
        
        <!-- Export buttons -->
        <div class="flex gap-2">
            <a href="/reportes/exportar/finanzas?format=excel" 
               class="inline-flex items-center gap-2 bg-emerald-600/20 hover:bg-emerald-600/30 border border-emerald-500/20 text-emerald-400 font-medium py-2 px-4 rounded-xl text-sm transition-all shadow-md">
                📊 Exportar Excel
            </a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-gradient-to-br from-emerald-600/15 to-emerald-650/5 border border-emerald-500/20 rounded-2xl p-6 shadow-xl">
            <p class="text-xs text-emerald-400 font-medium mb-1.5">Ingresos Año <?= e($year) ?></p>
            <p class="text-3xl font-extrabold text-white"><?= formatCurrency($cashFlow['total_income'] ?? 0) ?></p>
        </div>
        <div class="bg-gradient-to-br from-red-600/15 to-red-650/5 border border-red-500/20 rounded-2xl p-6 shadow-xl">
            <p class="text-xs text-red-400 font-medium mb-1.5">Egresos Año <?= e($year) ?></p>
            <p class="text-3xl font-extrabold text-white"><?= formatCurrency($cashFlow['total_expense'] ?? 0) ?></p>
        </div>
        <div class="bg-gradient-to-br from-brand-600/15 to-brand-650/5 border border-brand-500/20 rounded-2xl p-6 shadow-xl">
            <p class="text-xs text-brand-400 font-medium mb-1.5">Balance Anual</p>
            <p class="text-3xl font-extrabold text-white"><?= formatCurrency(($cashFlow['total_income'] ?? 0) - ($cashFlow['total_expense'] ?? 0)) ?></p>
        </div>
    </div>
</div>
