<?php /** Vista: Registrar Grupo Familiar */ ?>
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-6">
        <a href="/familias" class="hover:text-white">Familias</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-white">Registrar Familia</span>
    </div>

    <form method="POST" action="/familias/crear" class="space-y-6" novalidate>
        <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">

        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-4">
            <h2 class="text-base font-semibold text-white">Datos del Grupo Familiar</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Nombre de Familia -->
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Nombre de Familia <span class="text-red-400">*</span></label>
                    <input type="text" name="name" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" placeholder="Ej: Familia Gómez Martínez">
                </div>

                <!-- Cabeza de Familia -->
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Cabeza de Familia</label>
                    <select name="head_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Seleccione al cabeza de hogar (opcional)</option>
                        <?php foreach ($members as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= e($m['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Dirección -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Dirección de Residencia</label>
                <input type="text" name="address" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" placeholder="Dirección completa">
            </div>

            <!-- Notas -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Notas / Observaciones</label>
                <textarea name="notes" rows="3" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none" placeholder="Observaciones adicionales sobre el grupo familiar..."></textarea>
            </div>
        </div>

        <!-- Integrantes de la Familia -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-white">Integrantes de la Familia</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Asocie otros miembros de la iglesia a esta familia</p>
                </div>
                <button type="button" id="add-member-btn" class="inline-flex items-center gap-1 bg-gray-800 hover:bg-gray-700 text-brand-400 font-medium py-1.5 px-3 rounded-lg text-xs transition-colors border border-gray-700">
                    ➕ Agregar Integrante
                </button>
            </div>

            <div id="members-list-container" class="space-y-3">
                <!-- Se inyectarán dinámicamente aquí -->
            </div>
        </div>

        <div class="flex items-center justify-between pb-6">
            <a href="/familias" class="text-gray-400 hover:text-white text-sm font-medium transition-colors">← Cancelar</a>
            <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-500 hover:to-purple-500 text-white font-semibold py-3 px-8 rounded-xl text-sm transition-all shadow-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Guardar Familia
            </button>
        </div>
    </form>
</div>

<!-- Plantilla para fila de integrante (oculta en el DOM) -->
<template id="member-row-template">
    <div class="flex flex-col sm:flex-row gap-3 p-4 bg-gray-950 border border-gray-800/80 rounded-xl relative group member-row">
        <!-- Integrante -->
        <div class="flex-1">
            <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Miembro</label>
            <select name="members[]" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
                <option value="">Seleccione un integrante</option>
                <?php foreach ($members as $m): ?>
                <option value="<?= $m['id'] ?>"><?= e($m['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Relación/Parentesco -->
        <div class="w-full sm:w-48">
            <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Parentesco / Rol</label>
            <select name="relationships[]" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500">
                <option value="conyuge">Cónyuge</option>
                <option value="hijo">Hijo / Hija</option>
                <option value="padre">Padre / Madre</option>
                <option value="hermano">Hermano / Hermana</option>
                <option value="abuelo">Abuelo / Abuela</option>
                <option value="tio">Tío / Tía</option>
                <option value="primo">Primo / Prima</option>
                <option value="otro" selected>Otro</option>
            </select>
        </div>

        <!-- Botón de remover -->
        <div class="flex items-end pb-0.5">
            <button type="button" class="remove-row-btn p-2 text-gray-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all" title="Eliminar Integrante">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addBtn = document.getElementById('add-member-btn');
    const container = document.getElementById('members-list-container');
    const template = document.getElementById('member-row-template');

    // Agregar una fila
    addBtn.addEventListener('click', function() {
        const clone = template.content.cloneNode(true);
        container.appendChild(clone);
    });

    // Delegación de eventos para remover fila
    container.addEventListener('click', function(e) {
        const removeBtn = e.target.closest('.remove-row-btn');
        if (removeBtn) {
            const row = removeBtn.closest('.member-row');
            if (row) {
                row.remove();
            }
        }
    });

    // Agregar primera fila por defecto
    addBtn.click();
});
</script>
