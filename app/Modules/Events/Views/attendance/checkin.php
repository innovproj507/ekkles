<?php /** Vista: Check-in de Asistencia */ ?>
<div class="max-w-2xl mx-auto">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="w-14 h-14 bg-brand-500/10 border border-brand-500/20 rounded-2xl flex flex-col items-center justify-center flex-shrink-0">
                <span class="text-lg font-bold text-brand-400"><?= date('d', strtotime($event['start_datetime'])) ?></span>
                <span class="text-xs text-brand-500"><?= date('M', strtotime($event['start_datetime'])) ?></span>
            </div>
            <div>
                <h1 class="text-xl font-bold text-white"><?= e($event['title']) ?></h1>
                <p class="text-gray-400 text-sm mt-1">
                    <?= formatDateTime($event['start_datetime']) ?>
                    <?php if ($event['location']): ?> · <?= e($event['location']) ?><?php endif; ?>
                </p>
                <div class="mt-2 flex items-center gap-3">
                    <span class="text-xs bg-brand-500/10 text-brand-400 px-2 py-0.5 rounded-full border border-brand-500/20">
                        <?= $count ?> asistentes registrados
                    </span>
                    <?php if ($event['max_capacity']): ?>
                    <span class="text-xs text-gray-500">de <?= $event['max_capacity'] ?> cupos</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de Check-in -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <h2 class="text-base font-semibold text-white mb-5">📋 Registrar Asistencia</h2>

        <form id="checkin-form" method="POST" action="/asistencia/registrar" class="space-y-4">
            <input type="hidden" name="_csrf_token" value="<?= e($csrf_token) ?>">
            <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
            <input type="hidden" name="via_qr" value="1">

            <!-- Selector de miembro -->
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Buscar Miembro</label>
                <select name="member_id" id="member-select"
                        class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">Seleccionar miembro...</option>
                    <?php foreach ($members as $m): ?>
                    <option value="<?= $m['id'] ?>"><?= e($m['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="relative flex items-center gap-3">
                <div class="flex-1 border-t border-gray-700"></div>
                <span class="text-xs text-gray-500 flex-shrink-0">o ingresar visitante</span>
                <div class="flex-1 border-t border-gray-700"></div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Nombre del Visitante</label>
                <input type="text" name="guest_name" id="guest-name"
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-gray-500"
                       placeholder="Nombre del visitante no registrado">
            </div>

            <button type="submit" id="btn-checkin"
                    class="w-full bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-500 hover:to-purple-500
                           text-white font-semibold py-3.5 px-6 rounded-xl text-sm transition-all
                           flex items-center justify-center gap-2 shadow-lg shadow-brand-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Registrar Asistencia
            </button>
        </form>

        <!-- Lista de asistentes en tiempo real -->
        <div id="checkin-list" class="mt-6 border-t border-gray-800 pt-4">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Últimos Registros</p>
            <div id="attendance-log" class="space-y-2">
                <p class="text-xs text-gray-600 italic">Los check-ins aparecerán aquí...</p>
            </div>
        </div>
    </div>
</div>

<script>
// Validar que se seleccione miembro O visitante
document.getElementById('checkin-form').addEventListener('submit', function(e) {
    const member = document.getElementById('member-select').value;
    const guest  = document.getElementById('guest-name').value.trim();

    if (!member && !guest) {
        e.preventDefault();
        alert('Seleccione un miembro o ingrese el nombre del visitante.');
        return;
    }

    // Feedback visual
    document.getElementById('btn-checkin').textContent = 'Registrando...';
    document.getElementById('btn-checkin').disabled = true;

    // Si se usa AJAX
    if (window.fetch) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('/asistencia/registrar', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            const log    = document.getElementById('attendance-log');
            const name   = document.getElementById('member-select').options[document.getElementById('member-select').selectedIndex]?.text || guest;
            const time   = new Date().toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });

            const item   = document.createElement('div');
            item.className = 'flex items-center gap-3 p-2.5 bg-' + (data.success ? 'emerald' : 'red') + '-500/10 border border-' + (data.success ? 'emerald' : 'red') + '-500/20 rounded-xl animate-fadeIn';
            item.innerHTML = `
                <span class="text-lg">${data.success ? '✅' : '⚠️'}</span>
                <div>
                    <p class="text-xs font-medium text-white">${name}</p>
                    <p class="text-xs text-gray-500">${time} · ${data.message}</p>
                </div>`;

            // Limpiar placeholder
            if (log.querySelector('p.italic')) log.innerHTML = '';
            log.prepend(item);

            // Reset form
            document.getElementById('member-select').value = '';
            document.getElementById('guest-name').value    = '';
            document.getElementById('btn-checkin').textContent = 'Registrar Asistencia';
            document.getElementById('btn-checkin').disabled = false;
        })
        .catch(() => {
            document.getElementById('btn-checkin').textContent = 'Registrar Asistencia';
            document.getElementById('btn-checkin').disabled = false;
        });
    }
});

// Limpiar guest si se selecciona miembro y viceversa
document.getElementById('member-select').addEventListener('change', function() {
    if (this.value) document.getElementById('guest-name').value = '';
});
document.getElementById('guest-name').addEventListener('input', function() {
    if (this.value) document.getElementById('member-select').value = '';
});
</script>
