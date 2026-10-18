<?php /** Vista: Crear Ministerio */ ?>
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/ministerios" class="hover:text-white transition-colors">Ministerios</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-white"><?= e($title) ?></span>
    </div>

    <form method="POST" action="/ministerios/crear" class="space-y-6">
        <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">

        <?php if (!empty($errors)): ?>
        <div class="bg-red-500/10 border border-red-500/20 rounded-2xl p-4">
            <?php foreach ($errors as $fe): foreach ($fe as $err): ?>
            <p class="text-red-300 text-sm">• <?= e($err) ?></p>
            <?php endforeach; endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-4">
            <h2 class="text-base font-semibold text-white mb-4">Información del Ministerio</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Nombre del Ministerio <span class="text-red-400">*</span></label>
                    <input type="text" name="name" required value="<?= e($old['name'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                           placeholder="ej: Alabanza, Jóvenes, Ujieres...">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Color de Identificación</label>
                    <input type="color" name="color" value="<?= e($old['color'] ?? '#6366f1') ?>"
                           class="w-full h-[42px] bg-gray-800 border border-gray-700 rounded-xl px-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Líder del Ministerio</label>
                    <select name="leader_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Agregar Líder de Ministerio (Opcional)</option>
                        <?php foreach ($members as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= ($old['leader_id'] ?? '') == $m['id'] ? 'selected' : '' ?>>
                            <?= e($m['full_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Miembros Iniciales</label>
                    <select name="members[]" multiple class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 h-[100px]">
                        <?php foreach ($members as $m): ?>
                        <option value="<?= $m['id'] ?>">
                            <?= e($m['full_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-[11px] text-gray-500 mt-1">Mantén presionado Ctrl o Cmd para seleccionar varios</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Descripción</label>
                <textarea name="description" rows="4" 
                          class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"
                          placeholder="Describe brevemente el propósito de este ministerio..."><?= e($old['description'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="/ministerios" class="px-5 py-2.5 text-sm font-medium text-gray-300 hover:text-white transition-colors">Cancelar</a>
            <button type="submit" class="bg-brand-600 hover:bg-brand-500 text-white px-6 py-2.5 rounded-xl text-sm font-medium transition-colors shadow-lg shadow-brand-500/20">
                Crear Ministerio
            </button>
        </div>
    </form>
</div>
