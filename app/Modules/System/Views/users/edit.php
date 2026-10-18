<?php /** Vista: Editar Usuario */ ?>
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/usuarios" class="hover:text-white transition-colors">Usuarios</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-white"><?= e($title) ?></span>
    </div>

    <form method="POST" action="/usuarios/<?= $user['id'] ?>/editar" class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden shadow-sm">
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
                    <input type="text" name="name" required value="<?= e($user['name']) ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Rol de Acceso <span class="text-red-400">*</span></label>
                    <select name="role" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="administrador" <?= $user['role'] === 'administrador' ? 'selected' : '' ?>>Administrador</option>
                        <option value="ministro_principal" <?= $user['role'] === 'ministro_principal' ? 'selected' : '' ?>>Ministro Principal</option>
                        <option value="evangelista" <?= $user['role'] === 'evangelista' ? 'selected' : '' ?>>Evangelista</option>
                        <option value="tesorero" <?= $user['role'] === 'tesorero' ? 'selected' : '' ?>>Tesorero / Finanzas</option>
                        <option value="secretario" <?= $user['role'] === 'secretario' ? 'selected' : '' ?>>Secretario</option>
                        <option value="lider_ministerio" <?= $user['role'] === 'lider_ministerio' ? 'selected' : '' ?>>Líder de Ministerio</option>
                        <option value="coordinador" <?= $user['role'] === 'coordinador' ? 'selected' : '' ?>>Coordinador</option>
                        <option value="anciano" <?= $user['role'] === 'anciano' ? 'selected' : '' ?>>Anciano</option>
                        <option value="diacono" <?= $user['role'] === 'diacono' ? 'selected' : '' ?>>Diácono</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Correo Electrónico <span class="text-red-400">*</span></label>
                    <input type="email" name="email" required value="<?= e($user['email']) ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Teléfono</label>
                    <input type="text" name="phone" value="<?= e($user['phone'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Estado del Usuario</label>
                    <div class="flex items-center gap-3 bg-gray-800/50 p-3 rounded-xl border border-gray-700">
                        <input type="checkbox" id="is_active" name="is_active" value="1" <?= $user['is_active'] ? 'checked' : '' ?>
                               class="w-4 h-4 text-brand-600 bg-gray-800 border-gray-700 rounded focus:ring-brand-500">
                        <label for="is_active" class="text-sm text-gray-300">Cuenta activa (Puede iniciar sesión en el sistema)</label>
                    </div>
                </div>

                <div class="md:col-span-2 border-t border-gray-800 pt-6 mt-2">
                    <h3 class="text-sm font-medium text-white mb-4">Cambiar Contraseña (Opcional)</h3>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Nueva Contraseña</label>
                    <input type="password" name="password" placeholder="Dejar en blanco para no cambiarla"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <div class="bg-gray-800/30 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-800">
            <a href="/usuarios" class="px-6 py-2.5 text-sm font-medium text-gray-400 hover:text-white transition-colors">Cancelar</a>
            <button type="submit" class="bg-brand-600 hover:bg-brand-500 text-white font-medium py-2.5 px-8 rounded-xl text-sm transition-colors shadow-lg">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
