<?php /** Vista: Detalle de Ministerio */ ?>
<div class="max-w-5xl mx-auto">
    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/ministerios" class="hover:text-white">Ministerios</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-white"><?= e($ministry['name']) ?></span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Info del ministerio -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-2xl flex-shrink-0 flex items-center justify-center"
                             style="background-color: <?= e($ministry['color']) ?>22; border: 1px solid <?= e($ministry['color']) ?>44">
                            <svg class="w-8 h-8" style="color: <?= e($ministry['color']) ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-white"><?= e($ministry['name']) ?></h1>
                            <p class="text-gray-400 text-sm mt-1">Líder: <span class="text-white"><?= $ministry['leader_name'] ? e($ministry['leader_name']) : 'No asignado' ?></span></p>
                        </div>
                    </div>
                </div>

                <?php if (!empty($ministry['description'])): ?>
                <div class="mt-6 pt-6 border-t border-gray-800">
                    <p class="text-gray-300 text-sm leading-relaxed"><?= nl2br(e($ministry['description'])) ?></p>
                </div>
                <?php endif; ?>

                <!-- Acciones -->
                <div class="flex flex-wrap gap-2 mt-6 pt-6 border-t border-gray-800">
                    <?php if ($auth_can('ministries.edit')): ?>
                    <button type="button" onclick="openEditModal()" class="inline-flex items-center gap-2 bg-gray-700 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-xl text-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        Editar Ministerio
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Lista de miembros -->
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-white">
                        Miembros del Ministerio
                        <span class="ml-2 bg-brand-500/10 text-brand-400 text-xs px-2 py-0.5 rounded-full"><?= count($members) ?></span>
                    </h2>
                </div>
                <div class="divide-y divide-gray-800 max-h-[500px] overflow-y-auto">
                    <?php if (empty($members)): ?>
                    <div class="px-6 py-8 text-center text-gray-500 text-sm">
                        No hay miembros registrados en este ministerio
                    </div>
                    <?php else: ?>
                    <?php foreach ($members as $mem): ?>
                    <div class="flex items-center gap-3 px-6 py-3 hover:bg-gray-800/50 transition-colors">
                        <img src="<?= e(avatarUrl($mem['photo'] ?? null, $mem['first_name'] . ' ' . $mem['last_name'])) ?>"
                             alt="" class="w-10 h-10 rounded-xl object-cover flex-shrink-0 border border-gray-700">
                        <div class="min-w-0 flex-1">
                            <a href="/miembros/<?= $mem['id'] ?>" class="text-sm font-medium text-white hover:text-brand-400 transition-colors truncate block">
                                <?= e($mem['first_name'] . ' ' . $mem['last_name']) ?>
                            </a>
                            <p class="text-xs text-gray-500 mt-0.5"><?= e($mem['phone']) ?: 'Sin teléfono' ?></p>
                        </div>
                        <?php if ($mem['id'] == $ministry['leader_id']): ?>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400 bg-amber-400/10 px-2 py-1 rounded-lg border border-amber-400/20">Líder</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar: Stats -->
        <div class="space-y-6">
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
                <h3 class="text-sm font-semibold text-white mb-4">Resumen</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-gray-800 rounded-xl">
                        <span class="text-xs text-gray-400 font-medium">Total de Miembros</span>
                        <span class="text-lg font-bold text-white"><?= count($members) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal Placeholder -->
<?php if ($auth_can('ministries.edit')): ?>
<div id="editModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" onclick="closeEditModal()"></div>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        <div class="relative bg-gray-900 border border-gray-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-lg w-full">
            <form method="POST" action="/ministerios/<?= $ministry['id'] ?>/editar" class="p-6">
                <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">
                <h3 class="text-lg font-semibold text-white mb-4">Editar Ministerio</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Nombre *</label>
                        <input type="text" name="name" value="<?= e($ministry['name']) ?>" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Color</label>
                            <input type="color" name="color" value="<?= e($ministry['color']) ?>" class="w-full h-[42px] bg-gray-800 border border-gray-700 rounded-xl px-2 cursor-pointer">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">Líder del Ministerio</label>
                            <select name="leader_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <option value="">Ninguno (Opcional)</option>
                                <?php foreach ($all_members ?? [] as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= $ministry['leader_id'] == $m['id'] ? 'selected' : '' ?>>
                                    <?= e($m['full_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Descripción</label>
                        <textarea name="description" rows="3" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"><?= e($ministry['description']) ?></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm font-medium text-gray-300 hover:text-white transition-colors">Cancelar</button>
                    <button type="submit" class="bg-brand-600 hover:bg-brand-500 text-white px-5 py-2 rounded-xl text-sm font-medium transition-colors">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditModal() {
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>
<?php endif; ?>
