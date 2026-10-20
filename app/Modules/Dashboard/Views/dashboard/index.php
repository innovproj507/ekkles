<?php
/**
 * Vista: Dashboard Ejecutivo
 * KPIs, gráficas de Chart.js y widgets de resumen.
 */
?>
<!-- KPI Cards Row -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Miembros Activos -->
    <div class="bg-gradient-to-br from-brand-600/20 to-brand-600/5 border border-brand-500/20 rounded-2xl p-5 hover:border-brand-500/40 transition-all group">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-brand-500/20 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <span class="text-xs text-brand-400 font-medium bg-brand-500/10 px-2 py-0.5 rounded-full">Convertidos</span>
        </div>
        <p class="text-3xl font-bold text-white"><?= number_format($sectionCounts['miembros']) ?></p>
        <p class="text-gray-400 text-sm mt-1">Miembros</p>
        <p class="text-xs text-gray-500 mt-2">
            <a href="/miembros?section=no_convertidos" class="hover:text-amber-300"><?= $sectionCounts['no_convertidos'] ?> no convertidos</a> ·
            <a href="/miembros?section=visitas" class="hover:text-blue-300"><?= $sectionCounts['visitas'] ?> visitas</a>
        </p>
    </div>

    <!-- Visitantes -->
    <div class="bg-gradient-to-br from-blue-600/20 to-blue-600/5 border border-blue-500/20 rounded-2xl p-5 hover:border-blue-500/40 transition-all">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-blue-500/20 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <span class="text-xs text-blue-400 font-medium bg-blue-500/10 px-2 py-0.5 rounded-full">Nuevos</span>
        </div>
        <p class="text-3xl font-bold text-white"><?= number_format($visitors) ?></p>
        <p class="text-gray-400 text-sm mt-1">Visitantes</p>
        <p class="text-xs text-gray-500 mt-2"><?= $newBelievers ?> nuevos creyentes</p>
    </div>

    <!-- Ofrendas del mes -->
    <?php if ($auth_can('donations.view')): ?>
    <a href="/ofrendas" class="block bg-gradient-to-br from-emerald-600/20 to-emerald-600/5 border border-emerald-500/20 rounded-2xl p-5 hover:border-emerald-500/40 transition-all">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-emerald-500/20 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="text-xs text-emerald-400 font-medium bg-emerald-500/10 px-2 py-0.5 rounded-full"><?= e($currentMonthName) ?></span>
        </div>
        <p class="text-2xl font-bold text-white"><?= formatCurrency($offeringsMonth) ?></p>
        <p class="text-gray-400 text-sm mt-1">Ofrendas de <?= e($currentMonthName) ?></p>
        <p class="text-xs text-gray-500 mt-2">Balance cuenta Iglesia: <?= formatCurrency($offeringsBalance) ?></p>
    </a>
    <?php endif; ?>

    <!-- Alertas -->
    <div class="bg-gradient-to-br from-amber-600/20 to-amber-600/5 border border-amber-500/20 rounded-2xl p-5 hover:border-amber-500/40 transition-all">
        <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 bg-amber-500/20 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <span class="text-xs text-amber-400 font-medium bg-amber-500/10 px-2 py-0.5 rounded-full">Atención</span>
        </div>
        <p class="text-3xl font-bold text-white"><?= $inactiveAlert ?></p>
        <p class="text-gray-400 text-sm mt-1">Miembros Inactivos</p>
        <p class="text-xs text-gray-500 mt-2"><?= $overdueFollowups ?> seguimientos vencidos</p>
    </div>
</div>

<!-- Fila 2: Gráficas -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    <!-- Gráfica de Asistencia Semanal -->
    <div class="lg:col-span-2 bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-semibold text-white">Asistencia Semanal</h2>
                <p class="text-gray-500 text-xs mt-0.5">Últimas 8 semanas — Cultos</p>
            </div>
            <a href="/reportes/asistencia" class="text-xs text-brand-400 hover:text-brand-300 transition-colors">Ver reporte →</a>
        </div>
        <div class="h-48">
            <canvas id="chart-attendance"></canvas>
        </div>
    </div>

    <!-- Estado de Membresía -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-white">Estado Membresía</h2>
            <a href="/miembros" class="text-xs text-brand-400 hover:text-brand-300 transition-colors">Ver →</a>
        </div>
        <div class="h-32 flex items-center justify-center mb-4">
            <canvas id="chart-status" class="max-h-32"></canvas>
        </div>
        <!-- Leyenda manual -->
        <div class="space-y-2 mt-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span><span class="text-xs text-gray-400">Activos</span></div>
                <span class="text-xs font-medium text-white"><?= $activeMembers ?></span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span><span class="text-xs text-gray-400">Visitantes</span></div>
                <span class="text-xs font-medium text-white"><?= $visitors ?></span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span><span class="text-xs text-gray-400">Nuevos Creyentes</span></div>
                <span class="text-xs font-medium text-white"><?= $newBelievers ?></span>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span><span class="text-xs text-gray-400">Inactivos</span></div>
                <span class="text-xs font-medium text-white"><?= $inactiveMembers ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Fila 3: Finanzas + Eventos + Pipeline -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    <!-- Flujo de Caja Anual -->
    <?php if ($auth_can('finance.view')): ?>
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-white">Flujo de Caja</h2>
            <a href="/finanzas" class="text-xs text-brand-400 hover:text-brand-300 transition-colors">Ver →</a>
        </div>
        <!-- Resumen del mes -->
        <div class="space-y-3 mb-4">
            <div class="flex items-center justify-between py-2 border-b border-gray-800">
                <span class="text-xs text-gray-400">Ingresos del mes</span>
                <span class="text-sm font-semibold text-emerald-400"><?= formatCurrency($financeSummary['ingresos']) ?></span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-800">
                <span class="text-xs text-gray-400">Egresos del mes</span>
                <span class="text-sm font-semibold text-red-400"><?= formatCurrency($financeSummary['egresos']) ?></span>
            </div>
            <div class="flex items-center justify-between py-2">
                <span class="text-xs font-semibold text-gray-300">Balance neto</span>
                <span class="text-sm font-bold <?= $financeSummary['balance'] >= 0 ? 'text-emerald-400' : 'text-red-400' ?>">
                    <?= formatCurrency($financeSummary['balance']) ?>
                </span>
            </div>
        </div>
        <div class="h-24">
            <canvas id="chart-cashflow"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- Próximos Eventos -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-white">Próximos Eventos</h2>
            <a href="/eventos" class="text-xs text-brand-400 hover:text-brand-300 transition-colors">Ver todos →</a>
        </div>
        <div class="space-y-3">
            <?php if (empty($upcomingEvents)): ?>
            <p class="text-gray-500 text-sm text-center py-4">No hay eventos próximos</p>
            <?php else: ?>
            <?php foreach ($upcomingEvents as $event): ?>
            <a href="/eventos/<?= $event['id'] ?>" class="flex items-center gap-3 p-3 bg-gray-800/50 rounded-xl hover:bg-gray-800 transition-colors group">
                <div class="w-10 h-10 bg-brand-500/10 border border-brand-500/20 rounded-xl flex flex-col items-center justify-center flex-shrink-0">
                    <span class="text-xs font-bold text-brand-400"><?= date('d', strtotime($event['start_datetime'])) ?></span>
                    <span class="text-[9px] text-brand-500 uppercase"><?= date('M', strtotime($event['start_datetime'])) ?></span>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-white truncate group-hover:text-brand-400 transition-colors">
                        <?= e($event['title']) ?>
                    </p>
                    <p class="text-xs text-gray-500">
                        <?= date('H:i', strtotime($event['start_datetime'])) ?>
                        <?php if ($event['location']): ?> · <?= e(truncate($event['location'], 20)) ?><?php endif; ?>
                    </p>
                </div>
                <span class="ml-auto text-xs text-gray-600 flex-shrink-0"><?= $event['attendance_count'] ?>👥</span>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pipeline de Discipulado -->
    <?php if ($auth_can('pastoral.view')): ?>
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-white">Pipeline de Discipulado</h2>
            <a href="/pastoral/pipeline" class="text-xs text-brand-400 hover:text-brand-300 transition-colors">Ver →</a>
        </div>
        <div class="space-y-2">
            <?php
            $stageColors = [
                'visitante'   => ['bg' => 'bg-blue-500/10',    'text' => 'text-blue-400',   'bar' => 'bg-blue-500'],
                'contactado'  => ['bg' => 'bg-yellow-500/10',  'text' => 'text-yellow-400', 'bar' => 'bg-yellow-500'],
                'integrado'   => ['bg' => 'bg-orange-500/10',  'text' => 'text-orange-400', 'bar' => 'bg-orange-500'],
                'discipulado' => ['bg' => 'bg-purple-500/10',  'text' => 'text-purple-400', 'bar' => 'bg-purple-500'],
                'miembro'     => ['bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400','bar' => 'bg-emerald-500'],
            ];
            $pipelineMap = [];
            foreach ($pipelineSummary as $ps) {
                $pipelineMap[$ps['pipeline_stage']] = (int)$ps['total'];
            }
            $maxPipeline = max(1, max($pipelineMap ?: [1]));
            foreach ($stageColors as $stage => $colors):
                $count = $pipelineMap[$stage] ?? 0;
            ?>
            <div class="flex items-center gap-3">
                <span class="text-xs text-gray-400 w-24 flex-shrink-0"><?= pipelineStageLabel($stage) ?></span>
                <div class="flex-1 bg-gray-800 rounded-full h-1.5">
                    <div class="<?= $colors['bar'] ?> h-1.5 rounded-full transition-all duration-500"
                         style="width: <?= $count > 0 ? round(($count / $maxPipeline) * 100) : 0 ?>%"></div>
                </div>
                <span class="text-xs font-medium text-white w-6 text-right"><?= $count ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if ($overdueFollowups > 0): ?>
        <div class="mt-4 flex items-center gap-2 bg-red-500/10 border border-red-500/20 rounded-xl px-3 py-2">
            <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-xs text-red-300"><?= $overdueFollowups ?> seguimientos vencidos</p>
            <a href="/pastoral" class="ml-auto text-xs text-red-400 hover:text-red-300">Revisar →</a>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

<!-- Fila 4: Cumpleaños + Alerta Miembros Inactivos -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Cumpleaños de hoy -->
    <?php if (!empty($birthdaysToday)): ?>
    <div class="bg-gradient-to-br from-pink-600/10 to-pink-600/5 border border-pink-500/20 rounded-2xl p-6">
        <div class="flex items-center gap-2 mb-4">
            <span class="text-xl">🎂</span>
            <h2 class="text-base font-semibold text-white">Cumpleaños Hoy</h2>
            <span class="ml-auto bg-pink-500/20 text-pink-400 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                <?= count($birthdaysToday) ?>
            </span>
        </div>
        <div class="space-y-3">
            <?php foreach (array_slice($birthdaysToday, 0, 5) as $bday): ?>
            <a href="/miembros/<?= $bday['id'] ?>" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
                <img src="<?= e(avatarUrl($bday['photo'], $bday['first_name'] . ' ' . $bday['last_name'])) ?>"
                     alt="" class="w-9 h-9 rounded-xl object-cover flex-shrink-0">
                <div>
                    <p class="text-sm font-medium text-white"><?= e($bday['first_name'] . ' ' . $bday['last_name']) ?></p>
                    <p class="text-xs text-gray-400"><?= $bday['birth_date'] ? calcAge($bday['birth_date']) . ' años' : '' ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Crecimiento mensual -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-white">Crecimiento de Membresía</h2>
            <span class="text-xs text-gray-500">Últimos 12 meses</span>
        </div>
        <div class="h-32">
            <canvas id="chart-growth"></canvas>
        </div>
    </div>

</div>

<!-- ============================================================
     Chart.js — Datos de gráficas
     ============================================================ -->
<script>
// Paleta de colores del tema
const colors = {
    brand:   'rgba(99, 102, 241, 1)',
    brandA:  'rgba(99, 102, 241, 0.15)',
    emerald: 'rgba(16, 185, 129, 1)',
    emeraldA:'rgba(16, 185, 129, 0.15)',
    red:     'rgba(239, 68, 68, 1)',
    redA:    'rgba(239, 68, 68, 0.15)',
    blue:    'rgba(59, 130, 246, 1)',
    purple:  'rgba(168, 85, 247, 1)',
    amber:   'rgba(245, 158, 11, 1)',
    gray:    'rgba(75, 85, 99, 1)',
};

const chartDefaults = {
    color: 'rgba(156, 163, 175, 1)',
    borderColor: 'rgba(55, 65, 81, 1)',
};
Chart.defaults.color = chartDefaults.color;
Chart.defaults.borderColor = chartDefaults.borderColor;

// ── Asistencia Semanal ──
<?php
$weeklyLabels = [];
$weeklyData   = [];
foreach (array_reverse($weeklyAttendance) as $w) {
    $weeklyLabels[] = date('d M', strtotime($w['week_start']));
    $weeklyData[]   = (int)$w['attendees'];
}
?>
new Chart(document.getElementById('chart-attendance'), {
    type: 'line',
    data: {
        labels: <?= json_encode($weeklyLabels) ?>,
        datasets: [{
            label: 'Asistentes',
            data: <?= json_encode($weeklyData) ?>,
            borderColor: colors.brand,
            backgroundColor: colors.brandA,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: colors.brand,
            pointRadius: 4,
            pointHoverRadius: 6,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: { grid: { color: 'rgba(55,65,81,0.5)' }, beginAtZero: true, ticks: { precision: 0 } }
        }
    }
});

// ── Estado de Membresía (Doughnut) ──
new Chart(document.getElementById('chart-status'), {
    type: 'doughnut',
    data: {
        labels: ['Activos', 'Visitantes', 'Nuevos Creyentes', 'Inactivos'],
        datasets: [{
            data: [<?= $activeMembers ?>, <?= $visitors ?>, <?= $newBelievers ?>, <?= $inactiveMembers ?>],
            backgroundColor: [colors.emerald, colors.blue, colors.purple, colors.red],
            borderWidth: 0,
            hoverOffset: 4,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        cutout: '70%',
        plugins: { legend: { display: false } }
    }
});

// ── Flujo de Caja (mini bar) ──
<?php
$cfLabels = [];
$cfIncome = [];
$cfExpense= [];
foreach ($cashFlow as $cf) {
    $cfLabels[] = monthName((int)$cf['period_month']);
    $cfIncome[]  = (float)$cf['ingresos'];
    $cfExpense[] = (float)$cf['egresos'];
}
?>
new Chart(document.getElementById('chart-cashflow'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($cfLabels) ?>,
        datasets: [
            { label: 'Ingresos', data: <?= json_encode($cfIncome) ?>, backgroundColor: colors.emeraldA, borderColor: colors.emerald, borderWidth: 1, borderRadius: 4 },
            { label: 'Egresos',  data: <?= json_encode($cfExpense) ?>, backgroundColor: colors.redA,   borderColor: colors.red,     borderWidth: 1, borderRadius: 4 },
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { display: false },
            y: { display: false, beginAtZero: true }
        }
    }
});

// ── Crecimiento Mensual ──
<?php
$growthLabels = [];
$growthData   = [];
foreach ($monthlyGrowth as $g) {
    $growthLabels[] = monthName((int)$g['month']) . ' ' . $g['year'];
    $growthData[]   = (int)$g['total'];
}
?>
new Chart(document.getElementById('chart-growth'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($growthLabels) ?>,
        datasets: [{
            label: 'Nuevos Miembros',
            data: <?= json_encode($growthData) ?>,
            backgroundColor: colors.brandA,
            borderColor: colors.brand,
            borderWidth: 1.5,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 } } },
            y: { grid: { color: 'rgba(55,65,81,0.5)' }, beginAtZero: true, ticks: { precision: 0 } }
        }
    }
});
</script>
