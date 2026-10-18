<?php /** Vista: Registrar Donación, Ofrenda o Construcción */ ?>
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="<?= e($basePath) ?>" class="hover:text-white"><?= e($title) ?></a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-white">Registrar <?= e($title === 'Ofrendas' ? 'Ofrenda' : ($title === 'Construcción' ? 'Construcción' : 'Donación')) ?></span>
    </div>

    <form method="POST" action="<?= e($basePath . '/crear') ?>" class="space-y-6" novalidate>
        <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">

        <?php if (!empty($errors)): ?>
        <div class="bg-red-500/10 border border-red-500/20 rounded-2xl p-4">
            <?php foreach ($errors as $fe): foreach ($fe as $err): ?><p class="text-red-300 text-sm">• <?= e($err) ?></p><?php endforeach; endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-4">
            <h2 class="text-base font-semibold text-white">Información del Donante</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Miembro o visitante -->
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Miembro de la Iglesia</label>
                    <select name="member_id" id="member-select"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="" data-document="">Seleccionar miembro (opcional)</option>
                        <?php foreach ($members as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= ($old['member_id'] ?? '') == $m['id'] ? 'selected' : '' ?> data-document="<?= e($m['document_number'] ?? '') ?>">
                            <?= e($m['full_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- RUC / Identificación -->
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">RUC / Documento</label>
                    <input type="text" name="ruc" id="ruc" value="<?= e($old['ruc'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                           placeholder="RUC, Cédula o ID Tributaria">
                </div>
            </div>

            <div id="donor-name-wrap">
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Nombre del Donante (si no es miembro)</label>
                <input type="text" name="donor_name" value="<?= e($old['donor_name'] ?? '') ?>"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                       placeholder="Nombre completo">
            </div>

            <div class="flex items-center gap-3 pt-2">
                <input type="checkbox" id="is_anonymous" name="is_anonymous" value="1"
                       <?= !empty($old['is_anonymous']) ? 'checked' : '' ?>
                       class="w-4 h-4 text-brand-600 bg-gray-800 border-gray-700 rounded focus:ring-brand-500">
                <label for="is_anonymous" class="text-sm text-gray-300">Registro Anónimo</label>
            </div>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-4">
            <h2 class="text-base font-semibold text-white">Detalles del Registro</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Fecha -->
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Fecha <span class="text-red-400">*</span></label>
                    <input type="date" name="donation_date" required value="<?= e($old['donation_date'] ?? date('Y-m-d')) ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <!-- Recibo (Manual/Opcional) -->
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Nº de Recibo (Opcional)</label>
                    <input type="text" name="receipt_number" value="<?= e($old['receipt_number'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                           placeholder="Dejar en blanco para autogenerar">
                </div>

                <!-- Categoría dinámica -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-medium text-gray-400">Categoría <span class="text-red-400">*</span></label>
                        <span class="flex items-center gap-3">
                            <?php if ($auth_can('donations.edit')): ?>
                            <a href="<?= e($basePath) ?>/categorias" class="text-xs text-gray-400 hover:text-white">Clasificar</a>
                            <?php endif; ?>
                            <button type="button" id="btn-new-category" class="text-xs text-brand-400 hover:text-brand-300 font-medium transition-colors focus:outline-none">+ Nueva Categoría</button>
                        </span>
                    </div>
                    <select name="category_id" id="category-select" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Seleccione una categoría</option>
                        <?php
                        // Categorías agrupadas por su clasificación (las sin grupo al final)
                        $grouped = [];
                        foreach ($categories as $cat) {
                            $grouped[$cat['group_name'] ?: 'Sin grupo'][] = $cat;
                        }
                        foreach ($grouped as $groupName => $groupCats): ?>
                        <optgroup label="<?= e($groupName) ?>">
                            <?php foreach ($groupCats as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($old['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
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
                           placeholder="# transacción, cheque...">
                </div>
            </div>

            <!-- Detalle / Concepto -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Detalle / Concepto <span class="text-red-400">*</span></label>
                <input type="text" name="detail" required value="<?= e($old['detail'] ?? '') ?>"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                       placeholder="Ej: Ofrenda correspondiente al mes de mayo">
            </div>

            <!-- Notas adicionales -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Notas Adicionales</label>
                <textarea name="notes" rows="2"
                          class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"
                          placeholder="Observaciones..."><?= e($old['notes'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="flex items-center justify-between pb-6">
            <a href="<?= e($basePath) ?>" class="text-gray-400 hover:text-white text-sm font-medium transition-colors">← Cancelar</a>
            <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-brand-600 hover:from-emerald-500 hover:to-brand-500 text-white font-semibold py-3 px-8 rounded-xl text-sm transition-all shadow-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Registrar <?= e($title === 'Ofrendas' ? 'Ofrenda' : ($title === 'Construcción' ? 'Construcción' : 'Donación')) ?>
            </button>
        </div>
    </form>
</div>

<!-- Modal para Crear Nueva Categoría -->
<div id="modal-category" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden animate-fade-in">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 w-full max-w-sm mx-4 space-y-4 shadow-2xl relative">
        <h3 class="text-sm font-semibold text-white">Nueva Categoría de Ingresos</h3>
        
        <!-- Alerta de Error -->
        <div id="category-error" class="bg-red-500/10 border border-red-500/20 rounded-xl p-3 hidden">
            <p id="category-error-text" class="text-red-300 text-xs"></p>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Nombre de la Categoría *</label>
            <input type="text" id="new-category-name" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-gray-500" placeholder="ej: Proyecto de Navidad">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Grupo (opcional)</label>
            <input type="text" id="new-category-group" list="category-groups" maxlength="80" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-gray-500" placeholder="ej: Aportes y ofrendas">
            <datalist id="category-groups">
                <?php foreach ($groups ?? [] as $g): ?><option value="<?= e($g) ?>"><?php endforeach; ?>
            </datalist>
        </div>
        <div class="flex items-center justify-between pt-2">
            <button type="button" id="btn-cancel-category" class="text-xs text-gray-400 hover:text-white transition-colors">Cancelar</button>
            <button type="button" id="btn-save-category" class="bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold py-2.5 px-5 rounded-xl transition-colors flex items-center justify-center gap-1.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                <svg id="category-spinner" class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-white hidden" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Guardar
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const memberSelect = document.getElementById('member-select');
    const rucInput = document.getElementById('ruc');
    const donorNameWrap = document.getElementById('donor-name-wrap');

    // Auto-completar RUC / Documento al cambiar de miembro
    if (memberSelect) {
        memberSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const docNumber = selectedOption.getAttribute('data-document');
            
            if (docNumber) {
                rucInput.value = docNumber;
            } else if (this.value === '') {
                rucInput.value = '';
            }
            
            // Desvanecer nombre del donante si es miembro
            donorNameWrap.style.opacity = this.value ? '0.4' : '1';
        });

        // Inicializar estado de opacidad
        if (memberSelect.value) {
            donorNameWrap.style.opacity = '0.4';
        }
    }

    // Modal de Nueva Categoría
    const btnNewCategory = document.getElementById('btn-new-category');
    const modalCategory = document.getElementById('modal-category');
    const btnCancelCategory = document.getElementById('btn-cancel-category');
    const btnSaveCategory = document.getElementById('btn-save-category');
    const newCategoryName = document.getElementById('new-category-name');
    const categorySelect = document.getElementById('category-select');
    const categoryError = document.getElementById('category-error');
    const categoryErrorText = document.getElementById('category-error-text');
    const categorySpinner = document.getElementById('category-spinner');

    if (btnNewCategory && modalCategory) {
        btnNewCategory.addEventListener('click', function() {
            modalCategory.classList.remove('hidden');
            newCategoryName.value = '';
            categoryError.classList.add('hidden');
            newCategoryName.focus();
        });

        btnCancelCategory.addEventListener('click', function() {
            modalCategory.classList.add('hidden');
        });

        // Cerrar modal al hacer click fuera de él
        modalCategory.addEventListener('click', function(e) {
            if (e.target === modalCategory) {
                modalCategory.classList.add('hidden');
            }
        });

        btnSaveCategory.addEventListener('click', function() {
            const name = newCategoryName.value.trim();
            if (!name) {
                categoryErrorText.textContent = 'El nombre de la categoría es requerido.';
                categoryError.classList.remove('hidden');
                return;
            }

            // Mostrar spinner y deshabilitar botón
            categorySpinner.classList.remove('hidden');
            btnSaveCategory.disabled = true;
            categoryError.classList.add('hidden');

            const csrfToken = document.querySelector('input[name="_csrf_token"]').value;

            // Form data
            const formData = new FormData();
            formData.append('name', name);
            formData.append('group_name', document.getElementById('new-category-group').value.trim());
            formData.append('module', <?= json_encode(ltrim($basePath ?? '/donaciones', '/')) ?>);
            formData.append('_csrf_token', csrfToken);

            fetch('/donaciones/categorias/crear', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(res => {
                categorySpinner.classList.add('hidden');
                btnSaveCategory.disabled = false;

                if (res.status === 200 && res.body.success) {
                    // Agregar la nueva opción
                    const opt = document.createElement('option');
                    opt.value = res.body.category.id;
                    opt.textContent = res.body.category.name;
                    opt.selected = true;
                    // Ubicar la opción dentro de su grupo (o crear el grupo)
                    const groupLabel = res.body.category.group_name || 'Sin grupo';
                    let group = Array.from(categorySelect.querySelectorAll('optgroup')).find(g => g.label === groupLabel);
                    if (!group) {
                        group = document.createElement('optgroup');
                        group.label = groupLabel;
                        categorySelect.appendChild(group);
                    }
                    group.appendChild(opt);

                    // Ocultar modal
                    modalCategory.classList.add('hidden');
                } else {
                    categoryErrorText.textContent = res.body.error || 'Ocurrió un error inesperado.';
                    categoryError.classList.remove('hidden');
                }
            })
            .catch(err => {
                categorySpinner.classList.add('hidden');
                btnSaveCategory.disabled = false;
                categoryErrorText.textContent = 'Error de conexión con el servidor.';
                categoryError.classList.remove('hidden');
            });
        });
    }
});
</script>
