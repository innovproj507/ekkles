<?php /** Vista: Clasificación de categorías de ingreso por grupo */ ?>
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="<?= e($basePath) ?>" class="hover:text-white"><?= e(str_replace('Categorías · ', '', $title)) ?></a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-white">Clasificar categorías</span>
    </div>

    <form method="POST" action="<?= e($basePath) ?>/categorias" class="space-y-4">
        <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">

        <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-800">
                <h2 class="text-base font-semibold text-white">Grupo de cada categoría</h2>
                <p class="text-xs text-gray-500 mt-1">Escriba o elija un grupo (ej: Aportes y ofrendas, Otros ingresos, Saldos iniciales). Las categorías se mostrarán agrupadas al registrar un ingreso. Deje vacío para "Sin grupo".</p>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-800 bg-gray-800/30">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400">Categoría</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-400">Grupo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/80">
                    <?php if (empty($categories)): ?>
                    <tr><td colspan="2" class="px-6 py-10 text-center text-gray-500 text-sm">No hay categorías en esta cuenta.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td class="px-6 py-3 text-sm text-white"><?= e($cat['name']) ?></td>
                        <td class="px-6 py-3">
                            <input type="text" name="group[<?= (int)$cat['id'] ?>]" list="category-groups" maxlength="80"
                                   value="<?= e($cat['group_name'] ?? '') ?>" placeholder="Sin grupo"
                                   class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-gray-500">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <datalist id="category-groups">
            <?php foreach ($groups as $g): ?><option value="<?= e($g) ?>"><?php endforeach; ?>
        </datalist>

        <div class="flex items-center justify-between">
            <a href="<?= e($basePath) ?>" class="text-gray-400 hover:text-white text-sm font-medium">← Volver</a>
            <button type="submit" class="bg-brand-600 hover:bg-brand-500 text-white font-semibold py-2.5 px-6 rounded-xl text-sm">Guardar clasificación</button>
        </div>
    </form>
</div>
