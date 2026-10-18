<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $code ?> - <?= $title ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-full bg-gray-950 text-gray-100 flex items-center justify-center p-6">
    <div class="max-w-md w-full text-center space-y-8">
        <div>
            <h1 class="text-9xl font-bold text-brand-500 tracking-tighter"><?= $code ?></h1>
            <h2 class="text-3xl font-semibold mt-4 text-white"><?= $title ?></h2>
            <?php if (!empty($message)): ?>
                <p class="text-gray-400 mt-4 text-lg"><?= htmlspecialchars($message) ?></p>
            <?php else: ?>
                <p class="text-gray-400 mt-4 text-lg">Lo sentimos, no pudimos procesar tu solicitud.</p>
            <?php endif; ?>
        </div>
        
        <div class="pt-8">
            <a href="/dashboard" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-xl text-white bg-brand-600 hover:bg-brand-500 transition-colors duration-200">
                Volver al Inicio
            </a>
        </div>
    </div>
</body>
</html>
