<?php /** Vista: MFA */ ?>
<div class="bg-gray-900/80 backdrop-blur-xl border border-gray-800 rounded-2xl p-8 shadow-2xl text-center">
    <!-- Icono de escudo -->
    <div class="inline-flex items-center justify-center w-16 h-16 bg-brand-500/10 border border-brand-500/20 rounded-2xl mb-6">
        <svg class="w-8 h-8 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
    </div>

    <h2 class="text-xl font-bold text-white mb-2">Verificación en Dos Pasos</h2>
    <p class="text-gray-400 text-sm mb-6">
        Ingrese el código de 6 dígitos de su aplicación autenticadora (Google Authenticator, Authy, etc.)
    </p>

    <form method="POST" action="/mfa" class="space-y-5">
        <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">

        <!-- Inputs de código OTP -->
        <div class="flex justify-center gap-2" id="otp-inputs">
            <?php for ($i = 0; $i < 6; $i++): ?>
            <input type="text" maxlength="1" pattern="[0-9]"
                   class="w-12 h-12 text-center text-xl font-bold bg-gray-800 border border-gray-700 text-white rounded-xl
                          focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all"
                   oninput="moveToNext(this, <?= $i ?>)"
                   onkeydown="moveBack(event, this, <?= $i ?>)">
            <?php endfor; ?>
        </div>
        <input type="hidden" name="code" id="full-code">

        <button type="submit" onclick="combineCode()"
                class="w-full bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-500 hover:to-purple-500
                       text-white font-semibold py-3 px-6 rounded-xl text-sm transition-all duration-200
                       transform hover:scale-[1.02] shadow-lg shadow-brand-500/20">
            Verificar Código
        </button>

        <a href="/login" class="block text-sm text-gray-500 hover:text-gray-300 transition-colors">
            ← Volver al inicio de sesión
        </a>
    </form>
</div>

<script>
function moveToNext(input, index) {
    input.value = input.value.replace(/[^0-9]/g, '');
    if (input.value && index < 5) {
        document.querySelectorAll('#otp-inputs input')[index + 1].focus();
    }
}
function moveBack(e, input, index) {
    if (e.key === 'Backspace' && !input.value && index > 0) {
        document.querySelectorAll('#otp-inputs input')[index - 1].focus();
    }
}
function combineCode() {
    const inputs = document.querySelectorAll('#otp-inputs input');
    document.getElementById('full-code').value = Array.from(inputs).map(i => i.value).join('');
}
// Pegar código completo
document.querySelectorAll('#otp-inputs input')[0].addEventListener('paste', function(e) {
    e.preventDefault();
    const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').substring(0, 6);
    const inputs = document.querySelectorAll('#otp-inputs input');
    text.split('').forEach((char, i) => { if (inputs[i]) inputs[i].value = char; });
    if (inputs[text.length - 1]) inputs[text.length - 1].focus();
});
</script>
