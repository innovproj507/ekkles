<?php /** Vista: Reporte de Ocupación de Salones */
$dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
?>
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="/reportes" class="hover:text-white transition-colors">Reportes</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-white">Ocupación de Salones</span>
        </div>
        <form method="GET" action="/reportes/salones" class="flex gap-2">
            <select name="month" class="bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= $month === $m ? 'selected' : '' ?>><?= monthName($m) ?></option>
                <?php endfor; ?>
            </select>
            <select name="year" class="bg-gray-800 border border-gray-700 text-white rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                <?php for ($y = (int)date('Y') + 1; $y >= (int)date('Y') - 3; $y--): ?>
                <option value="<?= $y ?>" <?= $year === $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="bg-brand-600 hover:bg-brand-500 text-white font-medium py-2 px-4 rounded-xl text-sm">Ver</button>
        </form>
    </div>

    <!-- Resumen por salón -->
    <?php if (empty($occupancy)): ?>
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-10 text-center text-gray-500 text-sm">
        No hay salones registrados. <a href="/salones" class="text-brand-400 hover:text-brand-300">Registrar salones</a>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php foreach ($occupancy as $r): ?>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
            <p class="text-xs text-gray-400 font-medium mb-1"><?= e($r['name']) ?><?= $r['capacity'] ? ' · ' . (int)$r['capacity'] . ' personas' : '' ?></p>
            <p class="text-2xl font-bold text-white"><?= (int)$r['events_count'] ?> <span class="text-sm font-normal text-gray-400">eventos</span></p>
            <p class="text-xs text-gray-500 mt-1"><?= number_format((float)$r['hours'], 1) ?> horas reservadas en <?= monthName($month) ?> <?= $year ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Agenda por salón -->
    <?php foreach ($schedule as $roomName => $events): ?>
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-800">
            <h2 class="text-base font-semibold text-white"><?= e($roomName) ?></h2>
        </div>
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-800 bg-gray-800/30">
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400">Fecha</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400">Horario</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400">Evento</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400">Tipo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/80">
                <?php foreach ($events as $ev): $ts = strtotime($ev['start_datetime']); ?>
                <tr class="text-sm <?= !empty($ev['food_contribution']) ? 'bg-amber-500/5' : '' ?>">
                    <td class="px-6 py-3 text-gray-300 whitespace-nowrap"><?= $dias[date('w', $ts)] ?> <?= date('d/m', $ts) ?></td>
                    <td class="px-6 py-3 text-gray-300 whitespace-nowrap">
                        <?= date('H:i', $ts) ?><?= $ev['end_datetime'] ? ' – ' . date('H:i', strtotime($ev['end_datetime'])) : ' (sin hora de fin)' ?>
                    </td>
                    <td class="px-6 py-3">
                        <a href="/eventos/<?= (int)$ev['id'] ?>" class="text-white hover:text-brand-400"><?= e($ev['title']) ?></a>
                        <?php if (!empty($ev['food_contribution'])): ?><span class="ml-1 text-xs text-amber-300">🍲</span><?php endif; ?>
                    </td>
                    <td class="px-6 py-3">
                        <span class="inline-flex items-center gap-2 text-xs text-gray-400">
                            <span class="w-2 h-2 rounded-full" style="background-color: <?= e($ev['type_color'] ?? '#4f46e5') ?>"></span><?= e($ev['type'] ?? '') ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endforeach; ?>

    <p class="text-xs text-gray-500">Para los eventos sin hora de fin se asumen <?= \App\Modules\Events\Models\Event::DEFAULT_DURATION_HOURS ?> horas de uso del salón.</p>
</div>
