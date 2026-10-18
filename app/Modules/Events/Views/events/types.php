<?php
/**
 * Vista: Tipos de Eventos
 */
?>
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white mb-1">Tipos de Eventos</h1>
            <p class="text-sm text-gray-400">Gestiona las categorías o tipos de eventos que puedes crear en tu iglesia.</p>
        </div>
        <div class="flex gap-3">
            <a href="/eventos" class="bg-gray-800 hover:bg-gray-700 text-white font-medium py-2.5 px-4 rounded-xl text-sm transition-colors">Volver a Eventos</a>
            <button type="button" onclick="openModal('modal-create')" class="bg-brand-600 hover:bg-brand-500 text-white font-medium py-2.5 px-4 rounded-xl text-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Nuevo Tipo
            </button>
        </div>
    </div>

    <!-- Alertas Flash -->
    <?php $err = \App\Core\Session::getFlash('error'); if ($err): ?>
        <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm p-4 rounded-xl mb-6"><?= e($err) ?></div>
    <?php endif; ?>
    <?php $suc = \App\Core\Session::getFlash('success'); if ($suc): ?>
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm p-4 rounded-xl mb-6"><?= e($suc) ?></div>
    <?php endif; ?>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-800 bg-gray-800/50">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Nombre</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Color</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Estado</th>
                    <th class="text-right px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                <?php if (empty($types)): ?>
                <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500 text-sm">No hay tipos de eventos registrados.</td></tr>
                <?php else: ?>
                <?php foreach ($types as $type): ?>
                <tr class="hover:bg-gray-800/30 transition-colors">
                    <td class="px-6 py-4">
                        <p class="text-sm text-white font-medium"><?= e($type['name']) ?></p>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full" style="background-color: <?= e($type['color'] ?? '#4f46e5') ?>"></span>
                            <span class="text-xs text-gray-400"><?= e($type['color'] ?? '#4f46e5') ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <?php if ($type['is_active']): ?>
                            <span class="inline-flex items-center gap-1 text-xs px-2.5 py-0.5 rounded-full font-medium bg-emerald-500/10 text-emerald-400">Activo</span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1 text-xs px-2.5 py-0.5 rounded-full font-medium bg-red-500/10 text-red-400">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <button type="button" onclick="editType(<?= htmlspecialchars(json_encode($type)) ?>)" class="text-gray-400 hover:text-white transition-colors">Editar</button>
                            
                            <form action="/eventos/tipos/<?= $type['id'] ?>/eliminar" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este tipo de evento?')" class="inline">
                                <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">
                                <button type="submit" class="text-gray-400 hover:text-red-400 transition-colors">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Crear -->
<div id="modal-create" class="fixed inset-0 bg-gray-950/80 backdrop-blur-sm z-50 hidden opacity-0 transition-opacity duration-300">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20">
        <div class="bg-gray-900 border border-gray-800 rounded-3xl w-full max-w-md p-8 shadow-2xl transform scale-95 transition-transform duration-300" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-white">Nuevo Tipo de Evento</h3>
                <button type="button" onclick="closeModal('modal-create')" class="text-gray-400 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form action="/eventos/tipos" method="POST" class="space-y-4">
                <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Nombre <span class="text-red-400">*</span></label>
                    <input type="text" name="name" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Color Identificador</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="color" value="#4f46e5" class="w-12 h-10 rounded bg-transparent cursor-pointer border-0 p-0">
                        <span class="text-xs text-gray-500">Para mostrar en el calendario</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_active" id="create_active" value="1" checked class="w-4 h-4 rounded border-gray-700 bg-gray-800 text-brand-600 focus:ring-brand-500 focus:ring-offset-gray-900">
                    <label for="create_active" class="text-sm text-gray-300 cursor-pointer">Tipo Activo (Visible en formularios)</label>
                </div>
                <div class="pt-6 border-t border-gray-800 flex justify-end gap-3">
                    <button type="button" onclick="closeModal('modal-create')" class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-400 hover:text-white transition-colors">Cancelar</button>
                    <button type="submit" class="bg-brand-600 hover:bg-brand-500 text-white font-medium px-5 py-2.5 rounded-xl text-sm transition-colors">Crear Tipo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div id="modal-edit" class="fixed inset-0 bg-gray-950/80 backdrop-blur-sm z-50 hidden opacity-0 transition-opacity duration-300">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20">
        <div class="bg-gray-900 border border-gray-800 rounded-3xl w-full max-w-md p-8 shadow-2xl transform scale-95 transition-transform duration-300" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-white">Editar Tipo de Evento</h3>
                <button type="button" onclick="closeModal('modal-edit')" class="text-gray-400 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <form id="edit-form" method="POST" class="space-y-4">
                <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Nombre <span class="text-red-400">*</span></label>
                    <input type="text" name="name" id="edit-name" required class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Color Identificador</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="color" id="edit-color" class="w-12 h-10 rounded bg-transparent cursor-pointer border-0 p-0">
                    </div>
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_active" id="edit_active" value="1" class="w-4 h-4 rounded border-gray-700 bg-gray-800 text-brand-600 focus:ring-brand-500 focus:ring-offset-gray-900">
                    <label for="edit_active" class="text-sm text-gray-300 cursor-pointer">Activo</label>
                </div>
                <div class="pt-6 border-t border-gray-800 flex justify-end gap-3">
                    <button type="button" onclick="closeModal('modal-edit')" class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-400 hover:text-white transition-colors">Cancelar</button>
                    <button type="submit" class="bg-brand-600 hover:bg-brand-500 text-white font-medium px-5 py-2.5 rounded-xl text-sm transition-colors">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openModal(id) {
    const m = document.getElementById(id);
    m.classList.remove('hidden');
    setTimeout(() => {
        m.classList.remove('opacity-0');
        m.querySelector('div > div').classList.remove('scale-95');
    }, 10);
}

function closeModal(id) {
    const m = document.getElementById(id);
    m.classList.add('opacity-0');
    m.querySelector('div > div').classList.add('scale-95');
    setTimeout(() => { m.classList.add('hidden'); }, 300);
}

function editType(type) {
    document.getElementById('edit-form').action = '/eventos/tipos/' + type.id;
    document.getElementById('edit-name').value = type.name;
    document.getElementById('edit-color').value = type.color || '#4f46e5';
    document.getElementById('edit_active').checked = type.is_active == 1;
    openModal('modal-edit');
}
</script>
