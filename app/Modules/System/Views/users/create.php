<?php /** Vista: Crear Usuario */ ?>
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/usuarios" class="hover:text-white transition-colors">Usuarios</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-white"><?= e($title) ?></span>
    </div>

    <form method="POST" action="/usuarios/crear" class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden shadow-sm">
        <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">

        <div class="p-6 md:p-8 space-y-6">
            <?php if (!empty($flash_error)): ?>
                <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-red-400 text-sm">
                    <?= e($flash_error) ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Nombre Completo <span class="text-red-400">*</span></label>
                    <input type="text" name="name" required
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Rol de Acceso <span class="text-red-400">*</span></label>
                    <select name="role" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="administrador">Administrador</option>
                        <option value="ministro_principal">Ministro Principal</option>
                        <option value="evangelista">Evangelista</option>
                        <option value="tesorero">Tesorero / Finanzas</option>
                        <option value="secretario">Secretario</option>
                        <option value="lider_ministerio">Líder de Ministerio</option>
                        <option value="coordinador">Coordinador</option>
                        <option value="anciano">Anciano</option>
                        <option value="diacono">Diácono</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Correo Electrónico <span class="text-red-400">*</span></label>
                    <input type="email" name="email" required
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Teléfono</label>
                    <input type="text" name="phone"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Contraseña Inicial <span class="text-red-400">*</span></label>
                    <input type="password" name="password" required
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <p class="text-xs text-gray-500 mt-1.5">El usuario podrá cambiar su contraseña después de iniciar sesión.</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800/30 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-800">
            <a href="/usuarios" class="px-6 py-2.5 text-sm font-medium text-gray-400 hover:text-white transition-colors">Cancelar</a>
            <button type="submit" class="bg-brand-600 hover:bg-brand-500 text-white font-medium py-2.5 px-8 rounded-xl text-sm transition-colors shadow-lg">
                Crear Usuario
            </button>
        </div>
    </form>
</div>
