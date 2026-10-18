<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Ekkles - Sistema de Gestión Integral para Iglesias">
    <title><?= e($title ?? 'Ekkles') ?> — Ekkles</title>

    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#f0f0ff',
                            100: '#e0e1ff',
                            200: '#c7c8fe',
                            300: '#a4a5fd',
                            400: '#8183fa',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= url('assets/css/app.css') ?>">

    <!-- Chart.js para gráficas -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
    
    <!-- SweetAlert2 para notificaciones -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="h-full font-sans bg-gray-950 text-gray-100" x-data="{ sidebarOpen: false }">

    <div class="flex h-full">

        <!-- ================================================
             SIDEBAR — Navegación Principal
             ================================================ -->
        <?php \App\Core\View::partial('partials/sidebar', ['auth_user' => $auth_user, 'auth_can' => $auth_can]); ?>

        <!-- ================================================
             CONTENIDO PRINCIPAL
             ================================================ -->
        <div class="flex-1 flex flex-col min-h-0 overflow-hidden">

            <!-- Topbar -->
            <header class="bg-gray-900 border-b border-gray-800 px-6 py-4 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-4">
                    <!-- Botón hamburguesa (móvil y escritorio) -->
                    <button onclick="toggleSidebar()"
                            class="text-gray-400 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="text-lg font-semibold text-white"><?= e($title ?? 'Dashboard') ?></h1>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Notificaciones -->
                    <?php
                    $notifModel  = new \App\Modules\System\Models\Notification();
                    $unreadCount = \App\Core\Auth::id() ? $notifModel->countUnread(\App\Core\Auth::id()) : 0;
                    ?>
                    <a href="/notificaciones" class="relative text-gray-400 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <?php if ($unreadCount > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center font-bold">
                            <?= $unreadCount > 9 ? '9+' : $unreadCount ?>
                        </span>
                        <?php endif; ?>
                    </a>

                    <!-- Avatar + Menú usuario -->
                    <div class="relative group">
                        <button class="flex items-center gap-2 bg-gray-800 rounded-xl px-3 py-2 hover:bg-gray-700 transition-colors">
                            <img src="<?= e(avatarUrl($auth_user['avatar'] ?? null, $auth_user['name'] ?? 'U')) ?>"
                                 alt="Avatar" class="w-8 h-8 rounded-lg object-cover">
                            <div class="hidden sm:block text-left">
                                <p class="text-sm font-medium text-white leading-none"><?= e($auth_user['name'] ?? '') ?></p>
                                <p class="text-xs text-gray-400 mt-0.5"><?= e($app_config['roles'][\App\Core\Auth::role()]['label'] ?? '') ?></p>
                            </div>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <!-- Dropdown -->
                        <div class="absolute right-0 top-full mt-2 w-48 bg-gray-800 rounded-xl border border-gray-700 shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                            <a href="/perfil" class="flex items-center gap-2 px-4 py-3 text-sm text-gray-300 hover:text-white hover:bg-gray-700 rounded-t-xl transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Mi Perfil
                            </a>
                            <a href="/perfil/mfa" class="flex items-center gap-2 px-4 py-3 text-sm text-gray-300 hover:text-white hover:bg-gray-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                Seguridad 2FA
                            </a>
                            <div class="border-t border-gray-700"></div>
                            <a href="/logout" class="flex items-center gap-2 px-4 py-3 text-sm text-red-400 hover:text-red-300 hover:bg-gray-700 rounded-b-xl transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Alertas Flash -->
            <?php \App\Core\View::partial('partials/alerts', [
                'flash_success' => $flash_success,
                'flash_error'   => $flash_error,
                'flash_info'    => $flash_info ?? null,
            ]); ?>

            <!-- Área de contenido principal -->
            <main class="flex-1 overflow-y-auto p-6">
                <?= $content ?>
            </main>

        </div><!-- /.flex-1 -->
    </div><!-- /.flex -->

    <!-- Scripts -->
    <script src="<?= url('assets/js/app.js') ?>"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            // Toggle classes para móvil (slide-in)
            sidebar.classList.toggle('-translate-x-full');
            if (overlay) {
                overlay.classList.toggle('hidden');
            }
            
            // Toggle classes para escritorio (colapsar layout animado)
            if (window.innerWidth >= 1024) {
                sidebar.classList.toggle('lg:-ml-64');
                sidebar.classList.toggle('lg:opacity-0');
            }
        }

        // AJAX Polling: Mensajes de Administrador y Cierre Forzado
        setInterval(function() {
            fetch('/api/messages/check', {
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => {
                if(response.status === 401 || response.redirected) {
                    // Sesión destruida (probablemente force_logout)
                    window.location.reload();
                    return;
                }
                return response.json();
            })
            .then(data => {
                if(data && data.messages && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        Swal.fire({
                            toast: true,
                            position: 'bottom-end',
                            icon: 'info',
                            title: 'Mensaje del Administrador',
                            text: msg.body,
                            showConfirmButton: true,
                            confirmButtonText: 'Cerrar',
                            confirmButtonColor: '#4f46e5',
                            background: '#1f2937',
                            color: '#fff',
                            timer: 15000,
                            timerProgressBar: true
                        });
                    });
                }
            })
            .catch(err => console.error(err));
        }, 15000); // 15 segundos
    </script>
</body>
</html>
