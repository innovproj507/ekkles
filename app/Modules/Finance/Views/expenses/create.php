<?php /** Vista: Registrar Egreso Financiero */ ?>
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/egresos" class="hover:text-white">Egresos</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-white">Registrar Egreso</span>
    </div>

    <form method="POST" action="/egresos/crear" class="space-y-6" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">

        <?php if (!empty($errors)): ?>
        <div class="bg-red-500/10 border border-red-500/20 rounded-2xl p-4">
            <?php foreach ($errors as $fe): foreach ($fe as $err): ?><p class="text-red-300 text-sm">• <?= e($err) ?></p><?php endforeach; endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Sección de Proveedor y Comprobante -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-4">
            <h2 class="text-base font-semibold text-white">Información del Proveedor e Impuestos</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- RUC / Cédula del Proveedor -->
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">RUC / Cédula del Proveedor</label>
                    <input type="text" name="provider_ruc" value="<?= e($old['provider_ruc'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                           placeholder="Ej: 1792348576001">
                </div>

                <!-- Nombre del Proveedor -->
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Nombre del Proveedor <span class="text-red-400">*</span></label>
                    <input type="text" name="provider_name" required value="<?= e($old['provider_name'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                           placeholder="Nombre o Razón Social">
                </div>

                <!-- Factura / Comprobante -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Nº de Factura <span class="text-red-400">*</span></label>
                    <input type="text" name="invoice_number" required value="<?= e($old['invoice_number'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                           placeholder="Ej: 001-002-000045612">
                </div>
            </div>
        </div>

        <!-- Detalles del Registro del Gasto -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-4">
            <h2 class="text-base font-semibold text-white">Detalles del Egreso</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Fecha -->
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Fecha del Gasto <span class="text-red-400">*</span></label>
                    <input type="date" name="transaction_date" required value="<?= e($old['transaction_date'] ?? date('Y-m-d')) ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
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

                <!-- Corresponde a -->
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Corresponde a <span class="text-red-400">*</span></label>
                    <select name="target_module" id="target-module-select" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Seleccione procedencia</option>
                        <option value="ofrendas" <?= ($old['target_module'] ?? '') === 'ofrendas' ? 'selected' : '' ?>>Ofrendas</option>
                        <option value="construccion" <?= ($old['target_module'] ?? '') === 'construccion' ? 'selected' : '' ?>>Construcción</option>
                        <option value="donaciones" <?= ($old['target_module'] ?? '') === 'donaciones' ? 'selected' : '' ?>>Obras Misioneras y Donaciones</option>
                    </select>
                </div>

                <!-- Categoría dinámica de Egreso -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-medium text-gray-400">Categoría <span class="text-red-400">*</span></label>
                        <button type="button" id="btn-new-category" class="text-xs text-brand-400 hover:text-brand-300 font-medium transition-colors focus:outline-none">+ Nueva Categoría</button>
                    </div>
                    <select name="category_id" id="category-select" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="" data-module="">Seleccione una categoría</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($old['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?> data-module="<?= e($cat['target_module'] ?? '') ?>">
                            <?= e($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Autorizado Por -->
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Autorizado Por <span class="text-red-400">*</span></label>
                    <select name="approved_by" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Seleccione quién autoriza</option>
                        <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($old['approved_by'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                            <?= e($u['name']) ?> (<?= ucfirst($u['role']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
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
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Referencia de Pago</label>
                    <input type="text" name="reference" value="<?= e($old['reference'] ?? '') ?>"
                           class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                           placeholder="# transferencia, cheque, etc.">
                </div>
            </div>

            <!-- Detalle / Concepto -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Detalle del Gasto <span class="text-red-400">*</span></label>
                <input type="text" name="description" required value="<?= e($old['description'] ?? '') ?>"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                       placeholder="Ej: Compra de insumos de limpieza para el templo">
            </div>

            <!-- Notas -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Notas Adicionales</label>
                <textarea name="notes" rows="3"
                          class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"
                          placeholder="Especificaciones o comentarios extras..."><?= e($old['notes'] ?? '') ?></textarea>
            </div>

            <!-- Recibo / comprobante -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Recibo o factura (PDF o imagen)</label>
                <input type="file" name="receipt" accept="application/pdf,image/jpeg,image/png,image/webp"
                       class="block w-full text-sm text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-brand-600 file:text-white hover:file:bg-brand-500 bg-gray-800 border border-gray-700 rounded-xl">
                <p class="text-xs text-gray-500 mt-1">Se guarda ordenado por la fecha del egreso (carpeta año/mes) para revisarlo luego por mes. Máximo 10MB.</p>
            </div>
        </div>

        <div class="flex items-center justify-between pb-6">
            <a href="/egresos" class="text-gray-400 hover:text-white text-sm font-medium transition-colors">← Cancelar</a>
            <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-red-600 to-brand-600 hover:from-red-500 hover:to-brand-500 text-white font-semibold py-3 px-8 rounded-xl text-sm transition-all shadow-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Registrar Egreso
            </button>
        </div>
    </form>
</div>

<!-- Modal para Crear Nueva Categoría de Egreso -->
<div id="modal-category" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden animate-fade-in">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 w-full max-w-sm mx-4 space-y-4 shadow-2xl relative">
        <h3 class="text-sm font-semibold text-white">Nueva Categoría de Egreso</h3>
        
        <!-- Alerta de Error -->
        <div id="category-error" class="bg-red-500/10 border border-red-500/20 rounded-xl p-3 hidden">
            <p id="category-error-text" class="text-red-300 text-xs"></p>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-400 mb-1.5">Nombre de la Categoría *</label>
            <input type="text" id="new-category-name" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-gray-500" placeholder="ej: Materiales de Oficina">
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
    const categorySelect = document.getElementById('category-select');
    const targetModuleSelect = document.getElementById('target-module-select');

    // Cargar y almacenar copia de todas las opciones originales
    let originalOptions = Array.from(categorySelect.options)
        .filter(opt => opt.value !== '')
        .map(opt => ({
            value: opt.value,
            text: opt.textContent,
            module: opt.getAttribute('data-module'),
            selected: opt.selected
        }));

    // Función de filtrado dinámico
    function filterCategories() {
        const selectedModule = targetModuleSelect.value;
        const previousValue = categorySelect.value;
        
        categorySelect.innerHTML = '';
        
        const defaultOpt = document.createElement('option');
        defaultOpt.value = '';
        defaultOpt.textContent = 'Seleccione una categoría';
        categorySelect.appendChild(defaultOpt);
        
        originalOptions.forEach(opt => {
            if (!selectedModule || opt.module === selectedModule) {
                const newOpt = document.createElement('option');
                newOpt.value = opt.value;
                newOpt.textContent = opt.text;
                newOpt.setAttribute('data-module', opt.module);
                
                // Mantener seleccionada la opción anterior si corresponde al nuevo módulo
                if (opt.value == previousValue) {
                    newOpt.selected = true;
                }
                categorySelect.appendChild(newOpt);
            }
        });
    }

    targetModuleSelect.addEventListener('change', filterCategories);
    filterCategories(); // Filtrar inicialmente

    // Modal de Nueva Categoría
    const btnNewCategory = document.getElementById('btn-new-category');
    const modalCategory = document.getElementById('modal-category');
    const btnCancelCategory = document.getElementById('btn-cancel-category');
    const btnSaveCategory = document.getElementById('btn-save-category');
    const newCategoryName = document.getElementById('new-category-name');
    const categoryError = document.getElementById('category-error');
    const categoryErrorText = document.getElementById('category-error-text');
    const categorySpinner = document.getElementById('category-spinner');

    if (btnNewCategory && modalCategory) {
        btnNewCategory.addEventListener('click', function() {
            // Validar que se haya escogido procedencia primero
            if (!targetModuleSelect.value) {
                alert('Por favor, seleccione primero en "Corresponde a" para saber a qué módulo asignar la nueva categoría.');
                targetModuleSelect.focus();
                return;
            }
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

            const selectedModule = targetModuleSelect.value;
            if (!selectedModule) {
                categoryErrorText.textContent = 'El módulo de destino es requerido.';
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
            formData.append('target_module', selectedModule);
            formData.append('_csrf_token', csrfToken);

            fetch('/egresos/categorias/crear', {
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
                    // Agregar a la lista original
                    originalOptions.push({
                        value: res.body.category.id,
                        text: res.body.category.name,
                        module: res.body.category.target_module
                    });

                    // Re-filtrar
                    filterCategories();
                    
                    // Seleccionar la nueva opción
                    categorySelect.value = res.body.category.id;

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
