<?php /** Vista: Crear/Editar Miembro */ ?>
<div class="max-w-4xl mx-auto">

    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/miembros" class="hover:text-white transition-colors">Miembros</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-white"><?= e($title) ?></span>
    </div>

    <form method="POST" action="/miembros/crear" enctype="multipart/form-data" class="space-y-6" novalidate>
        <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">

        <!-- Errores de validación -->
        <?php if (!empty($errors)): ?>
        <div class="bg-red-500/10 border border-red-500/20 rounded-2xl p-4">
            <p class="text-red-400 font-medium text-sm mb-2">Por favor corrija los siguientes errores:</p>
            <ul class="list-disc list-inside space-y-1">
                <?php foreach ($errors as $field => $fieldErrors): ?>
                    <?php foreach ($fieldErrors as $error): ?>
                    <li class="text-red-300 text-sm"><?= e($error) ?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Sección: Foto e Info Básica -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h2 class="text-base font-semibold text-white mb-5 flex items-center gap-2">
                <span class="w-6 h-6 bg-brand-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </span>
                Información Personal
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Foto -->
                <div class="flex flex-col items-center gap-3">
                    <div class="relative">
                        <img id="photo-preview"
                             src="<?= e(avatarUrl($old['photo'] ?? null, ($old['first_name'] ?? '') . ' ' . ($old['last_name'] ?? ''))) ?>"
                             alt="Foto de perfil"
                             class="w-28 h-28 rounded-2xl object-cover border-2 border-gray-700">
                        <label for="photo"
                               class="absolute bottom-1 right-1 w-7 h-7 bg-brand-600 hover:bg-brand-500 rounded-lg flex items-center justify-center cursor-pointer transition-colors shadow-lg">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </label>
                    </div>
                    <input type="file" id="photo" name="photo" accept="image/*" class="hidden"
                           onchange="previewPhoto(this)">
                    <p class="text-xs text-gray-500 text-center">JPG, PNG, WebP<br>Máx. 5MB</p>
                </div>

                <!-- Campos principales -->
                <div class="lg:col-span-3 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nombre -->
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Nombre <span class="text-red-400">*</span></label>
                        <input type="text" name="first_name" required value="<?= e($old['first_name'] ?? '') ?>"
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-gray-600"
                               placeholder="Nombre(s)">
                    </div>
                    <!-- Apellido -->
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Apellido <span class="text-red-400">*</span></label>
                        <input type="text" name="last_name" required value="<?= e($old['last_name'] ?? '') ?>"
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-gray-600"
                               placeholder="Apellido(s)">
                    </div>
                    <!-- Fecha de nacimiento -->
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Fecha de Nacimiento</label>
                        <input type="date" name="birth_date" value="<?= e($old['birth_date'] ?? '') ?>"
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                    <!-- Género -->
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Género</label>
                        <select name="gender"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="M" <?= ($old['gender'] ?? 'M') === 'M' ? 'selected' : '' ?>>Masculino</option>
                            <option value="F" <?= ($old['gender'] ?? '') === 'F' ? 'selected' : '' ?>>Femenino</option>
                            <option value="otro" <?= ($old['gender'] ?? '') === 'otro' ? 'selected' : '' ?>>Otro</option>
                        </select>
                    </div>
                    <!-- Estado civil -->
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Estado Civil</label>
                        <select name="marital_status"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="">Seleccionar...</option>
                            <?php foreach (['soltero','casado','divorciado','viudo','union_libre'] as $ms): ?>
                            <option value="<?= $ms ?>" <?= ($old['marital_status'] ?? '') === $ms ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $ms)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Estado del miembro -->
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Estado</label>
                        <select name="status"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <?php foreach (['activo','inactivo','visitante','nuevo_creyente','trasladado'] as $st): ?>
                            <option value="<?= $st ?>" <?= ($old['status'] ?? 'visitante') === $st ? 'selected' : '' ?>><?= memberStatusLabel($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <!-- Convertido (bautizado) -->
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">¿Convertido (bautizado)?</label>
                        <select name="is_converted"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="1" <?= (string)($old['is_converted'] ?? '1') === '1' ? 'selected' : '' ?>>Sí</option>
                            <option value="0" <?= (string)($old['is_converted'] ?? '1') === '0' ? 'selected' : '' ?>>No</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección: Contacto -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h2 class="text-base font-semibold text-white mb-5">📞 Información de Contacto</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Correo Electrónico</label>
                    <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                           placeholder="correo@ejemplo.com">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Teléfono Principal</label>
                    <input type="tel" name="phone" value="<?= e($old['phone'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                           placeholder="+57 300 000 0000">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Teléfono Alternativo</label>
                    <input type="tel" name="phone_alt" value="<?= e($old['phone_alt'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Dirección</label>
                    <input type="text" name="address" value="<?= e($old['address'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                           placeholder="Calle, número, barrio">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Ciudad</label>
                    <input type="text" name="city" value="<?= e($old['city'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <!-- Contacto de emergencia -->
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Contacto de Emergencia</label>
                    <input type="text" name="emergency_contact" value="<?= e($old['emergency_contact'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Teléfono Emergencia</label>
                    <input type="tel" name="emergency_phone" value="<?= e($old['emergency_phone'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <!-- Sección: Documento -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h2 class="text-base font-semibold text-white mb-5">🪪 Documento e Identificación</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Tipo de Documento</label>
                    <select name="document_type"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <?php foreach (['CC','CE','Pasaporte','NIT','TI','otro'] as $dt): ?>
                        <option value="<?= $dt ?>" <?= ($old['document_type'] ?? 'CC') === $dt ? 'selected' : '' ?>><?= $dt ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Número de Documento</label>
                    <input type="text" name="document_number" value="<?= e($old['document_number'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Ocupación</label>
                    <input type="text" name="occupation" value="<?= e($old['occupation'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">¿Cómo nos conoció?</label>
                    <input type="text" name="how_did_you_know" value="<?= e($old['how_did_you_know'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                           placeholder="Redes, invitado, familia...">
                </div>
            </div>
        </div>

        <!-- Sección: Información Eclesiástica -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
            <h2 class="text-base font-semibold text-white mb-5">⛪ Información Eclesiástica</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Ministerio</label>
                    <select name="ministry_id"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Sin ministerio</option>
                        <?php foreach ($ministries as $ministry): ?>
                        <option value="<?= $ministry['id'] ?>" <?= ($old['ministry_id'] ?? '') == $ministry['id'] ? 'selected' : '' ?>>
                            <?= e($ministry['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Fecha de Bautismo</label>
                    <input type="date" name="baptism_date" value="<?= e($old['baptism_date'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Fecha de Membresía</label>
                    <input type="date" name="membership_date" value="<?= e($old['membership_date'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Fecha de Conversión</label>
                    <input type="date" name="conversion_date" value="<?= e($old['conversion_date'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <?php if ($auth_can('members.edit_notes')): ?>
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Notas de Discipulado</label>
                    <textarea name="pastoral_notes" rows="3"
                              class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"
                              placeholder="Observaciones, necesidades de oración, seguimiento..."><?= e($old['pastoral_notes'] ?? '') ?></textarea>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Botones -->
        <div class="flex items-center justify-between gap-4 pb-6">
            <a href="/miembros"
               class="flex items-center gap-2 text-gray-400 hover:text-white text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Cancelar
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-500 hover:to-purple-500
                           text-white font-semibold py-3 px-8 rounded-xl text-sm transition-all shadow-lg shadow-brand-500/20
                           transform hover:scale-[1.02]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Guardar Miembro
            </button>
        </div>
    </form>
</div>

<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photo-preview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
