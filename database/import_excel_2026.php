<?php
/**
 * Script de importación: Excel 2026 (carpeta ekkles/) → Base de datos
 * ---------------------------------------------------------------------
 * Importa ingresos/egresos 2026 de las tres cuentas (Iglesia, Construcción,
 * Obras Misioneras y Donaciones), miembros y asignaciones de ministerios.
 *
 * Uso (requiere PHP 8.2+):
 *   php database/import_excel_2026.php            → simulacro: solo reporta, no escribe nada
 *   php database/import_excel_2026.php --commit   → escribe todo en una transacción
 *
 * Opciones de commit:
 *   --keep-test-data   no borra los registros de prueba existentes
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/vendor/autoload.php';

Dotenv\Dotenv::createImmutable(BASE_PATH)->safeLoad();

use App\Core\Database;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlsDate;
use Ramsey\Uuid\Uuid;

const CHURCH_ID   = 1;
const ADMIN_ID    = 1;
const IMPORT_YEAR = 2026;
const MARK        = 'Importado: ';
const UNCATEGORIZED = 'Sin categoría (importación)';

$commit   = in_array('--commit', $argv, true);
$keepTest = in_array('--keep-test-data', $argv, true);

$db  = Database::getInstance();
$dir = BASE_PATH . '/ekkles/';

// Registros de prueba a borrar (verificados por id + contenido)
$testData = [
    'donations' => [1 => 'REC-2026-00001', 2 => 'REC-2026-00002'],
    'finances'  => [1 => 150.00, 2 => 300.00],
    'members'   => [1 => 'MBR00001'],
];

// Cuentas: archivo → módulo
$accounts = [
    'ofrendas'     => ['file' => 'Fundación Unidad Cristiana Iglesia 2026.xlsx',                        'label' => 'Iglesia'],
    'construccion' => ['file' => 'Fundación Unidad Cristiana Construcción 2026.xlsx',                   'label' => 'Construcción'],
    'donaciones'   => ['file' => 'Fundación Unidad Cristiana Obras Misioneras y Donaciones 2026.xlsx', 'label' => 'Obras Misioneras'],
];

// Decisiones confirmadas por el usuario tras el simulacro
$dateOverrides = [
    'Obras Misioneras/INGRESOS 2026' => [46 => '2026-06-01', 47 => '2026-06-01', 48 => '2026-06-01'], // venían como 2023
    'Obras Misioneras/EGRESOS 2026'  => [215 => '2026-09-15', 216 => '2026-09-15'],                  // venían sin fecha
];
$memberAliases = [   // nombre duplicado → nombre que se conserva
    'melissa walter' => 'melissa watler',
];
$ministryNameOverrides = [   // apodo en hoja Ministerios → miembro
    'josue' => 'josue cedeno',
    'cony'  => 'constancia gaitan',
];
$coupleOverrides = [   // texto en hoja Parejas → [apellido, [nombres]]
    'Watsón Wendell Jr. Mabe' => ['Watsón', ['Wendell Jr.', 'Mabe']],
];

// ─────────────────────────────────────────────────────────────
// Utilidades
// ─────────────────────────────────────────────────────────────

/** Limpiar texto: quitar caracteres invisibles y espacios repetidos */
function clean(mixed $v): string
{
    $s = str_replace(["\u{2060}", "\u{00A0}", "\u{200B}"], ['', ' ', ''], (string)($v ?? ''));
    return trim(preg_replace('/\s+/u', ' ', $s));
}

/** Clave de comparación: minúsculas y sin tildes */
function nkey(mixed $v): string
{
    $s = mb_strtolower(clean($v));
    return strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
}

function money(float $n): string
{
    return '$' . number_format($n, 2);
}

/** Valor de una celda usando el valor calculado guardado en el archivo */
function cellValue($ws, int $col, int $row): mixed
{
    $cell = $ws->getCell([$col, $row]);
    $v = $cell->getValue();
    if (is_string($v) && str_starts_with($v, '=')) {
        $v = $cell->getOldCalculatedValue();
    }
    return $v;
}

/**
 * Convertir el valor de la celda FECHA a 'Y-m-d'.
 * @return array{0: ?string, 1: ?string} [fecha, nota de corrección]
 */
function parseDate(mixed $v, string $headerFormat): array
{
    if ($v === null || $v === '') {
        return [null, null];
    }
    if (is_numeric($v)) {
        return [XlsDate::excelToDateTimeObject((float)$v)->format('Y-m-d'), null];
    }
    // Fechas escritas como texto, p.ej. '26//5/2026' o '7/15//2026'
    if (preg_match_all('/\d+/', (string)$v, $m) && count($m[0]) === 3) {
        [$a, $b, $y] = array_map('intval', $m[0]);
        if ($a > 12)      { [$d, $mo] = [$a, $b]; }
        elseif ($b > 12)  { [$mo, $d] = [$a, $b]; }
        elseif (str_contains($headerFormat, 'dd/mm')) { [$d, $mo] = [$a, $b]; }
        else              { [$mo, $d] = [$a, $b]; }
        if (checkdate($mo, $d, $y)) {
            $date = sprintf('%04d-%02d-%02d', $y, $mo, $d);
            return [$date, "fecha en texto '{$v}' → {$date}"];
        }
    }
    return [null, null];
}

/** Leer una hoja INGRESOS/EGRESOS: localiza el encabezado y mapea columnas por nombre */
function readLedger(string $path, string $sheetName): array
{
    $reader = IOFactory::createReaderForFile($path);
    $reader->setReadDataOnly(true);
    $reader->setLoadSheetsOnly([$sheetName]);
    $ws = $reader->load($path)->getSheetByName($sheetName);

    $maxRow = $ws->getHighestDataRow();
    $maxCol = min(30, \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($ws->getHighestDataColumn()));

    $headerRow = null;
    for ($r = 1; $r <= 10; $r++) {
        if (str_starts_with(nkey(cellValue($ws, 1, $r)), 'fecha')) { $headerRow = $r; break; }
    }
    if (!$headerRow) {
        throw new RuntimeException("No se encontró encabezado FECHA en {$sheetName}");
    }

    $cols = [];
    $patterns = [
        'fecha'     => '/^fecha/',
        'recibo'    => '/^(recibo|factura)/',
        'ruc'       => '/^ruc/',
        'nombre'    => '/^(donante|nombre del proveedor)/',
        'detalle'   => '/^detalle/',
        'monto'     => '/^monto/',
        'total'     => '/^total/',
        'categoria' => '/^categor/',
    ];
    $headerFormat = '';
    for ($c = 1; $c <= $maxCol; $c++) {
        $h = nkey(cellValue($ws, $c, $headerRow));
        foreach ($patterns as $name => $re) {
            if (!isset($cols[$name]) && $h !== '' && preg_match($re, $h)) {
                $cols[$name] = $c;
                if ($name === 'fecha') $headerFormat = $h;
            }
        }
    }

    $rows = [];
    $excelTotal = null;
    for ($r = $headerRow + 1; $r <= $maxRow; $r++) {
        $get = fn(string $n) => isset($cols[$n]) ? cellValue($ws, $cols[$n], $r) : null;
        if ($excelTotal === null && is_numeric($get('total'))) {
            $excelTotal = (float)$get('total');
        }
        $rows[] = [
            'row'       => $r,
            'fecha_raw' => $get('fecha'),
            'recibo'    => clean($get('recibo')),
            'ruc'       => clean($get('ruc')),
            'nombre'    => clean($get('nombre')),
            'detalle'   => clean($get('detalle')),
            'monto'     => $get('monto'),
            'categoria' => clean($get('categoria')),
        ];
    }
    return ['rows' => $rows, 'headerFormat' => $headerFormat, 'excelTotal' => $excelTotal];
}

/** Ejecutar escritura solo en modo commit; en simulacro devuelve IDs ficticios */
function write(string $sql, array $params = []): int
{
    global $commit, $db;
    static $fakeId = 900000;
    if (!$commit) {
        return ++$fakeId;
    }
    $db->execute($sql, $params);
    return (int)$db->lastInsertId();
}

function insert(string $table, array $data): int
{
    $cols = '`' . implode('`, `', array_keys($data)) . '`';
    $ph   = implode(', ', array_fill(0, count($data), '?'));
    return write("INSERT INTO `{$table}` ({$cols}) VALUES ({$ph})", array_values($data));
}

$report = [];
function out(string $line = ''): void
{
    echo $line, PHP_EOL;
}

// ─────────────────────────────────────────────────────────────
// 0. Verificaciones previas
// ─────────────────────────────────────────────────────────────
out('==============================================================');
out(' IMPORTACIÓN EXCEL 2026 — ' . ($commit ? 'MODO COMMIT (ESCRIBE EN LA BD)' : 'SIMULACRO (NO ESCRIBE NADA)'));
out('==============================================================');

$already = (int)$db->queryOne(
    "SELECT (SELECT COUNT(*) FROM donations WHERE notes LIKE ?) + (SELECT COUNT(*) FROM finances WHERE notes LIKE ?) + (SELECT COUNT(*) FROM members WHERE pastoral_notes LIKE ?) AS n",
    [MARK . '%', MARK . '%', MARK . '%']
)['n'];
if ($already > 0) {
    out("ABORTADO: ya existen {$already} registros importados previamente (marca '" . MARK . "').");
    exit(1);
}

if ($commit) {
    $db->beginTransaction();
}

try {
    // ─────────────────────────────────────────────────────────
    // 1. Limpieza de datos de prueba
    // ─────────────────────────────────────────────────────────
    out();
    out('── 1. Datos de prueba ──');
    if ($keepTest) {
        out('   Se conservan (--keep-test-data).');
    } else {
        foreach ($testData['donations'] as $id => $receipt) {
            $row = $db->queryOne("SELECT id FROM donations WHERE id = ? AND receipt_number = ?", [$id, $receipt]);
            if ($row) { write("DELETE FROM donations WHERE id = ?", [$id]); out("   Borrar donation #{$id} ({$receipt})"); }
        }
        foreach ($testData['finances'] as $id => $amount) {
            $row = $db->queryOne("SELECT id FROM finances WHERE id = ? AND amount = ?", [$id, $amount]);
            if ($row) { write("DELETE FROM finances WHERE id = ?", [$id]); out("   Borrar finance #{$id} (" . money($amount) . ")"); }
        }
        foreach ($testData['members'] as $id => $code) {
            $row = $db->queryOne("SELECT id FROM members WHERE id = ? AND code = ?", [$id, $code]);
            if (!$row) continue;
            $deps = (int)$db->queryOne(
                "SELECT (SELECT COUNT(*) FROM donations WHERE member_id = ?) + (SELECT COUNT(*) FROM event_attendance WHERE member_id = ?)
                      + (SELECT COUNT(*) FROM family_members WHERE member_id = ?) + (SELECT COUNT(*) FROM pastoral_follows WHERE member_id = ?) AS n",
                [$id, $id, $id, $id]
            )['n'];
            if ($deps > 0) {
                out("   ⚠ Miembro #{$id} ({$code}) tiene {$deps} registros relacionados: NO se borra.");
            } else {
                write("DELETE FROM members WHERE id = ?", [$id]);
                out("   Borrar miembro #{$id} ({$code})");
            }
        }
    }

    // ─────────────────────────────────────────────────────────
    // 2. Categorías existentes
    // ─────────────────────────────────────────────────────────
    $categories = [];
    foreach ($db->query("SELECT id, name, type, target_module FROM finance_categories WHERE church_id = ? AND is_active = 1", [CHURCH_ID]) as $c) {
        $categories[$c['type']][$c['target_module']][nkey($c['name'])] ??= (int)$c['id'];
    }
    $uncategorizedIds = [];
    $getUncategorized = function (string $type, string $module) use (&$categories, &$uncategorizedIds): int {
        $k = nkey(UNCATEGORIZED);
        if (isset($categories[$type][$module][$k])) return $categories[$type][$module][$k];
        $id = insert('finance_categories', [
            'church_id' => CHURCH_ID, 'name' => UNCATEGORIZED, 'type' => $type, 'target_module' => $module, 'is_active' => 1,
        ]);
        $uncategorizedIds[] = "{$type}/{$module}";
        return $categories[$type][$module][$k] = $id;
    };

    // Recibos: existentes en BD + contador para los vacíos
    $existingReceipts = array_flip(array_column(
        $db->query("SELECT receipt_number FROM donations WHERE church_id = ?", [CHURCH_ID]), 'receipt_number'
    ));
    if (!$keepTest) {
        foreach ($testData['donations'] as $r) unset($existingReceipts[$r]);
    }
    $settings = $db->query("SELECT `key`, value FROM settings WHERE church_id = ? AND `key` IN ('receipt_counter','receipt_prefix')", [CHURCH_ID]);
    $settings = array_column($settings, 'value', 'key');
    $receiptCounter = (int)($settings['receipt_counter'] ?? 1);
    $receiptPrefix  = $settings['receipt_prefix'] ?? 'REC';

    // ─────────────────────────────────────────────────────────
    // 3. Ingresos y egresos
    // ─────────────────────────────────────────────────────────
    $held       = [];   // filas que requieren decisión del usuario
    $noCategory = [];   // filas cargadas en "Sin categoría"
    $dateFixes  = [];
    $summary    = [];

    foreach ($accounts as $module => $acc) {
        foreach (['ingreso' => 'INGRESOS ' . IMPORT_YEAR, 'egreso' => 'EGRESOS ' . IMPORT_YEAR] as $type => $sheet) {
            $ledger = readLedger($dir . $acc['file'], $sheet);
            $src = "{$acc['label']}/{$sheet}";
            $stats = ['ok' => 0, 'sum' => 0.0, 'skipped' => 0, 'held' => 0, 'held_sum' => 0.0];

            foreach ($ledger['rows'] as $r) {
                $amount = is_numeric($r['monto']) ? round((float)$r['monto'], 2) : 0.0;
                [$date, $fix] = parseDate($r['fecha_raw'], $ledger['headerFormat']);
                if (isset($dateOverrides[$src][$r['row']])) {
                    $newDate = $dateOverrides[$src][$r['row']];
                    $fix  = 'fecha ' . ($date ?? 'vacía') . " → {$newDate} (confirmado por el usuario)";
                    $date = $newDate;
                }

                // Filas de plantilla vacías o en cero
                if ($amount == 0.0) { $stats['skipped']++; continue; }
                if ($amount < 0) {
                    $held[] = "{$src} fila {$r['row']}: monto negativo " . money($amount) . " — {$r['detalle']}";
                    $stats['held']++; $stats['held_sum'] += $amount; continue;
                }
                if ($date === null) {
                    $held[] = "{$src} fila {$r['row']}: SIN FECHA — " . money($amount) . " {$r['nombre']} / {$r['detalle']}";
                    $stats['held']++; $stats['held_sum'] += $amount; continue;
                }
                $year = (int)substr($date, 0, 4);
                if ($year === 2028) {
                    $newDate = IMPORT_YEAR . substr($date, 4);
                    $fix = "año 2028 → {$newDate}";
                    $date = $newDate;
                } elseif ($year !== IMPORT_YEAR) {
                    $held[] = "{$src} fila {$r['row']}: fecha {$date} (no es " . IMPORT_YEAR . ") — " . money($amount) . " {$r['nombre']} / {$r['detalle']}";
                    $stats['held']++; $stats['held_sum'] += $amount; continue;
                }
                if ($fix) {
                    $dateFixes[] = "{$src} fila {$r['row']}: {$fix} — " . money($amount) . " {$r['detalle']}";
                }

                // Categoría
                $catId = $categories[$type][$module][nkey($r['categoria'])] ?? null;
                if (!$catId) {
                    $catId = $getUncategorized($type, $module);
                    $noCategory[] = "{$src} fila {$r['row']}: '{$r['categoria']}' — {$date} " . money($amount) . " {$r['nombre']} / {$r['detalle']}";
                }

                $notes = MARK . "{$acc['file']} / {$sheet} fila {$r['row']}";

                if ($type === 'ingreso') {
                    $receipt = preg_replace('/\.0+$/', '', $r['recibo']);
                    if ($receipt === '' || isset($existingReceipts[$receipt])) {
                        if ($receipt !== '') {
                            $held[] = "{$src} fila {$r['row']}: recibo {$receipt} duplicado — se asigna número nuevo";
                        }
                        $receipt = $receiptPrefix . '-' . IMPORT_YEAR . '-' . str_pad((string)$receiptCounter++, 5, '0', STR_PAD_LEFT);
                    }
                    $existingReceipts[$receipt] = true;

                    $catKey = nkey($r['categoria']);
                    $donationType = match ($module) {
                        'ofrendas'     => $catKey === 'aporte especial' ? 'ofrenda_especial' : 'ofrenda_general',
                        'construccion' => 'construccion',
                        'donaciones'   => 'donacion',
                    };

                    insert('donations', [
                        'church_id'      => CHURCH_ID,
                        'uuid'           => Uuid::uuid4()->toString(),
                        'receipt_number' => $receipt,
                        'member_id'      => null,
                        'donor_name'     => $r['nombre'] !== '' ? mb_substr($r['nombre'], 0, 150) : null,
                        'ruc'            => $r['ruc'] !== '' ? mb_substr($r['ruc'], 0, 30) : null,
                        'type'           => $donationType,
                        'detail'         => $r['detalle'] !== '' ? mb_substr($r['detalle'], 0, 255) : null,
                        'category_id'    => $catId,
                        'donation_date'  => $date,
                        'amount'         => $amount,
                        'currency'       => 'USD',
                        'payment_method' => $catKey === 'aporte presencial' ? 'efectivo' : 'transferencia',
                        'notes'          => $notes,
                        'period_month'   => (int)substr($date, 5, 2),
                        'period_year'    => (int)substr($date, 0, 4),
                        'created_by'     => ADMIN_ID,
                        'status'         => 'aprobado',
                        'approved_by'    => ADMIN_ID,
                    ]);
                } else {
                    $description = $r['detalle'] !== '' ? $r['detalle'] : ($r['categoria'] ?: $r['nombre']);
                    insert('finances', [
                        'church_id'        => CHURCH_ID,
                        'uuid'             => Uuid::uuid4()->toString(),
                        'type'             => 'egreso',
                        'category_id'      => $catId,
                        'target_module'    => $module,
                        'description'      => mb_substr($description, 0, 255),
                        'amount'           => $amount,
                        'currency'         => 'USD',
                        'payment_method'   => 'transferencia',
                        'invoice_number'   => $r['recibo'] !== '' ? mb_substr(preg_replace('/\.0+$/', '', $r['recibo']), 0, 50) : null,
                        'provider_ruc'     => $r['ruc'] !== '' ? mb_substr($r['ruc'], 0, 30) : null,
                        'provider_name'    => $r['nombre'] !== '' ? mb_substr($r['nombre'], 0, 150) : null,
                        'transaction_date' => $date,
                        'period_month'     => (int)substr($date, 5, 2),
                        'period_year'      => (int)substr($date, 0, 4),
                        'notes'            => $notes,
                        'created_by'       => ADMIN_ID,
                        'approved_by'      => ADMIN_ID,
                        'status'           => 'aprobado',
                    ]);
                }
                $stats['ok']++;
                $stats['sum'] += $amount;
            }
            $stats['excel'] = $ledger['excelTotal'];
            $summary[$src] = $stats;
        }
    }

    if ($commit && $receiptCounter !== (int)($settings['receipt_counter'] ?? 1)) {
        $db->execute("UPDATE settings SET value = ? WHERE church_id = ? AND `key` = 'receipt_counter'", [$receiptCounter, CHURCH_ID]);
    }

    out();
    out('── 2. Finanzas 2026 ──');
    out(sprintf('   %-38s %6s %14s %14s %8s %14s', 'Hoja', 'Filas', 'A cargar', 'Total Excel', 'Retenid.', 'Monto reten.'));
    foreach ($summary as $src => $s) {
        $diff = $s['excel'] !== null ? round($s['excel'] - $s['sum'] - $s['held_sum'], 2) : null;
        out(sprintf('   %-38s %6d %14s %14s %8d %14s%s',
            $src, $s['ok'], money($s['sum']), $s['excel'] !== null ? money($s['excel']) : 'n/d',
            $s['held'], money($s['held_sum']), $diff ? '   ⚠ diferencia ' . money($diff) : '   ✓'));
    }
    $receiptsGenerated = $receiptCounter - (int)($settings['receipt_counter'] ?? 1);
    out("   Recibos generados automáticamente (ingresos sin número): {$receiptsGenerated}");

    out();
    out('── 3. Correcciones automáticas de fecha (' . count($dateFixes) . ') ──');
    foreach ($dateFixes as $l) out('   ' . $l);

    out();
    out('── 4. RETENIDAS — necesitan tu decisión, NO se cargan (' . count($held) . ') ──');
    foreach ($held as $l) out('   ' . $l);

    out();
    out('── 5. Cargadas en "' . UNCATEGORIZED . '" — recategorizar en la app (' . count($noCategory) . ') ──');
    foreach ($noCategory as $l) out('   ' . $l);
    if ($uncategorizedIds) out('   Categorías nuevas: ' . implode(', ', $uncategorizedIds));

    // ─────────────────────────────────────────────────────────
    // 4. Miembros
    // ─────────────────────────────────────────────────────────
    $groups = [
        'mujeres solteras'          => ['F', 'soltero'],
        'varones solteros'          => ['M', 'soltero'],
        'jovenes (chicos)'          => ['M', null],
        'jovenes (chicas)'          => ['F', null],
        'adultos mayores (varones)' => ['M', null],
        'adultos mayores (mujeres)' => ['F', null],
        'ninos'                     => ['M', null],
        'ninas'                     => ['F', null],
    ];

    $reader = IOFactory::createReaderForFile($dir . 'Miembros y Ministerios.xlsx');
    $reader->setReadDataOnly(true);
    $book = $reader->load($dir . 'Miembros y Ministerios.xlsx');

    /** Fila donde aparece alguno de los encabezados esperados */
    $findHeaderRow = function ($ws, array $expected): int {
        for ($r = 1; $r <= 20; $r++) {
            for ($c = 1; $c <= 30; $c++) {
                if (in_array(nkey(cellValue($ws, $c, $r)), $expected, true)) return $r;
            }
        }
        throw new RuntimeException('Encabezado no encontrado en la hoja ' . $ws->getTitle());
    };

    $ws = $book->getSheetByName('Miembros');
    $maxRow = $ws->getHighestDataRow();
    $hr = $findHeaderRow($ws, array_keys($groups));
    $people = [];         // clave → persona
    $memberDupes = [];
    for ($c = 1; $c <= 30; $c++) {
        $g = nkey(cellValue($ws, $c, $hr));
        if (!isset($groups[$g])) continue;
        [$gender, $marital] = $groups[$g];
        $label = clean(cellValue($ws, $c, $hr));
        for ($r = $hr + 1; $r <= $maxRow; $r++) {
            $name = clean(cellValue($ws, $c, $r));
            if ($name === '') continue;
            $k = $memberAliases[nkey($name)] ?? nkey($name);
            if (isset($people[$k])) {
                $memberDupes[] = "'{$name}' aparece en '{$people[$k]['group']}' y en '{$label}' → se carga una sola vez como '{$people[$k]['name']}'";
                $people[$k]['group'] .= " / {$label}";
                continue;
            }
            $people[$k] = ['name' => $name, 'group' => $label, 'gender' => $gender, 'marital' => $marital, 'source' => 'Miembros'];
        }
    }

    // Parejas: "Apellido Nombre1 y Nombre2" (el segundo puede traer su propio apellido)
    $ws = $book->getSheetByName('Parejas');
    $maxRow = $ws->getHighestDataRow();
    $hr = $findHeaderRow($ws, ['nombres']);
    $cols = [];
    for ($c = 1; $c <= 30; $c++) {
        $cols[nkey(cellValue($ws, $c, $hr))] = $c;
    }
    $families = [];
    $coupleWarnings = [];
    for ($r = $hr + 1; $r <= $maxRow; $r++) {
        $raw = clean(cellValue($ws, $cols['nombres'], $r));
        if ($raw === '') continue;
        $cv = fn(string $name) => isset($cols[$name]) ? cellValue($ws, $cols[$name], $r) : null;
        $casados   = nkey($cv('casados'));
        $noBaut    = clean($cv('no bautizado (a)'));
        $restaurar = clean($cv('para restaurar'));
        $marital = match ($casados) { 'si' => 'casado', 'no' => 'union_libre', default => null };

        if (isset($coupleOverrides[$raw])) {
            [$surname, $parts] = $coupleOverrides[$raw];
        } else {
            [$surname, $rest] = array_pad(explode(' ', $raw, 2), 2, '');
            $parts = array_values(array_filter(array_map('clean', preg_split('/\s+(?:y|e)(?:\s+|$)/u', $rest))));
        }
        if (count($parts) !== 2) {
            $coupleWarnings[] = "fila {$r} '{$raw}': se esperaban 2 nombres, se tomaron " . count($parts) . ' → ' . implode(' | ', array_map(fn($p) => "{$p} {$surname}", $parts));
        }
        $famKeys = [];
        foreach ($parts as $i => $first) {
            // Si el segundo nombre trae apellido propio ("Nick Parson") se respeta
            $full = ($i > 0 && str_contains($first, ' ')) ? $first : "{$first} {$surname}";
            $k = nkey($full);
            if (isset($people[$k])) {
                $memberDupes[] = "'{$full}' (Parejas) ya estaba en '{$people[$k]['group']}' → se usa el mismo miembro";
                $people[$k]['marital'] ??= $marital;
            } else {
                $people[$k] = ['name' => $full, 'group' => 'Parejas', 'gender' => 'otro', 'marital' => $marital, 'source' => 'Parejas'];
            }
            $famKeys[] = $k;
        }
        $notes = array_filter([
            $casados === '' ? 'Estado civil no indicado' : null,
            $noBaut !== '' ? "No bautizado(a): {$noBaut}" : null,
            $restaurar !== '' ? 'Para restaurar' : null,
        ]);
        $families[] = ['name' => "Familia {$surname}", 'raw' => $raw, 'members' => $famKeys, 'notes' => implode('. ', $notes)];
    }

    // Posibles duplicados por ortografía (mismo nombre, apellido casi igual)
    $keys = array_keys($people);
    $similar = [];
    foreach ($keys as $i => $a) {
        foreach (array_slice($keys, $i + 1) as $b) {
            if ($a !== $b && levenshtein($a, $b) <= 2 && strlen($a) > 6) {
                $similar[] = "'{$people[$a]['name']}' ({$people[$a]['group']}) ≈ '{$people[$b]['name']}' ({$people[$b]['group']})";
            }
        }
    }

    // ─────────────────────────────────────────────────────────
    // 5. Ministerios: emparejar nombres con miembros
    // ─────────────────────────────────────────────────────────
    $ministryAlias = [
        'educacion' => 'educacion', 'evangelismo' => 'evangelismo', 'adoracion' => 'adoracion',
        'benevolencia' => 'benevolencia', 'mantenimiento' => 'mantenimiento', 'ministerio de ninos' => 'ninos',
        'ministerio juvenil' => 'jovenes', 'finanzas' => 'finanzas', 'comunicacion' => 'comunicacion', 'cumpleanos' => 'cumpleanos',
    ];
    $ministries = [];
    foreach ($db->query("SELECT id, name FROM ministries WHERE church_id = ? AND deleted_at IS NULL", [CHURCH_ID]) as $m) {
        $ministries[nkey($m['name'])] = ['id' => (int)$m['id'], 'name' => $m['name']];
    }

    $ws = $book->getSheetByName('Ministerios');
    $maxRow = $ws->getHighestDataRow();
    $hr = $findHeaderRow($ws, array_keys($ministryAlias));
    $assignments = [];   // [ministryName, rawName, role]
    for ($c = 1; $c <= 30; $c++) {
        $mk = nkey(cellValue($ws, $c, $hr));
        if (!isset($ministryAlias[$mk])) continue;
        $mName = clean(cellValue($ws, $c, $hr));
        for ($r = $hr + 1; $r <= $maxRow; $r++) {
            $raw = clean(cellValue($ws, $c, $r));
            if ($raw === '') continue;
            $role = $r === $hr + 1 ? 'coordinador' : 'miembro';
            // "Raúl y Betty" → dos personas
            foreach (preg_split('/\s+y\s+/u', $raw) as $part) {
                $assignments[] = ['ministry' => $mName, 'mkey' => $ministryAlias[$mk], 'raw' => $raw, 'name' => clean($part), 'role' => $role];
            }
        }
    }
    // Coordinadores primero: sus nombres suelen ser más completos y sirven para emparejar los apodos
    usort($assignments, fn($a, $b) => ($a['role'] === 'coordinador' ? 0 : 1) <=> ($b['role'] === 'coordinador' ? 0 : 1));

    $matchLog = [];
    $resolve = function (string $name) use (&$people, $ministryNameOverrides): array {
        $starred = str_contains($name, '*');
        $name = clean(str_replace('*', '', $name));
        $k = nkey($name);
        if (isset($people[$k])) return [$k, 'exacto', $starred];
        if (isset($ministryNameOverrides[$k], $people[$ministryNameOverrides[$k]])) {
            return [$ministryNameOverrides[$k], 'confirmado por el usuario', $starred];
        }

        $tokens = explode(' ', $k);
        $first  = $tokens[0];
        $rest   = rtrim(implode(' ', array_slice($tokens, 1)), '.');
        $cands = [];
        foreach ($people as $pk => $p) {
            $pt = explode(' ', $pk);
            if ($pt[0] !== $first) continue;
            $plast = implode(' ', array_slice($pt, 1));
            if ($rest === '' || str_starts_with($plast, $rest)) $cands[] = $pk;
        }
        if (count($cands) === 1) return [$cands[0], $rest === '' ? 'por nombre' : 'por inicial', $starred];
        if (count($cands) > 1)  return [null, 'AMBIGUO: ' . implode(' / ', array_map(fn($x) => $people[$x]['name'], $cands)), $starred];

        // Errores de tipeo en el nombre ("Donanciano" ≈ "Donaciano")
        if ($rest === '' && strlen($first) >= 5) {
            $fuzzy = array_keys(array_filter($people, fn($p, $pk) => levenshtein(explode(' ', $pk)[0], $first) === 1, ARRAY_FILTER_USE_BOTH));
            if (count($fuzzy) === 1) return [$fuzzy[0], 'APROXIMADO', $starred];
        }

        // Sin coincidencia → persona nueva
        $people[$k] = ['name' => $name, 'group' => 'Ministerios', 'gender' => 'otro', 'marital' => null, 'source' => 'Ministerios'];
        return [$k, 'NUEVO', $starred];
    };

    $pairs = [];
    foreach ($assignments as $a) {
        [$pk, $how, $starred] = $resolve($a['name']);
        $matchLog[] = sprintf('   %-14s %-12s %-22s → %-28s %s%s',
            $a['ministry'], $a['role'], "'{$a['name']}'", $pk ? $people[$pk]['name'] : '(sin asignar)', $how, $starred ? '  [*]' : '');
        if (!$pk) continue;
        $pairKey = $a['mkey'] . '|' . $pk;
        if (isset($pairs[$pairKey])) continue;   // ya asignado (coordinador tiene prioridad)
        $pairs[$pairKey] = ['mkey' => $a['mkey'], 'ministry' => $a['ministry'], 'person' => $pk, 'role' => $a['role']];
    }

    // ─────────────────────────────────────────────────────────
    // 6. Insertar miembros y asignaciones
    // ─────────────────────────────────────────────────────────
    $nextCode = (int)($db->queryOne(
        "SELECT MAX(CAST(SUBSTRING(code, 4) AS UNSIGNED)) AS n FROM members WHERE church_id = ?" . ($keepTest ? '' : " AND id NOT IN (" . implode(',', array_keys($testData['members'])) . ")"),
        [CHURCH_ID]
    )['n'] ?? 0) + 1;

    $memberIds = [];
    $firstMinistry = [];
    foreach ($pairs as $p) {
        $firstMinistry[$p['person']] ??= $p['mkey'];
    }

    // Ministerio "Finanzas" si no existe
    foreach (array_unique(array_column($pairs, 'mkey')) as $mkey) {
        if (!isset($ministries[$mkey])) {
            $label = array_values(array_filter($pairs, fn($p) => $p['mkey'] === $mkey))[0]['ministry'];
            $ministries[$mkey] = ['id' => insert('ministries', ['church_id' => CHURCH_ID, 'name' => $label, 'is_active' => 1]), 'name' => $label];
            out();
            out("   Ministerio nuevo: {$label}");
        }
    }

    foreach ($people as $pk => $p) {
        $parts = explode(' ', $p['name'], 2);
        $memberIds[$pk] = insert('members', [
            'church_id'       => CHURCH_ID,
            'uuid'            => Uuid::uuid4()->toString(),
            'code'            => 'MBR' . str_pad((string)$nextCode++, 5, '0', STR_PAD_LEFT),
            'first_name'      => mb_substr($parts[0], 0, 80),
            'last_name'       => mb_substr($parts[1] ?? '', 0, 80),
            'gender'          => $p['gender'],
            'marital_status'  => $p['marital'],
            'document_type'   => null,
            'status'          => 'activo',
            'ministry_id'     => isset($firstMinistry[$pk]) ? $ministries[$firstMinistry[$pk]]['id'] : null,
            'pastoral_notes'  => MARK . "Miembros y Ministerios.xlsx — grupo: {$p['group']}",
            'created_by'      => ADMIN_ID,
        ]);
    }

    foreach ($families as $f) {
        $famId = insert('families', [
            'church_id' => CHURCH_ID,
            'name'      => $f['name'],
            'notes'     => trim(MARK . "Parejas '{$f['raw']}'. " . $f['notes']),
        ]);
        foreach (array_unique($f['members']) as $k) {
            insert('family_members', ['family_id' => $famId, 'member_id' => $memberIds[$k], 'relationship' => 'conyuge']);
        }
    }

    $leaders = [];
    foreach ($pairs as $p) {
        $mid = $ministries[$p['mkey']]['id'];
        insert('ministry_members', [
            'ministry_id' => $mid,
            'member_id'   => $memberIds[$p['person']],
            'role'        => $p['role'],
            'is_active'   => 1,
        ]);
        if ($p['role'] === 'coordinador' && !isset($leaders[$mid])) {
            $leaders[$mid] = true;
            write("UPDATE ministries SET leader_id = ? WHERE id = ? AND church_id = ?", [$memberIds[$p['person']], $mid, CHURCH_ID]);
        }
    }

    $bySource = array_count_values(array_column($people, 'source'));
    out();
    out('── 6. Miembros y familias ──');
    out(sprintf('   Hoja Miembros: %d | Hoja Parejas: %d | Nuevas desde Ministerios: %d | Total a crear: %d',
        $bySource['Miembros'] ?? 0, $bySource['Parejas'] ?? 0, $bySource['Ministerios'] ?? 0, count($people)));
    out('   Familias (una por pareja): ' . count($families));
    $marital = array_count_values(array_map(fn($p) => $p['marital'] ?? 'sin dato', array_filter($people, fn($p) => $p['source'] === 'Parejas')));
    out('   Estado civil en Parejas: ' . json_encode($marital, JSON_UNESCAPED_UNICODE));
    foreach ($coupleWarnings as $l) out('   ⚠ Pareja: ' . $l);
    foreach ($memberDupes as $l) out('   Duplicado: ' . $l);
    foreach ($similar as $l) out('   ⚠ ¿Misma persona?: ' . $l);
    $noLast = array_filter($people, fn($p) => !str_contains($p['name'], ' '));
    out('   Sin apellido (' . count($noLast) . '): ' . implode(', ', array_map(fn($p) => $p['name'], $noLast)));
    $noGender = array_filter($people, fn($p) => $p['gender'] === 'otro');
    out('   Sin género conocido → \'otro\' (' . count($noGender) . '): ' . implode(', ', array_map(fn($p) => $p['name'], $noGender)));
    $newFromMin = array_filter($people, fn($p) => $p['source'] === 'Ministerios');
    out('   Creadas desde Ministerios (no estaban en ninguna lista): ' . implode(', ', array_map(fn($p) => $p['name'], $newFromMin)));

    out();
    out('── 7. Emparejamiento Ministerios → Miembros (' . count($pairs) . ' asignaciones) ──');
    foreach ($matchLog as $l) out($l);
    out('   [*] = nombre marcado con asterisco en el Excel');

    if ($commit) {
        $db->commit();
        out();
        out('✔ COMMIT realizado.');
    } else {
        out();
        out('Simulacro terminado. No se escribió nada en la base de datos.');
        out('Para importar: php database/import_excel_2026.php --commit');
    }
} catch (Throwable $e) {
    if ($commit) {
        $db->rollback();
        out('✖ ERROR — se revirtió la transacción completa.');
    }
    out((string)$e);
    exit(1);
}
