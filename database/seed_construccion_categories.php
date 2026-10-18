<?php
/**
 * Script de seed: Categorías de Ingreso y Egreso para Construcción
 * Ejecutar desde: php seed_construccion_categories.php
 */

define('BASE_PATH', __DIR__ . '/..');
require_once BASE_PATH . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $_ENV['DB_HOST'] ?? '127.0.0.1',
    $_ENV['DB_PORT'] ?? '3306',
    $_ENV['DB_DATABASE'] ?? 'ekkles'
);

try {
    $pdo = new PDO($dsn, $_ENV['DB_USERNAME'] ?? 'root', $_ENV['DB_PASSWORD'] ?? '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    ]);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage() . "\n");
}

$churchId = 1;
$module   = 'construccion';

$ingresos = [
    'Aportes',
    'Cuenta inicial',
    'Donaciones',
    'Intereses',
    'Reembolso',
    'Devolución',
];

$egresos = [
    'Electricidad',
    'Plomería',
    'Albañilería',
    'Materiales Electricidad',
    'Puertas',
    'Cielo Raso',
    'Ventanas',
    'Puertas corredizas',
    'Instalación de extintores',
    'Materiales para la rampa',
    'Mano de obra para la rampa',
    'Puertas de antipánico',
    'Pintura de puertas de metal',
    'Señalizaciones',
    'Gravilla',
    'Instalación sobres de baño',
    'Materiales de plomería',
    'Instalación de pared de plycem',
    'Certificación',
    'Compra de lavamanos y llaves urinal',
    'Gastos de Uber',
    'Lámparas solares',
    'Materiales de trifásico',
    'Mano de obra de trifásico',
    'Materiales de alarma',
    'Mano de obra de alarma',
    'Materiales de pasamano',
    'Mano de obra de pasamano',
    'Materiales del muro',
    'Mano de obra del muro',
    'Transferencia por equivocación',
    'Materiales Plomería',
    'Materiales Albañilería',
    'Viga Sísmica',
    'Acero',
    'Soldadura',
    'Mano de Obra',
    'Grúa',
    'Reparación de canal',
    'Alquiler de baño portátil',
    'Transferencia',
    'Gypsum',
    'Baldosas y azulejos',
    'Accesorios de baño',
    'Accesorios de cocina',
    'Acarreo',
    'Inspección',
    'Misceláneos',
    'Otros Accesorios',
    'Préstamo',
    'Pago por uso de servidumbre (letrero)',
    'Instalación de ventana',
    'Muebles de baño',
    'Confección de Planos',
    'Pintura',
    'Gastos comisión internacional',
    'Gastos comisión local',
];

$stmt = $pdo->prepare(
    "INSERT INTO finance_categories (church_id, name, type, target_module, code, is_active) VALUES (?, ?, ?, ?, NULL, 1)"
);

$checkStmt = $pdo->prepare(
    "SELECT id FROM finance_categories WHERE church_id = ? AND LOWER(name) = LOWER(?) AND type = ? AND target_module = ?"
);

$ingresosInserted = 0;
$egresosInserted  = 0;
$skipped          = 0;

echo "=== Insertando Categorías de INGRESO para Construcción ===\n";
foreach ($ingresos as $name) {
    $checkStmt->execute([$churchId, $name, 'ingreso', $module]);
    if ($checkStmt->fetch()) {
        echo "  [SKIP] Ya existe: {$name}\n";
        $skipped++;
        continue;
    }
    $stmt->execute([$churchId, $name, 'ingreso', $module]);
    echo "  [OK] Ingreso: {$name}\n";
    $ingresosInserted++;
}

echo "\n=== Insertando Categorías de EGRESO para Construcción ===\n";
foreach ($egresos as $name) {
    $checkStmt->execute([$churchId, $name, 'egreso', $module]);
    if ($checkStmt->fetch()) {
        echo "  [SKIP] Ya existe: {$name}\n";
        $skipped++;
        continue;
    }
    $stmt->execute([$churchId, $name, 'egreso', $module]);
    echo "  [OK] Egreso: {$name}\n";
    $egresosInserted++;
}

echo "\n=== RESUMEN ===\n";
echo "  Ingresos insertados : {$ingresosInserted}\n";
echo "  Egresos insertados  : {$egresosInserted}\n";
echo "  Omitidos (dup.)     : {$skipped}\n";
echo "  TOTAL               : " . ($ingresosInserted + $egresosInserted) . "\n";
echo "\nListo!\n";
