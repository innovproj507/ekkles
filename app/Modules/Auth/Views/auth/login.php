<?php /** Vista: Login */ ?>
<div class="bg-gray-900/80 backdrop-blur-xl border border-gray-800 rounded-2xl p-8 shadow-2xl">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-white">Iniciar Sesión</h2>
        <p class="text-gray-400 text-sm mt-1">Ingrese sus credenciales para continuar</p>
    </div>

    <form method="POST" action="/login" id="form-login" class="space-y-5" novalidate>
        <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">

        <!-- Email -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">
                Correo Electrónico
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                    </svg>
                </div>
                <input type="email" id="email" name="email" required
                       autocomplete="email"
                       placeholder="admin@ekkles.local"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl pl-10 pr-4 py-3 text-sm
                              focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent
                              placeholder-gray-600 transition-all">
            </div>
        </div>

        <!-- Contraseña -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-sm font-medium text-gray-300">Contraseña</label>
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <input type="password" id="password" name="password" required
                       autocomplete="current-password"
                       placeholder="••••••••"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl pl-10 pr-12 py-3 text-sm
                              focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent
                              placeholder-gray-600 transition-all">
                <!-- Toggle password visibility -->
                <button type="button" onclick="togglePassword()"
                        class="absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-300 transition-colors">
                    <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Botón de envío -->
        <button type="submit" id="btn-login"
                class="w-full bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-500 hover:to-purple-500
                       text-white font-semibold py-3 px-6 rounded-xl text-sm transition-all duration-200
                       transform hover:scale-[1.02] active:scale-[0.98] shadow-lg shadow-brand-500/20
                       flex items-center justify-center gap-2">
            <span id="btn-text">Iniciar Sesión</span>
            <svg id="btn-spinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </button>
    </form>
</div>

<script>
function togglePassword() {
    const pwd = document.getElementById('password');
    pwd.type = pwd.type === 'password' ? 'text' : 'password';
}

document.getElementById('form-login').addEventListener('submit', function() {
    document.getElementById('btn-text').textContent = 'Verificando...';
    document.getElementById('btn-spinner').classList.remove('hidden');
    document.getElementById('btn-login').disabled = true;
});
</script>
