<?php
/**
 * Sidebar — Navegación Principal
 * Incluye iconos SVG y menú colapsible por secciones.
 */
$roles = require dirname(__DIR__, 3) . '/config/roles.php';
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Helper para detectar ruta activa
$isActive = function(string $path) use ($currentUri): bool {
    return str_starts_with($currentUri, $path);
};

// Secciones del menú con sus permisos
$menuSections = [
    [
        'label' => 'Principal',
        'items' => [
            ['href' => '/dashboard', 'label' => 'Dashboard', 'permission' => 'dashboard.view', 'icon' => 'home'],
        ]
    ],
    [
        'label' => 'Iglesia',
        'items' => [
            ['href' => '/miembros',   'label' => 'Miembros',    'permission' => 'members.view',    'icon' => 'users'],
            ['href' => '/familias',   'label' => 'Familias',    'permission' => 'families.view',   'icon' => 'family'],
            ['href' => '/asistencia', 'label' => 'Asistencia',  'permission' => 'attendance.view', 'icon' => 'check-circle'],
            ['href' => '/eventos',    'label' => 'Eventos',     'permission' => 'events.view',     'icon' => 'calendar'],
        ]
    ],
    [
        'label' => 'Ministerios',
        'items' => [
            ['href' => '/ministerios', 'label' => 'Ministerios',  'permission' => 'ministries.view', 'icon' => 'star'],
            ['href' => '/voluntarios', 'label' => 'Voluntarios',  'permission' => 'volunteers.view', 'icon' => 'hand'],
        ]
    ],
    [
        'label' => 'Finanzas',
        'items' => [
            ['href' => '/ofrendas',       'label' => 'Ofrendas',                        'permission' => 'donations.view', 'icon' => 'cash'],
            ['href' => '/construccion',   'label' => 'Construcción',                    'permission' => 'donations.view', 'icon' => 'building'],
            ['href' => '/donaciones',     'label' => 'Obras Misioneras y Donaciones',   'permission' => 'donations.view', 'icon' => 'heart'],
            ['href' => '/egresos',        'label' => 'Egresos',                         'permission' => 'finance.view',   'icon' => 'cash-out'],
            ['href' => '/conciliaciones', 'label' => 'Conciliación',                    'permission' => 'finance.view',   'icon' => 'clipboard'],
        ]
    ],
    [
        'label' => 'Discipulado',
        'items' => [
            ['href' => '/pastoral',          'label' => 'Seguimiento',  'permission' => 'pastoral.view',  'icon' => 'clipboard'],
            ['href' => '/pastoral/pipeline', 'label' => 'Pipeline',     'permission' => 'pastoral.view',  'icon' => 'trending-up'],
        ]
    ],
    [
        'label' => 'Sistema',
        'items' => [
            ['href' => '/reportes',      'label' => 'Reportes',      'permission' => 'reports.view', 'icon' => 'document'],
            ['href' => '/usuarios',      'label' => 'Usuarios',      'permission' => 'users.view',   'icon' => 'users'],
            ['href' => '/configuracion', 'label' => 'Configuración', 'permission' => 'users.view',   'icon' => 'cog'],
        ]
    ],
];

// Iconos SVG por nombre
$icons = [
    'home'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
    'users'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
    'family'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>',
    'check-circle'=> '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'calendar'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
    'star'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>',
    'hand'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"/>',
    'heart'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
    'chart-bar'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
    'clipboard'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>',
    'trending-up' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>',
    'document'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
    'cog'         => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
    'cash'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    'cash-out'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0M17 17l4-4m0 0l-4-4m4 4H13"/>',
    'building'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
];
?>

<!-- Sidebar -->
<aside id="sidebar"
       class="w-64 flex-shrink-0 bg-gray-900 border-r border-gray-800 flex flex-col
              fixed lg:static inset-y-0 left-0 z-40
              transition-all duration-300 ease-in-out -translate-x-full lg:translate-x-0">

    <!-- Logo -->
    <div class="flex items-center px-6 py-5 border-b border-gray-800">
        <a href="/dashboard" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
            <div class="w-9 h-9 bg-gradient-to-br from-brand-500 to-purple-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-brand-500/20">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
            <div>
                <p class="font-bold text-white text-base leading-none">Ekkles</p>
                <p class="text-xs text-gray-500 mt-0.5 font-medium">Gestión de Iglesias</p>
            </div>
        </a>
        <!-- Cerrar en móvil -->
        <button onclick="document.getElementById('sidebar').classList.add('-translate-x-full')"
                class="ml-auto lg:hidden text-gray-500 hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Navegación -->
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
        <?php foreach ($menuSections as $section): ?>
        <div class="mb-2">
            <p class="px-3 mb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <?= e($section['label']) ?>
            </p>
            <?php foreach ($section['items'] as $item): ?>
                <?php if ($item['permission'] && !$auth_can($item['permission'])) continue; ?>
                <?php $active = $isActive($item['href']); ?>
                <a href="<?= e($item['href']) ?>"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                          <?= $active
                              ? 'bg-brand-500/15 text-brand-400 border border-brand-500/20'
                              : 'text-gray-400 hover:text-white hover:bg-gray-800' ?>">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <?= $icons[$item['icon']] ?? '' ?>
                    </svg>
                    <?= e($item['label']) ?>
                    <?php if ($active): ?>
                    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-brand-400"></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </nav>

    <!-- Footer del sidebar -->
    <div class="border-t border-gray-800 px-4 py-4">
        <div class="flex items-center gap-3 bg-gray-800/50 rounded-xl px-3 py-2.5">
            <img src="<?= e(avatarUrl($auth_user['avatar'] ?? null, $auth_user['name'] ?? 'U')) ?>"
                 alt="Avatar" class="w-8 h-8 rounded-lg object-cover flex-shrink-0">
            <div class="min-w-0">
                <p class="text-sm font-medium text-white truncate"><?= e($auth_user['name'] ?? '') ?></p>
                <p class="text-xs text-gray-400 truncate"><?= e($auth_user['email'] ?? '') ?></p>
            </div>
        </div>
    </div>
</aside>

<!-- Overlay para móvil -->
<div id="sidebar-overlay" onclick="document.getElementById('sidebar').classList.add('-translate-x-full')"
     class="fixed inset-0 bg-black/50 z-30 hidden lg:hidden"></div>
