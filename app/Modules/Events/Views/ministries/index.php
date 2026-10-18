<?php /** Vista: Ministerios */ ?>
<?php if (($mode ?? '') === 'create'): ?>
<!-- Formulario de creación -->
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/ministerios" class="hover:text-white">Ministerios</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-white">Nuevo Ministerio</span>
    </div>
    <form method="POST" action="/ministerios/crear" class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-4">
        <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">
        <h2 class="text-base font-semibold text-white mb-2">Información del Ministerio</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Nombre *</label>
                <input type="text" name="name" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" placeholder="ej: Ministerio de Jóvenes">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Color</label>
                <input type="color" name="color" value="#6366f1" class="w-full h-11 bg-gray-800 border border-gray-700 rounded-xl px-2 cursor-pointer">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Descripción</label>
                <textarea name="description" rows="2" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"></textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Líder</label>
                <select name="leader_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">Sin líder asignado</option>
                    <?php foreach ($members ?? [] as $m): ?>
                    <option value="<?= $m['id'] ?>"><?= e($m['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="flex items-center justify-between pt-2">
            <a href="/ministerios" class="text-gray-400 hover:text-white text-sm">← Cancelar</a>
            <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-500 hover:to-purple-500 text-white font-semibold py-2.5 px-6 rounded-xl text-sm transition-all">
                Crear Ministerio
            </button>
        </div>
    </form>
</div>

<?php else: ?>
<!-- Lista de ministerios -->
<div class="flex items-center justify-between mb-6">
    <p class="text-gray-400 text-sm"><span class="text-white font-semibold"><?= count($ministries) ?></span> ministerios activos</p>
    <?php if ($auth_can('ministries.create')): ?>
    <a href="/ministerios/crear" class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-500 hover:to-purple-500 text-white font-medium py-2.5 px-5 rounded-xl text-sm transition-all shadow-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Nuevo Ministerio
    </a>
    <?php endif; ?>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php if (empty($ministries)): ?>
    <div class="col-span-3 text-center py-12 text-gray-500">No hay ministerios registrados</div>
    <?php else: ?>
    <?php foreach ($ministries as $m): ?>
    <a href="/ministerios/<?= $m['id'] ?>" class="bg-gray-900 border border-gray-800 hover:border-gray-700 rounded-2xl p-6 transition-all group card-hover">
        <div class="flex items-start gap-4 mb-4">
            <div class="w-12 h-12 rounded-xl flex-shrink-0 flex items-center justify-center"
                 style="background-color: <?= e($m['color']) ?>22; border: 1px solid <?= e($m['color']) ?>44">
                <svg class="w-6 h-6" style="color: <?= e($m['color']) ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <h3 class="text-base font-semibold text-white group-hover:text-brand-400 transition-colors truncate">
                    <?= e($m['name']) ?>
                </h3>
                <?php if ($m['leader_name']): ?>
                <p class="text-xs text-gray-500 mt-0.5">Líder: <?= e($m['leader_name']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-2xl font-bold text-white"><?= $m['member_count'] ?></span>
            <span class="text-xs text-gray-400">miembros</span>
        </div>
    </a>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php endif; ?>
