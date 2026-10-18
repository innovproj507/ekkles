<?php
/**
 * Script de seed: Categorías de Ingreso y Egreso para Obras Misioneras y Donaciones
 * Ejecutar desde: php seed_donaciones_categories.php
 */

// Cargar el autoloader de la aplicación
define('BASE_PATH', __DIR__ . '/..');
require_once BASE_PATH . '/vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// Conexión directa PDO con charset UTF-8
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
$module   = 'donaciones';

$ingresos = [
    'Donación de PROSEA',
    'Donación de Iglesia Unión del Área del Canal',
    'Donación de Yolanda Torres',
    'Donación de la Sra. María',
    'Donación de Scott',
    'Donación para canoa de Nelson Chamí',
    'Donación para canoa de Leo',
    'Donación para jóvenes de Cimarrón',
    'Donación McKenzie CoC',
    'Donación Timberlane CoC',
    'Reembolso',
    'Devolución',
    'Donación Luz Marina Velásquez',
    'Donación Park CoC',
    'Donación Mike Vann',
    'Donación Rebeca Kidder',
    'Compra de Medicinas',
    'Monto inicial',
    'Donación',
    'Donación Computadora PROSEA',
    'Donaciones para el equipo de sonido',
    'Donaciones de Jason Meadow para Danitza Cárdenas',
    'Reservación de hoteles',
    'Transporte para giras médicas',
    'Transferencia de cuentas',
    'Intereses',
];

$egresos = [
    'Salario de Nelson Mepaquito',
    'Salario de Nelson Chamí',
    'Salario de Edwin Isabaré',
    'Salario de Euclides Nieto',
    'Gastos PROSEA',
    'Gastos para canoa de Nelson Chamí',
    'Gastos de hoteles',
    'Gastos de transporte',
    'Donación de comida',
    'Compra de medicamentos',
    'Comida para Ancoré',
    'Misceláneos',
    'Encuentro juvenil',
    'Reembolso a Nelson',
    'Reembolso por materiales para canoa',
    'Compra de comida para familias de Cimarrón',
    'Transporte en canoa y comida a Cimarron',
    'Construcción de la casa de Nelson Mepaquito',
    'Transporte hijas de Leo',
    'Chamí',
    'Mano de obra canoa',
    'Escuela Bíblica de Verano',
    'Gastos del Proyecto Wellness',
    'Universidad de Enerson Mepaquito',
    'Sepelio de Luis Amagara',
    'Gastos para matrimonios',
    'Donaciones de Timberlake Church of Christ',
    'Donación',
    'Eliester guiro y baños Ancoré',
    'Computadora PROSEA',
    'Transporte',
    'Reembolso',
    'Transferencia a JOSIAS de Scott Blackwell',
    'Gastos escolares de Danitza Cárdenas',
    'Gastos para equipo de sonido',
    'Salario de Emiliano Mepaquito',
    'Compra comida Sra. María',
    'Compra de comida Scott',
    'Gastos de comida Iglesia Unión',
    'Transferencia de cuentas',
    'Comisión del banco',
    'Transferencia por equivocación',
];

$stmt = $pdo->prepare(
    "INSERT INTO finance_categories (church_id, name, type, target_module, code, is_active) VALUES (?, ?, ?, ?, NULL, 1)"
);

$ingresosInserted = 0;
$egresosInserted  = 0;
$skipped          = 0;

// Verificar si ya existe la categoría (evitar duplicados)
$checkStmt = $pdo->prepare(
    "SELECT id FROM finance_categories WHERE church_id = ? AND LOWER(name) = LOWER(?) AND type = ? AND target_module = ?"
);

echo "=== Insertando Categorías de INGRESO para Obras Misioneras y Donaciones ===\n";
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

echo "\n=== Insertando Categorías de EGRESO para Obras Misioneras y Donaciones ===\n";
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
