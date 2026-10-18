<?php
/**
 * Vista: Editar Miembro (reutiliza el formulario de crear con datos pre-rellenos)
 */
// Copiar los datos del miembro al array $old para reutilizar la vista de crear
$old = array_merge($member, ['old' => []]);
?>
<div class="max-w-4xl mx-auto">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/miembros" class="hover:text-white transition-colors">Miembros</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="/miembros/<?= $member['id'] ?>" class="hover:text-white transition-colors"><?= e($member['first_name'] . ' ' . $member['last_name']) ?></a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-white">Editar</span>
    </div>

    <form method="POST" action="/miembros/<?= $member['id'] ?>/editar" enctype="multipart/form-data" class="space-y-6" novalidate>
        <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">

        <?php if (!empty($errors)): ?>
        <div class="bg-red-500/10 border border-red-500/20 rounded-2xl p-4">
            <ul class="list-disc list-inside space-y-1">
                <?php foreach ($errors as $fieldErrors): foreach ($fieldErrors as $error): ?>
                <li class="text-red-300 text-sm"><?= e($error) ?></li>
                <?php endforeach; endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Los mismos campos del formulario de crear, ya rellenados -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h2 class="text-base font-semibold text-white mb-5">Información Personal</h2>
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <div class="flex flex-col items-center gap-3">
                    <div class="relative">
                        <img id="photo-preview"
                             src="<?= e(avatarUrl($member['photo'], $member['first_name'] . ' ' . $member['last_name'])) ?>"
                             alt="Foto" class="w-28 h-28 rounded-2xl object-cover border-2 border-gray-700">
                        <label for="photo" class="absolute bottom-1 right-1 w-7 h-7 bg-brand-600 hover:bg-brand-500 rounded-lg flex items-center justify-center cursor-pointer transition-colors shadow-lg">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            </svg>
                        </label>
                    </div>
                    <input type="file" id="photo" name="photo" accept="image/*" class="hidden" onchange="previewPhoto(this)">
                </div>

                <div class="lg:col-span-3 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Nombre *</label>
                        <input type="text" name="first_name" required value="<?= e($member['first_name']) ?>"
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Apellido *</label>
                        <input type="text" name="last_name" required value="<?= e($member['last_name']) ?>"
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Email</label>
                        <input type="email" name="email" value="<?= e($member['email'] ?? '') ?>"
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Teléfono</label>
                        <input type="tel" name="phone" value="<?= e($member['phone'] ?? '') ?>"
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Estado</label>
                        <select name="status" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <?php foreach (['activo','inactivo','visitante','nuevo_creyente','trasladado'] as $st): ?>
                            <option value="<?= $st ?>" <?= $member['status'] === $st ? 'selected' : '' ?>><?= memberStatusLabel($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">¿Convertido (bautizado)?</label>
                        <select name="is_converted" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="1" <?= (int)($member['is_converted'] ?? 1) === 1 ? 'selected' : '' ?>>Sí</option>
                            <option value="0" <?= (int)($member['is_converted'] ?? 1) === 0 ? 'selected' : '' ?>>No</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Ministerio</label>
                        <select name="ministry_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="">Sin ministerio</option>
                            <?php foreach ($ministries as $min): ?>
                            <option value="<?= $min['id'] ?>" <?= $member['ministry_id'] == $min['id'] ? 'selected' : '' ?>><?= e($min['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Fecha Membresía</label>
                        <input type="date" name="membership_date" value="<?= e($member['membership_date'] ?? '') ?>"
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Fecha Bautismo</label>
                        <input type="date" name="baptism_date" value="<?= e($member['baptism_date'] ?? '') ?>"
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <?php if ($auth_can('members.edit_notes')): ?>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Notas de Discipulado</label>
                        <textarea name="pastoral_notes" rows="3"
                                  class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"><?= e($member['pastoral_notes'] ?? '') ?></textarea>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between gap-4 pb-6">
            <a href="/miembros/<?= $member['id'] ?>" class="text-gray-400 hover:text-white text-sm font-medium transition-colors">
                ← Cancelar
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-500 hover:to-purple-500
                           text-white font-semibold py-3 px-8 rounded-xl text-sm transition-all shadow-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Actualizar Miembro
            </button>
        </div>
    </form>
</div>
<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('photo-preview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
