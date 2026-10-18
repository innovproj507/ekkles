<?php /** Vista: Reportes */
if (isset($report)) {
    $subview = __DIR__ . '/' . $report . '.php';
    if (file_exists($subview)) {
        require $subview;
        return;
    }
}
?>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    <!-- Reporte de Miembros -->
    <a href="/reportes/miembros" class="bg-gradient-to-br from-brand-600/15 to-brand-600/5 border border-brand-500/20 rounded-2xl p-6 hover:border-brand-500/40 transition-all group card-hover">
        <div class="w-12 h-12 bg-brand-500/20 rounded-xl flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
        <h3 class="text-base font-semibold text-white group-hover:text-brand-400 transition-colors mb-2">Reporte de Miembros</h3>
        <p class="text-sm text-gray-400">Estadísticas de membresía, crecimiento e inactividad</p>
    </a>



    <!-- Reporte de Asistencia -->
    <a href="/reportes/asistencia" class="bg-gradient-to-br from-blue-600/15 to-blue-600/5 border border-blue-500/20 rounded-2xl p-6 hover:border-blue-500/40 transition-all group card-hover">
        <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h3 class="text-base font-semibold text-white group-hover:text-blue-400 transition-colors mb-2">Reporte de Asistencia</h3>
        <p class="text-sm text-gray-400">Tendencias semanales y comparativo por evento</p>
        <div class="flex gap-2 mt-4">
            <span class="text-xs bg-gray-800 text-gray-500 px-3 py-1.5 rounded-lg">Próximamente</span>
        </div>
    </a>

    <!-- Reporte de Ocupación de Salones -->
    <a href="/reportes/salones" class="bg-gradient-to-br from-amber-600/15 to-amber-600/5 border border-amber-500/20 rounded-2xl p-6 hover:border-amber-500/40 transition-all group card-hover">
        <div class="w-12 h-12 bg-amber-500/20 rounded-xl flex items-center justify-center mb-4 text-2xl">🏛</div>
        <h3 class="text-base font-semibold text-white group-hover:text-amber-400 transition-colors mb-2">Ocupación de Salones</h3>
        <p class="text-sm text-gray-400">Eventos y horas reservadas por salón en cada mes</p>
    </a>



    <!-- Reporte de Ingresos -->
    <a href="/reportes/ingresos" class="bg-gradient-to-br from-emerald-600/15 to-emerald-600/5 border border-emerald-500/20 rounded-2xl p-6 hover:border-emerald-500/40 transition-all group card-hover">
        <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h3 class="text-base font-semibold text-white group-hover:text-emerald-400 transition-colors mb-2">Reporte de Ingresos</h3>
        <p class="text-sm text-gray-400">Análisis detallado de ingresos por módulo, categoría y rango de fechas</p>
        <div class="flex gap-2 mt-4">
            <span class="text-xs bg-emerald-500/10 text-emerald-400 px-3 py-1.5 rounded-lg border border-emerald-500/20">
                👁️ Ver Reporte
            </span>
        </div>
    </a>

    <!-- Reporte de Egresos -->
    <a href="/reportes/egresos" class="bg-gradient-to-br from-red-600/15 to-red-600/5 border border-red-500/20 rounded-2xl p-6 hover:border-red-500/40 transition-all group card-hover">
        <div class="w-12 h-12 bg-red-500/20 rounded-xl flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2m3 2h12a3 3 0 003-3v-3a3 3 0 00-3-3H7a3 3 0 00-3 3v3a3 3 0 003 3z"/></svg>
        </div>
        <h3 class="text-base font-semibold text-white group-hover:text-red-400 transition-colors mb-2">Reporte de Egresos</h3>
        <p class="text-sm text-gray-400">Análisis detallado de egresos por módulo, categoría y rango de fechas</p>
        <div class="flex gap-2 mt-4">
            <span class="text-xs bg-red-500/10 text-red-400 px-3 py-1.5 rounded-lg border border-red-500/20">
                👁️ Ver Reporte
            </span>
        </div>
    </a>
</div>

<!-- Nota de auditoría -->
<div class="bg-amber-500/5 border border-amber-500/20 rounded-2xl p-6">
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div>
            <p class="text-sm font-semibold text-amber-400 mb-1">Política de Auditoría</p>
            <p class="text-sm text-gray-400">
                Ninguna transacción financiera puede eliminarse del sistema. Todas las operaciones quedan
                registradas en el log de auditoría con usuario, fecha, IP y los valores anterior y posterior.
            </p>
        </div>
    </div>
</div>
