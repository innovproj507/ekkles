/**
 * app.js — JavaScript Principal de Ekkles
 * -----------------------------------------
 * Helpers globales, confirmaciones, AJAX y UX.
 */

'use strict';

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   1. Auto-cerrar alertas flash tras 5 s
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
document.querySelectorAll('[role="alert"]').forEach(alert => {
    setTimeout(() => {
        alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-8px)';
        setTimeout(() => alert.remove(), 400);
    }, 5000);
});

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   2. Confirmación antes de eliminar (formularios con data-confirm)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', function(e) {
        const msg = this.dataset.confirm || '¿Está seguro? Esta acción no se puede deshacer.';
        if (!confirm(msg)) e.preventDefault();
    });
});

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   3. Feedback visual en todos los formularios al enviar
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function() {
        const btn = this.querySelector('button[type="submit"]:not([data-no-loader])');
        if (btn && !btn.disabled) {
            btn.disabled = true;
            const originalContent = btn.innerHTML;
            btn.innerHTML = `
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Procesando...`;
            // Restaurar si hay error de validación (HTML5)
            if (!form.checkValidity()) {
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        }
    });
});

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   4. Sidebar móvil — Overlay
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
const sidebar  = document.getElementById('sidebar');
const overlay  = document.getElementById('sidebar-overlay');

if (sidebar && overlay) {
    const openSidebar = () => {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
    };
    const closeSidebar = () => {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    };
    overlay.addEventListener('click', closeSidebar);
}

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   5. Formatear montos de dinero en inputs
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
document.querySelectorAll('input[data-currency]').forEach(input => {
    input.addEventListener('blur', function() {
        const val = parseFloat(this.value.replace(/[^0-9.]/g, ''));
        if (!isNaN(val)) {
            this.value = val.toFixed(2);
        }
    });
});

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   6. Notificaciones flash generadas por JS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
window.ekkles = window.ekkles || {};
window.ekkles.notify = function(message, type = 'success') {
    const colors = {
        success: 'bg-emerald-500/10 border-emerald-500/20 text-emerald-300',
        error:   'bg-red-500/10 border-red-500/20 text-red-300',
        info:    'bg-blue-500/10 border-blue-500/20 text-blue-300',
        warning: 'bg-amber-500/10 border-amber-500/20 text-amber-300',
    };
    const icons = {
        success: '✅', error: '❌', info: 'ℹ️', warning: '⚠️',
    };

    const el = document.createElement('div');
    el.className = `fixed top-4 right-4 z-50 flex items-center gap-3 px-4 py-3 rounded-xl border shadow-2xl text-sm ${colors[type] || colors.info} animate-slideIn`;
    el.innerHTML = `<span>${icons[type] || '💬'}</span><span>${message}</span>`;
    document.body.appendChild(el);

    setTimeout(() => {
        el.style.transition = 'opacity 0.3s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 300);
    }, 4000);
};

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   7. Búsqueda en tiempo real con debounce
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
function debounce(fn, delay = 300) {
    let timer;
    return function(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

// Si hay un input#live-search en la página, hacer submit del formulario al escribir
const liveSearch = document.getElementById('live-search');
if (liveSearch) {
    liveSearch.addEventListener('input', debounce(function() {
        this.closest('form')?.submit();
    }, 400));
}

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   8. Marcar enlace activo en sidebar
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
const currentPath = window.location.pathname;
document.querySelectorAll('nav a[href]').forEach(link => {
    if (currentPath.startsWith(link.getAttribute('href')) && link.getAttribute('href') !== '/') {
        link.classList.add('text-brand-400');
    }
});

/* ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
   9. Tooltips para elementos con data-tooltip
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ */
// Implementado vía CSS puro (ver app.css)

console.log('%c🏛️ Ekkles v1.0.0 — Sistema de Gestión para Iglesias', 'color:#6366f1;font-size:14px;font-weight:bold;');
