<?php /** Vista: Nueva Conciliación Bancaria */ ?>
<div class="max-w-4xl mx-auto">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/conciliaciones" class="hover:text-white">Conciliaciones</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-white">Nueva Conciliación</span>
    </div>

    <form method="POST" action="/conciliaciones/crear" id="recon-form" class="space-y-6">
        <input type="hidden" name="_csrf_token" id="csrf_token" value="<?= e($csrf_token) ?>">
        <input type="hidden" name="action" id="action-type" value="draft">

        <?php if (!empty($errors)): ?>
        <div class="bg-red-500/10 border border-red-500/20 rounded-2xl p-4">
            <?php foreach ($errors as $fe): foreach ($fe as $err): ?><p class="text-red-300 text-sm">• <?= e($err) ?></p><?php endforeach; endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Paso 1: Configuración -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-4">
            <h2 class="text-base font-semibold text-white mb-2">1. Definir Periodo y Cuenta</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Cuenta Bancaria <span class="text-red-400">*</span></label>
                    <select name="account_name" id="account_name" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Seleccione...</option>
                        <option value="ofrendas" <?= ($old['account_name'] ?? '') === 'ofrendas' ? 'selected' : '' ?>>Ofrendas</option>
                        <option value="construccion" <?= ($old['account_name'] ?? '') === 'construccion' ? 'selected' : '' ?>>Construcción</option>
                        <option value="misiones" <?= ($old['account_name'] ?? '') === 'misiones' ? 'selected' : '' ?>>Obras Misioneras y Donaciones</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Fecha Inicio <span class="text-red-400">*</span></label>
                    <input type="date" name="period_start" id="period_start" required value="<?= e($old['period_start'] ?? date('Y-m-01')) ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Fecha Fin <span class="text-red-400">*</span></label>
                    <input type="date" name="period_end" id="period_end" required value="<?= e($old['period_end'] ?? date('Y-m-t')) ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>

            <div class="pt-2">
                <button type="button" id="btn-calculate" class="bg-gray-800 hover:bg-gray-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Calcular Saldos del Sistema
                </button>
            </div>
        </div>

        <!-- Paso 2: Resultados e Ingreso Real -->
        <div id="results-section" class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-6 <?= empty($old['starting_balance']) ? 'opacity-50 pointer-events-none' : '' ?>">
            <h2 class="text-base font-semibold text-white mb-2">2. Resultados y Cuadre Bancario</h2>

            <!-- Campos Ocultos para enviar al backend -->
            <input type="hidden" name="starting_balance" id="hidden_starting" value="<?= e($old['starting_balance'] ?? '') ?>">
            <input type="hidden" name="total_income" id="hidden_income" value="<?= e($old['total_income'] ?? '') ?>">
            <input type="hidden" name="total_expense" id="hidden_expense" value="<?= e($old['total_expense'] ?? '') ?>">
            <input type="hidden" name="expected_balance" id="hidden_expected" value="<?= e($old['expected_balance'] ?? '') ?>">

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-gray-800 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 mb-1">Saldo Inicial</p>
                    <p class="text-lg font-bold text-gray-300" id="lbl-starting">$<?= number_format((float)($old['starting_balance'] ?? 0), 2) ?></p>
                </div>
                <div class="bg-emerald-500/5 rounded-xl p-4 text-center border border-emerald-500/10">
                    <p class="text-xs text-gray-500 mb-1">Ingresos</p>
                    <p class="text-lg font-bold text-emerald-400" id="lbl-income">+$<?= number_format((float)($old['total_income'] ?? 0), 2) ?></p>
                </div>
                <div class="bg-red-500/5 rounded-xl p-4 text-center border border-red-500/10">
                    <p class="text-xs text-gray-500 mb-1">Egresos</p>
                    <p class="text-lg font-bold text-red-400" id="lbl-expense">-$<?= number_format((float)($old['total_expense'] ?? 0), 2) ?></p>
                </div>
                <div class="bg-brand-500/10 rounded-xl p-4 text-center border border-brand-500/20">
                    <p class="text-xs text-brand-300/80 mb-1">Saldo Esperado</p>
                    <p class="text-xl font-black text-white" id="lbl-expected">$<?= number_format((float)($old['expected_balance'] ?? 0), 2) ?></p>
                </div>
            </div>

            <hr class="border-gray-800">

            <div class="flex flex-col sm:flex-row items-center gap-6 bg-gray-800/50 p-6 rounded-2xl border border-gray-800">
                <div class="flex-1 w-full">
                    <label class="block text-sm font-semibold text-white mb-2">Saldo Real en el Banco (Estado de Cuenta) <span class="text-red-400">*</span></label>
                    <div class="relative max-w-xs">
                        <span class="absolute inset-y-0 left-4 flex items-center text-gray-500 text-lg font-bold">$</span>
                        <input type="number" name="actual_balance" id="actual_balance" required min="0" step="0.01" value="<?= e($old['actual_balance'] ?? '') ?>"
                               class="w-full bg-gray-900 border-2 border-gray-700 text-white rounded-xl pl-10 pr-4 py-3 text-lg font-bold focus:outline-none focus:border-brand-500 transition-colors"
                               placeholder="0.00">
                    </div>
                </div>

                <div class="flex-1 w-full text-center sm:text-right">
                    <p class="text-xs text-gray-400 mb-1 uppercase tracking-wider">Diferencia Matemática</p>
                    <p class="text-3xl font-black" id="lbl-difference">$0.00</p>
                    <p class="text-xs text-gray-500 mt-2" id="lbl-msg">La diferencia debe ser $0 para poder cerrar.</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Notas (Opcional)</label>
                <textarea name="notes" rows="2" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"><?= e($old['notes'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="flex items-center justify-between pb-8">
            <a href="/conciliaciones" class="text-gray-400 hover:text-white text-sm transition-colors">Cancelar</a>
            <div class="flex gap-3">
                <button type="button" onclick="submitForm('draft')" class="bg-gray-800 hover:bg-gray-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-colors">
                    Guardar Borrador
                </button>
                <button type="button" id="btn-close" onclick="submitForm('close')" class="bg-brand-600 hover:bg-brand-500 text-white px-6 py-2.5 rounded-xl text-sm font-semibold shadow-lg shadow-brand-500/20 transition-colors hidden">
                    Cerrar Conciliación
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnCalc = document.getElementById('btn-calculate');
    const accSelect = document.getElementById('account_name');
    const dStart = document.getElementById('period_start');
    const dEnd = document.getElementById('period_end');
    const csrfToken = document.getElementById('csrf_token').value;
    
    const resultsSection = document.getElementById('results-section');
    const hStart = document.getElementById('hidden_starting');
    const hInc = document.getElementById('hidden_income');
    const hExp = document.getElementById('hidden_expense');
    const hExpc = document.getElementById('hidden_expected');
    
    const lStart = document.getElementById('lbl-starting');
    const lInc = document.getElementById('lbl-income');
    const lExp = document.getElementById('lbl-expense');
    const lExpc = document.getElementById('lbl-expected');
    
    const actualBal = document.getElementById('actual_balance');
    const lDiff = document.getElementById('lbl-difference');
    const lMsg = document.getElementById('lbl-msg');
    const btnClose = document.getElementById('btn-close');

    // Number format helper
    const formatMoney = (n) => new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);

    // Calcular en BD
    btnCalc.addEventListener('click', function() {
        if (!accSelect.value || !dStart.value || !dEnd.value) {
            alert('Completa la Cuenta y Fechas primero.');
            return;
        }

        const formData = new FormData();
        formData.append('account_name', accSelect.value);
        formData.append('period_start', dStart.value);
        formData.append('period_end', dEnd.value);
        formData.append('_csrf_token', csrfToken);

        btnCalc.disabled = true;
        btnCalc.innerHTML = 'Calculando...';

        fetch('/conciliaciones/calcular', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            btnCalc.disabled = false;
            btnCalc.innerHTML = 'Calcular Saldos del Sistema';

            if (data.success) {
                // Rellenar ocultos
                hStart.value = data.starting_balance;
                hInc.value = data.total_income;
                hExp.value = data.total_expense;
                hExpc.value = data.expected_balance;

                // Actualizar labels
                lStart.textContent = '$' + formatMoney(data.starting_balance);
                lInc.textContent = '+$' + formatMoney(data.total_income);
                lExp.textContent = '-$' + formatMoney(data.total_expense);
                lExpc.textContent = '$' + formatMoney(data.expected_balance);

                // Activar seccion
                resultsSection.classList.remove('opacity-50', 'pointer-events-none');
                
                // Recalcular diff si hay valor en actual
                recalcDiff();
                
                // Hacer scroll
                resultsSection.scrollIntoView({ behavior: 'smooth' });
            } else {
                alert(data.error);
            }
        })
        .catch(err => {
            btnCalc.disabled = false;
            btnCalc.innerHTML = 'Calcular Saldos del Sistema';
            alert('Error de conexión.');
        });
    });

    // Validar diferencia en tiempo real
    function recalcDiff() {
        const expected = parseFloat(hExpc.value || 0);
        const actual = parseFloat(actualBal.value || 0);
        const diff = expected - actual;
        
        lDiff.textContent = '$' + formatMoney(diff);
        
        if (Math.abs(diff) < 0.01 && actualBal.value !== '') {
            lDiff.className = 'text-3xl font-black text-emerald-400';
            lMsg.textContent = '¡Cuadre Perfecto!';
            lMsg.className = 'text-xs text-emerald-500 mt-2 font-medium';
            btnClose.classList.remove('hidden');
        } else {
            lDiff.className = 'text-3xl font-black text-red-400';
            lMsg.textContent = diff > 0 ? 'Falta dinero en banco.' : 'Sobra dinero en banco.';
            lMsg.className = 'text-xs text-red-400 mt-2 font-medium';
            btnClose.classList.add('hidden');
        }
    }

    actualBal.addEventListener('input', recalcDiff);
    
    // Si la pagina cargó con error y ya habia datos
    if (hExpc.value !== '') {
        recalcDiff();
    }
});

function submitForm(action) {
    const actualBal = document.getElementById('actual_balance').value;
    if (actualBal === '') {
        alert('Ingresa el Saldo Real en Banco.');
        return;
    }
    document.getElementById('action-type').value = action;
    document.getElementById('recon-form').submit();
}
</script>
