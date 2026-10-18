<?php /** Vista: Configuración General de la Iglesia */ ?>
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-white">Configuración de la Iglesia</h2>
        <p class="text-gray-400 text-sm mt-1">Actualiza los datos generales y el logotipo que aparecerá en los reportes.</p>
    </div>

    <form action="/configuracion" method="POST" enctype="multipart/form-data" class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden shadow-xl">
        <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">

        <div class="p-6 md:p-8 space-y-8">
            
            <!-- Branding / Logo -->
            <div>
                <h3 class="text-lg font-medium text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Identidad Visual (Logo)
                </h3>
                <div class="flex items-start gap-6">
                    <div class="flex-shrink-0">
                        <?php if (!empty($church['logo'])): ?>
                            <div class="w-32 h-32 rounded-2xl border border-gray-700 bg-gray-800 flex items-center justify-center overflow-hidden p-2">
                                <img src="<?= url('uploads/' . $church['logo']) ?>" alt="Logo actual" class="max-w-full max-h-full object-contain">
                            </div>
                        <?php else: ?>
                            <div class="w-32 h-32 rounded-2xl border border-gray-700 border-dashed bg-gray-800/50 flex flex-col items-center justify-center text-gray-500">
                                <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="text-xs">Sin logo</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Subir nuevo logo</label>
                        <input type="file" name="logo" accept="image/png, image/jpeg, image/jpg, image/webp"
                               class="block w-full text-sm text-gray-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-brand-500/10 file:text-brand-400 hover:file:bg-brand-500/20 transition-all border border-gray-700 rounded-xl bg-gray-800 focus:outline-none">
                        <p class="mt-2 text-xs text-gray-500">Recomendado: Formato PNG con fondo transparente. Tamaño máximo 2MB. Resoluciones cuadradas (ej: 500x500px).</p>
                    </div>
                </div>
            </div>

            <hr class="border-gray-800">

            <!-- Información General -->
            <div>
                <h3 class="text-lg font-medium text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Información General
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Nombre de la Iglesia <span class="text-red-400">*</span></label>
                        <input type="text" name="name" value="<?= e($church['name'] ?? '') ?>" required
                               class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Slogan o Lema</label>
                        <input type="text" name="slogan" value="<?= e($church['slogan'] ?? '') ?>"
                               class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    </div>
                </div>
            </div>

            <hr class="border-gray-800">

            <!-- Contacto -->
            <div>
                <h3 class="text-lg font-medium text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Contacto y Ubicación
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Dirección Principal</label>
                        <input type="text" name="address" value="<?= e($church['address'] ?? '') ?>"
                               class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Teléfono</label>
                        <input type="text" name="phone" value="<?= e($church['phone'] ?? '') ?>"
                               class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Correo Electrónico</label>
                        <input type="email" name="email" value="<?= e($church['email'] ?? '') ?>"
                               class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Sitio Web o Red Social</label>
                        <input type="text" name="website" value="<?= e($church['website'] ?? '') ?>" placeholder="https://..."
                               class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                    </div>
                </div>
            </div>

        </div>

        <div class="bg-gray-800/50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-800">
            <button type="reset" class="text-gray-400 hover:text-white px-4 py-2 font-medium transition-colors">Cancelar</button>
            <button type="submit" class="bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-500 hover:to-purple-500 text-white font-medium py-2.5 px-6 rounded-xl transition-all shadow-lg shadow-brand-500/20">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
