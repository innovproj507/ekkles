<?php /** Vista: Reporte de Egresos */ ?>
<div class="space-y-6">
    <!-- Breadcrumb & Back Link -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="/reportes" class="hover:text-white transition-colors">Reportes</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-white">Reporte de Egresos</span>
        </div>
        
        <!-- Export buttons -->
        <div class="flex gap-2">
            <a href="/reportes/exportar/egresos?format=excel&<?= http_build_query($filters) ?>" 
               class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-750 border border-gray-700 text-gray-200 font-medium py-2 px-4 rounded-xl text-sm transition-all shadow-md">
                📊 Exportar Excel
            </a>
            <a href="/reportes/exportar/egresos?format=pdf&<?= http_build_query($filters) ?>" 
               class="inline-flex items-center gap-2 bg-red-650 hover:bg-red-600 text-white font-medium py-2 px-4 rounded-xl text-sm transition-all shadow-md">
                📕 Exportar PDF
            </a>
        </div>
    </div>

    <!-- Filtros de Búsqueda -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden mb-6">
        <div class="px-6 py-4 flex items-center justify-between cursor-pointer hover:bg-gray-800/50 transition-colors" onclick="const fc = document.getElementById('filter-content'); fc.classList.toggle('max-h-0'); fc.classList.toggle('max-h-[1000px]'); fc.classList.toggle('opacity-0'); fc.classList.toggle('opacity-100'); fc.classList.toggle('invisible'); document.getElementById('filter-icon').classList.toggle('rotate-180');">
            <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filtros de Reporte
            </h3>
            <svg id="filter-icon" class="w-5 h-5 text-gray-400 transition-transform duration-300 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>
        <div id="filter-content" class="transition-all duration-300 ease-in-out max-h-[1000px] opacity-100 overflow-hidden">
            <form method="GET" action="/reportes/egresos" id="filter-form" class="p-6 pt-4 border-t border-gray-800/60">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
            
            <!-- Módulo -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Módulo / Procedencia</label>
                <select name="target_module" id="target-module-select" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">Todos los módulos</option>
                    <option value="ofrendas" <?= $filters['target_module'] === 'ofrendas' ? 'selected' : '' ?>>Ofrendas</option>
                    <option value="construccion" <?= $filters['target_module'] === 'construccion' ? 'selected' : '' ?>>Construcción</option>
                    <option value="donaciones" <?= $filters['target_module'] === 'donaciones' ? 'selected' : '' ?>>Obras Misioneras y Donaciones</option>
                </select>
            </div>

            <!-- Categoría -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Categoría</label>
                <select name="category_id" id="category-select" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="" data-module="">Todas las categorías</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $filters['category_id'] == $cat['id'] ? 'selected' : '' ?> data-module="<?= e($cat['target_module'] ?? '') ?>">
                        <?= e($cat['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Año -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Año</label>
                <select name="year" id="year-select" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">Cualquier año</option>
                    <?php foreach ($years as $y): ?>
                    <option value="<?= $y ?>" <?= $filters['year'] == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Fecha Inicio -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Fecha Inicio</label>
                <input type="date" name="start_date" id="start-date-input" value="<?= e($filters['start_date']) ?>"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <!-- Fecha Fin -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Fecha Fin</label>
                <input type="date" name="end_date" id="end-date-input" value="<?= e($filters['end_date']) ?>"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

        </div>

        <div class="flex items-center justify-between gap-4 mt-6 pt-4 border-t border-gray-800/60">
            <a href="/reportes/egresos" class="text-xs text-gray-500 hover:text-white transition-colors">Limpiar Filtros</a>
            <button type="submit" class="bg-brand-650 hover:bg-brand-600 text-white font-medium py-2.5 px-6 rounded-xl text-sm transition-colors shadow-lg shadow-brand-900/20">
                Generar Reporte
            </button>
        </div>
            </form>
        </div>
    </div>

    <!-- KPIs de Resumen -->
    <?php
    $totalAmount = 0;
    $maxExpense = 0;
    $maxExpenseCategory = '—';
    $categoriesExpenses = [];

    foreach ($records as $r) {
        $amount = (float)$r['amount'];
        $totalAmount += $amount;
        if ($amount > $maxExpense) {
            $maxExpense = $amount;
        }
        if (!isset($categoriesExpenses[$r['category_name']])) {
            $categoriesExpenses[$r['category_name']] = 0;
        }
        $categoriesExpenses[$r['category_name']] += $amount;
    }

    if (!empty($categoriesExpenses)) {
        arsort($categoriesExpenses);
        $maxExpenseCategory = array_key_first($categoriesExpenses);
    }
    ?>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-gradient-to-br from-red-600/15 to-red-650/5 border border-red-500/20 rounded-2xl p-6 shadow-xl">
            <p class="text-xs text-red-400 font-medium mb-1.5">Total de Egresos Filtrados</p>
            <p class="text-3xl font-extrabold text-white"><?= formatCurrency($totalAmount) ?></p>
            <p class="text-xs text-gray-500 mt-2"><?= count($records) ?> transacciones registradas</p>
        </div>
        <div class="bg-gradient-to-br from-amber-600/15 to-amber-650/5 border border-amber-500/20 rounded-2xl p-6 shadow-xl">
            <p class="text-xs text-amber-400 font-medium mb-1.5">Mayor Egreso Único</p>
            <p class="text-3xl font-extrabold text-white"><?= formatCurrency($maxExpense) ?></p>
        </div>
        <div class="bg-gradient-to-br from-brand-600/15 to-brand-650/5 border border-brand-500/20 rounded-2xl p-6 shadow-xl">
            <p class="text-xs text-brand-400 font-medium mb-1.5">Mayor Categoría de Gasto</p>
            <p class="text-xl font-bold text-white truncate" title="<?= e($maxExpenseCategory) ?>"><?= e($maxExpenseCategory) ?></p>
            <?php if ($maxExpenseCategory !== '—'): ?>
            <p class="text-xs text-gray-500 mt-2">Monto acumulado: <?= formatCurrency($categoriesExpenses[$maxExpenseCategory]) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Gráfica Analítica -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-4 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-800 pb-4">
            <div>
                <h2 class="text-base font-semibold text-white">Análisis por Categoría</h2>
                <p class="text-xs text-gray-500">Distribución de gastos en base a la consulta actual</p>
            </div>
            
            <!-- Selector de tipo de gráfico -->
            <div class="inline-flex bg-gray-800 rounded-xl p-1 gap-1 border border-gray-700">
                <button type="button" id="btn-chart-pie" class="px-4 py-1.5 rounded-lg text-xs font-semibold transition-all bg-brand-600 text-white shadow">
                    🎂 Pastel
                </button>
                <button type="button" id="btn-chart-bar" class="px-4 py-1.5 rounded-lg text-xs font-semibold transition-all text-gray-400 hover:text-white">
                    📊 Barras
                </button>
            </div>
        </div>

        <div class="w-full flex justify-center py-4" style="height: 350px;">
            <?php if (empty($chartData)): ?>
            <div class="flex flex-col items-center justify-center text-gray-500 text-sm gap-2">
                <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                No hay datos disponibles para graficar
            </div>
            <?php else: ?>
            <canvas id="chart-expenses" class="max-w-full h-full"></canvas>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabla Detallada -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-800">
            <h2 class="text-base font-semibold text-white">Listado de Registros</h2>
            <p class="text-xs text-gray-500">Transacciones detalladas ordenadas cronológicamente</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-800 bg-gray-800/30">
                        <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">Fecha</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">Factura</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">RUC/Cédula</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">Proveedor</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">Detalle</th>
                        <th class="text-right px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">Monto</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">Categoría</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">Módulo</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 tracking-wider">Autorizado Por</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/80">
                    <?php if (empty($records)): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500 text-sm">
                            No se encontraron egresos con los filtros seleccionados
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($records as $r): ?>
                    <tr class="hover:bg-gray-800/20 transition-colors text-sm">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-300"><?= formatDate($r['transaction_date']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-white"><?= e($r['invoice_number'] ?? 'N/A') ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-400"><?= e($r['provider_ruc'] ?? 'N/A') ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-350"><?= e($r['provider_name'] ?? 'N/A') ?></td>
                        <td class="px-6 py-4 text-gray-350 min-w-[200px]"><?= e($r['description']) ?></td>
                        <td class="px-6 py-4 text-right whitespace-nowrap text-red-400 font-bold">-<?= formatCurrency((float)$r['amount']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-400"><?= e($r['category_name']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if (($r['target_module'] ?? '') === 'ofrendas'): ?>
                            <span class="inline-flex items-center text-xs px-2.5 py-0.5 rounded-full font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Ofrendas</span>
                            <?php elseif (($r['target_module'] ?? '') === 'construccion'): ?>
                            <span class="inline-flex items-center text-xs px-2.5 py-0.5 rounded-full font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">Construcción</span>
                            <?php elseif (($r['target_module'] ?? '') === 'donaciones'): ?>
                            <span class="inline-flex items-center text-xs px-2.5 py-0.5 rounded-full font-medium bg-purple-500/10 text-purple-400 border border-purple-500/20">Obras Misioneras</span>
                            <?php else: ?>
                            <span class="inline-flex items-center text-xs px-2.5 py-0.5 rounded-full font-medium bg-gray-500/10 text-gray-400 border border-gray-500/20">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 text-xs text-brand-300 bg-brand-500/10 px-2.5 py-1 rounded-lg border border-brand-500/20">
                                👤 <?= e($r['approved_by_name'] ?? 'Sistema') ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Scripts de Gráficos e Integración -->
<?php if (!empty($chartData)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = <?= json_encode($chartData) ?>;
    
    // Preparar colores dinámicos pero armónicos
    const colors = [
        'rgba(239, 68, 68, 0.7)',   // red
        'rgba(245, 158, 11, 0.7)',  // amber
        'rgba(16, 185, 129, 0.7)',  // emerald
        'rgba(59, 130, 246, 0.7)',  // blue
        'rgba(168, 85, 247, 0.7)',  // purple
        'rgba(99, 102, 241, 0.7)',  // brand
        'rgba(236, 72, 153, 0.7)',  // pink
        'rgba(14, 165, 233, 0.7)',  // sky
        'rgba(20, 184, 166, 0.7)',  // teal
        'rgba(132, 204, 22, 0.7)',  // lime
        'rgba(234, 179, 8, 0.7)',   // yellow
        'rgba(249, 115, 22, 0.7)',  // orange
    ];
    
    const borderColors = colors.map(c => c.replace('0.7', '1'));

    const labels = chartData.map(c => c.label);
    const values = chartData.map(c => parseFloat(c.value));

    const ctx = document.getElementById('chart-expenses').getContext('2d');
    let chartInstance = null;

    function buildChart(type) {
        if (chartInstance) {
            chartInstance.destroy();
        }

        const isPie = (type === 'pie');
        
        chartInstance = new Chart(ctx, {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderColor: isPie ? 'rgba(17, 24, 39, 1)' : borderColors,
                    borderWidth: isPie ? 2 : 1,
                    borderRadius: isPie ? 0 : 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: isPie,
                        position: 'right',
                        labels: {
                            color: '#9ca3af',
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.raw !== null && context.raw !== undefined) {
                                    label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.raw);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: isPie ? {} : {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#9ca3af', font: { size: 10 } }
                    },
                    y: {
                        grid: { color: 'rgba(75, 85, 99, 0.2)' },
                        ticks: {
                            color: '#9ca3af',
                            font: { size: 10 },
                            callback: function(value) {
                                return '$' + value;
                            }
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Inicializar como pastel
    buildChart('pie');

    // Manejar clics de los botones de cambio de tipo
    const btnPie = document.getElementById('btn-chart-pie');
    const btnBar = document.getElementById('btn-chart-bar');

    btnPie.addEventListener('click', function() {
        btnPie.className = 'px-4 py-1.5 rounded-lg text-xs font-semibold transition-all bg-brand-600 text-white shadow';
        btnBar.className = 'px-4 py-1.5 rounded-lg text-xs font-semibold transition-all text-gray-400 hover:text-white';
        buildChart('pie');
    });

    btnBar.addEventListener('click', function() {
        btnBar.className = 'px-4 py-1.5 rounded-lg text-xs font-semibold transition-all bg-brand-600 text-white shadow';
        btnPie.className = 'px-4 py-1.5 rounded-lg text-xs font-semibold transition-all text-gray-400 hover:text-white';
        buildChart('bar');
    });
});
</script>
<?php endif; ?>

<!-- Javascript para filtrado dinámico de categorías -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category-select');
    const targetModuleSelect = document.getElementById('target-module-select');

    // Cargar y almacenar copia de todas las opciones originales
    let originalOptions = Array.from(categorySelect.options)
        .filter(opt => opt.value !== '')
        .map(opt => ({
            value: opt.value,
            text: opt.textContent,
            module: opt.getAttribute('data-module'),
            selected: opt.selected
        }));

    // Función de filtrado dinámico
    function filterCategories() {
        const selectedModule = targetModuleSelect.value;
        const previousValue = categorySelect.value;
        
        categorySelect.innerHTML = '';
        
        const defaultOpt = document.createElement('option');
        defaultOpt.value = '';
        defaultOpt.textContent = 'Todas las categorías';
        categorySelect.appendChild(defaultOpt);
        
        originalOptions.forEach(opt => {
            if (!selectedModule || opt.module === selectedModule) {
                const newOpt = document.createElement('option');
                newOpt.value = opt.value;
                newOpt.textContent = opt.text;
                newOpt.setAttribute('data-module', opt.module);
                
                // Mantener seleccionada la opción anterior si corresponde al nuevo módulo
                if (opt.value == previousValue) {
                    newOpt.selected = true;
                }
                categorySelect.appendChild(newOpt);
            }
        });
    }

    targetModuleSelect.addEventListener('change', filterCategories);
    
    // Si ya hay cargado un módulo al iniciar, aplicar filtro
    if (targetModuleSelect.value) {
        filterCategories();
    }
});
</script>
