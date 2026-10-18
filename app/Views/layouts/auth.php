<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Ekkles') ?> — Ekkles</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca' }
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('assets/css/app.css') ?>">
</head>
<body class="h-full font-sans bg-gray-950">

    <!-- Fondo con gradiente y partículas decorativas -->
    <div class="min-h-screen relative flex flex-col items-center justify-center px-4 py-12 overflow-hidden">

        <!-- Gradiente de fondo -->
        <div class="absolute inset-0 bg-gradient-to-br from-gray-950 via-indigo-950/30 to-gray-950"></div>

        <!-- Círculos decorativos de fondo -->
        <div class="absolute top-1/4 -left-32 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-1/4 -right-32 w-96 h-96 bg-purple-600/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-brand-500/5 rounded-full blur-3xl"></div>

        <!-- Contenido de la página de auth -->
        <div class="relative z-10 w-full max-w-md">

            <!-- Logo y nombre de la app -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-brand-500 to-purple-600 rounded-2xl shadow-2xl mb-4">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white tracking-tight">Ekkles</h1>
                <p class="text-gray-400 mt-1 text-sm">Sistema de Gestión para Iglesias</p>
            </div>

            <!-- Alertas flash -->
            <?php if (!empty($flash_error)): ?>
            <div class="mb-4 flex items-center gap-3 bg-red-500/10 border border-red-500/20 rounded-xl px-4 py-3">
                <svg class="w-5 h-5 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-red-300 text-sm"><?= e($flash_error) ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($flash_success)): ?>
            <div class="mb-4 flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-4 py-3">
                <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-emerald-300 text-sm"><?= e($flash_success) ?></p>
            </div>
            <?php endif; ?>

            <!-- Contenido de la vista -->
            <?= $content ?>

        </div>

        <!-- Footer -->
        <p class="relative z-10 mt-8 text-center text-xs text-gray-600">
            &copy; <?= date('Y') ?> Ekkles v<?= e($app_config['version'] ?? '1.0.0') ?> —
            Todos los derechos reservados
        </p>
    </div>

    <script src="<?= url('assets/js/app.js') ?>"></script>
</body>
</html>
