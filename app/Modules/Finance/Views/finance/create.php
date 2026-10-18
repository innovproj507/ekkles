<?php /** Vista: Registrar Transacción Financiera */ ?>
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/finanzas" class="hover:text-white">Finanzas</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-white">Registrar Transacción</span>
    </div>

    <form method="POST" action="/finanzas/crear" class="space-y-6" novalidate>
        <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">

        <?php if (!empty($errors)): ?>
        <div class="bg-red-500/10 border border-red-500/20 rounded-2xl p-4">
            <?php foreach ($errors as $fe): foreach ($fe as $err): ?><p class="text-red-300 text-sm">• <?= e($err) ?></p><?php endforeach; endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-4">
            <h2 class="text-base font-semibold text-white">Detalles de la Transacción</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Tipo -->
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Tipo de Transacción <span class="text-red-400">*</span></label>
                    <select name="type" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="ingreso" <?= ($old['type'] ?? '') === 'ingreso' ? 'selected' : '' ?>>Ingreso (Entrada)</option>
                        <option value="egreso" <?= ($old['type'] ?? '') === 'egreso' ? 'selected' : '' ?>>Egreso (Salida)</option>
                    </select>
                </div>

                <!-- Categoría -->
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Categoría <span class="text-red-400">*</span></label>
                    <select name="category_id" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Seleccione una categoría</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($old['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?> data-type="<?= $cat['type'] ?>">
                            <?= e($cat['name']) ?> (<?= ucfirst($cat['type']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Monto -->
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Monto (USD) <span class="text-red-400">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-500 text-sm">$</span>
                        <input type="number" name="amount" required min="0.01" step="0.01" value="<?= e($old['amount'] ?? '') ?>"
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl pl-8 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                               placeholder="0.00">
                    </div>
                </div>

                <!-- Fecha -->
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Fecha de Transacción <span class="text-red-400">*</span></label>
                    <input type="date" name="transaction_date" required value="<?= e($old['transaction_date'] ?? date('Y-m-d')) ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <!-- Método de pago -->
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Método de Pago</label>
                    <select name="payment_method" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <?php foreach (['efectivo','transferencia','tarjeta','cheque','otro'] as $pm): ?>
                        <option value="<?= $pm ?>" <?= ($old['payment_method'] ?? 'efectivo') === $pm ? 'selected' : '' ?>><?= paymentMethodLabel($pm) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Referencia -->
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Referencia / Comprobante</label>
                    <input type="text" name="reference" value="<?= e($old['reference'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                           placeholder="# transferencia, cheque, etc.">
                </div>
            </div>

            <!-- Descripción -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Descripción / Concepto <span class="text-red-400">*</span></label>
                <input type="text" name="description" required value="<?= e($old['description'] ?? '') ?>"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                       placeholder="Ej: Pago de servicios de energía de la sede principal">
            </div>

            <!-- Notas -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Notas Adicionales</label>
                <textarea name="notes" rows="3"
                          class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"
                          placeholder="Detalles adicionales o aclaraciones..."><?= e($old['notes'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="flex items-center justify-between pb-6">
            <a href="/finanzas" class="text-gray-400 hover:text-white text-sm font-medium transition-colors">← Cancelar</a>
            <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-brand-600 hover:from-emerald-500 hover:to-brand-500 text-white font-semibold py-3 px-8 rounded-xl text-sm transition-all shadow-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Registrar Transacción
            </button>
        </div>
    </form>
</div>
