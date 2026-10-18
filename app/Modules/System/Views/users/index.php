<?php /** Vista: Usuarios del Sistema */ ?>
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-white"><?= e($title) ?></h2>
            <p class="text-gray-400 text-sm mt-1">Administra los usuarios con acceso al sistema y sus roles.</p>
        </div>
        <?php if ($auth_can('users.create')): ?>
        <a href="/usuarios/crear" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-500 text-white font-medium py-2 px-4 rounded-xl transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nuevo Usuario
        </a>
        <?php endif; ?>
    </div>

    <!-- Lista -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden shadow-sm">
        <?php if (empty($users)): ?>
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <h3 class="text-lg font-medium text-white mb-2">No hay usuarios</h3>
                <p class="text-gray-400">Aún no hay usuarios registrados en el sistema.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-800/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-800">
                            <th class="px-6 py-3 font-medium">Usuario</th>
                            <th class="px-6 py-3 font-medium">Rol</th>
                            <th class="px-6 py-3 font-medium">Estado</th>
                            <th class="px-6 py-3 font-medium">Conexión</th>
                            <th class="px-6 py-3 font-medium">Último Acceso</th>
                            <th class="px-6 py-3 font-medium text-center">Sesión</th>
                            <th class="px-6 py-3 font-medium text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-800">
                        <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-gray-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-brand-500/20 text-brand-400 flex items-center justify-center font-bold text-lg flex-shrink-0">
                                        <?= strtoupper(substr($u['name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="text-white font-medium"><?= e($u['name']) ?></p>
                                        <p class="text-xs text-gray-400 mt-0.5"><?= e($u['email']) ?></p>
                                        <?php if ($u['phone']): ?>
                                            <p class="text-xs text-gray-500"><?= e($u['phone']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-800 text-gray-300 border border-gray-700 capitalize">
                                    <?= e($u['role']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($u['is_active']): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-500/10 text-green-400 border border-green-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span> Activo
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span> Inactivo
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                $statusColor = 'bg-red-500 shadow-red-500/50';
                                $statusText = 'Offline';
                                if (!empty($u['last_activity_at'])) {
                                    $diff = time() - strtotime($u['last_activity_at']);
                                    $minutes = floor($diff / 60);
                                    if ($minutes < 2) {
                                        $statusColor = 'bg-green-500 shadow-green-500/50';
                                        $statusText = 'Online';
                                    } elseif ($minutes <= 15) {
                                        $statusColor = 'bg-yellow-500 shadow-yellow-500/50';
                                        $statusText = 'Ausente';
                                    }
                                }
                                ?>
                                <div class="flex items-center gap-2" title="<?= $statusText ?>" id="status-container-<?= $u['id'] ?>">
                                    <div class="relative flex h-3 w-3" id="status-indicator-<?= $u['id'] ?>">
                                      <?php if ($statusText === 'Online'): ?>
                                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 <?= $statusColor ?>"></span>
                                      <?php endif; ?>
                                      <span class="relative inline-flex rounded-full h-3 w-3 shadow-lg <?= $statusColor ?>"></span>
                                    </div>
                                    <span class="text-xs text-gray-400" id="status-text-<?= $u['id'] ?>"><?= $statusText ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-400">
                                <?= $u['last_login_at'] ? date('d M Y, h:i A', strtotime($u['last_login_at'])) : 'Nunca' ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($u['id'] !== \App\Core\Auth::id()): ?>
                                <label class="relative inline-flex items-center cursor-pointer group" title="Forzar Cierre de Sesión">
                                  <input type="checkbox" class="sr-only peer" onchange="forceLogout(<?= $u['id'] ?>, this.checked ? 1 : 0)" <?= $u['force_logout'] ? 'checked' : '' ?>>
                                  <div class="w-9 h-5 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-500 group-hover:bg-gray-600 transition-colors"></div>
                                </label>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <?php if ($u['id'] !== \App\Core\Auth::id()): ?>
                                    <button type="button" onclick="openChatModal(<?= $u['id'] ?>, '<?= e($u['name']) ?>')" class="text-gray-400 hover:text-brand-400 transition-colors" title="Enviar Mensaje">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($auth_can('users.edit')): ?>
                                    <a href="/usuarios/<?= $u['id'] ?>/editar" class="text-brand-400 hover:text-brand-300 text-sm font-medium transition-colors">Editar</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para Enviar Mensaje -->
<div id="chat-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 transition-opacity opacity-0">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-md shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300" id="chat-modal-content">
        <div class="px-6 py-4 border-b border-gray-800 flex justify-between items-center bg-gray-800/30">
            <h3 class="text-lg font-bold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                Mensaje a <span id="chat-user-name" class="text-brand-400"></span>
            </h3>
            <button type="button" onclick="closeChatModal()" class="text-gray-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6">
            <input type="hidden" id="chat-user-id">
            <textarea id="chat-message" rows="3" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 resize-none" placeholder="Escribe tu mensaje aquí..."></textarea>
            
            <div class="mt-4 flex justify-end gap-3">
                <button type="button" onclick="closeChatModal()" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-xl text-sm font-medium transition-colors">
                    Cancelar
                </button>
                <button type="button" id="btn-send-chat" onclick="sendMessage()" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white rounded-xl text-sm font-medium transition-colors shadow-lg shadow-brand-500/20 flex items-center gap-2">
                    <span>Enviar</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function forceLogout(userId, force) {
    fetch('/api/users/force-logout', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `id=${userId}&force=${force}&_csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>`
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            Swal.fire({
                toast: true,
                position: 'bottom-end',
                icon: 'success',
                title: force ? 'Cierre de sesión forzado programado.' : 'Cierre forzado cancelado.',
                showConfirmButton: false,
                timer: 3000,
                background: '#1f2937',
                color: '#fff'
            });
        }
    })
    .catch(err => console.error('Error in forceLogout:', err));
}

function openChatModal(userId, userName) {
    document.getElementById('chat-user-id').value = userId;
    document.getElementById('chat-user-name').textContent = userName;
    document.getElementById('chat-message').value = '';
    
    const modal = document.getElementById('chat-modal');
    const content = document.getElementById('chat-modal-content');
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Trigger reflow
    void modal.offsetWidth;
    
    modal.classList.remove('opacity-0');
    content.classList.remove('scale-95');
    
    setTimeout(() => {
        document.getElementById('chat-message').focus();
    }, 300);
}

function closeChatModal() {
    const modal = document.getElementById('chat-modal');
    const content = document.getElementById('chat-modal-content');
    
    modal.classList.add('opacity-0');
    content.classList.add('scale-95');
    
    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 300);
}

function sendMessage() {
    const userId = document.getElementById('chat-user-id').value;
    const msg = document.getElementById('chat-message').value;
    const btn = document.getElementById('btn-send-chat');
    
    if(!msg.trim()) return;
    
    btn.disabled = true;
    btn.innerHTML = 'Enviando...';
    
    fetch('/api/users/send-message', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `user_id=${userId}&message=${encodeURIComponent(msg)}&_csrf_token=<?= $_SESSION['csrf_token'] ?? '' ?>`
    })
    .then(async r => {
        const text = await r.text();
        try {
            return JSON.parse(text);
        } catch(e) {
            console.error("JSON Parse Error. Server response:", text);
            throw new Error(text);
        }
    })
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = `<span>Enviar</span><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>`;
        
        if(data.success) {
            closeChatModal();
            Swal.fire({
                toast: true,
                position: 'bottom-end',
                icon: 'success',
                title: 'Mensaje enviado correctamente.',
                showConfirmButton: false,
                timer: 3000,
                background: '#1f2937',
                color: '#fff'
            });
        } else {
            alert(data.error || 'Error al enviar');
        }
    })
    .catch(err => {
        console.error('Error in sendMessage:', err);
        btn.disabled = false;
        btn.innerHTML = `<span>Enviar</span><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>`;
        alert('Error: ' + err.message.substring(0, 500));
    });
}

// Polling for user status updates
setInterval(() => {
    fetch('/api/users/active-status', {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if(data && data.statuses) {
            for(const [userId, status] of Object.entries(data.statuses)) {
                const container = document.getElementById('status-container-' + userId);
                const indicator = document.getElementById('status-indicator-' + userId);
                const textElem = document.getElementById('status-text-' + userId);
                
                if(container && indicator && textElem) {
                    container.title = status.text;
                    textElem.textContent = status.text;
                    
                    let html = '';
                    if(status.text === 'Online') {
                        html += `<span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 ${status.color}"></span>`;
                    }
                    html += `<span class="relative inline-flex rounded-full h-3 w-3 shadow-lg ${status.color}"></span>`;
                    indicator.innerHTML = html;
                }
            }
        }
    })
    .catch(err => console.error('Error polling statuses:', err));
}, 15000);
</script>
