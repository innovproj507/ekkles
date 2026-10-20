<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Ekkles') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 300: '#a5b4fc', 400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca' }
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<!--
    Layout público (sin sesión): páginas que abre cualquier persona, como el registro de asistencia por QR.
    No incluye menú lateral ni el sondeo de mensajes del layout "app" (ese sondeo recargaba la página
    a los pocos segundos cuando no hay sesión iniciada).
-->
<body class="h-full font-sans bg-[#0B0F19] text-gray-100">
    <?php if (!empty($flash_error)): ?>
    <div class="max-w-md mx-auto px-4 pt-4">
        <div class="bg-red-500/10 border border-red-500/20 text-red-300 text-sm p-4 rounded-xl text-center"><?= e($flash_error) ?></div>
    </div>
    <?php endif; ?>

    <?= $content ?>
</body>
</html>
