<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Egresos</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        @page {
            margin: 30px 40px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-logo {
            font-size: 22px;
            font-weight: bold;
            color: #4f46e5;
            letter-spacing: 1px;
        }
        .header-title {
            text-align: right;
            font-size: 16px;
            font-weight: bold;
            color: #111827;
        }
        .header-meta {
            text-align: right;
            font-size: 9px;
            color: #6b7280;
            margin-top: 5px;
        }
        .divider {
            border-top: 2px solid #e5e7eb;
            margin-bottom: 15px;
        }
        .filters-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 20px;
        }
        .filters-title {
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            color: #4b5563;
            margin-bottom: 4px;
        }
        .filters-content {
            font-size: 10px;
            color: #1f2937;
        }
        .kpis-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .kpi-card {
            background-color: #fef2f2;
            border: 1px solid #fca5a5;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            width: 31%;
        }
        .kpi-title {
            font-size: 9px;
            color: #991b1b;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .kpi-value {
            font-size: 18px;
            font-weight: bold;
            color: #7f1d1d;
        }
        .kpi-desc {
            font-size: 8px;
            color: #b91c1c;
            margin-top: 4px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            padding: 8px 10px;
            border-bottom: 2px solid #d1d5db;
            text-align: left;
        }
        .data-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 9px;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f9fafb;
        }
        .data-table .amount-col {
            text-align: right;
            font-weight: bold;
            color: #dc2626;
        }
        .data-table .total-row td {
            font-weight: bold;
            font-size: 10px;
            background-color: #f3f4f6 !important;
            border-top: 2px solid #9ca3af;
            border-bottom: 2px solid #9ca3af;
            color: #111827;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-ofrendas {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-construccion {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-donaciones {
            background-color: #f3e8ff;
            color: #6b21a8;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
            border-top: 1px solid #f3f4f6;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                Ekkles
                <div style="font-size: 10px; font-weight: normal; color: #6b7280; margin-top: 2px;">
                    <?= e($church_name) ?>
                </div>
            </td>
            <td>
                <div class="header-title">Reporte de Egresos</div>
                <div class="header-meta">
                    Generado el: <?= date('d/m/Y H:i') ?><br>
                    Moneda: USD (Dólares Americanos)
                </div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- Filtros Aplicados -->
    <div class="filters-box">
        <div class="filters-title">Filtros aplicados</div>
        <div class="filters-content"><?= e($filtersDesc) ?></div>
    </div>

    <!-- KPIs -->
    <?php
    $total = 0;
    $count = count($records);
    foreach ($records as $r) {
        $total += (float)$r['amount'];
    }
    ?>
    <table class="kpis-table">
        <tr>
            <td class="kpi-card">
                <div class="kpi-title">Total de Egresos</div>
                <div class="kpi-value"><?= formatCurrency($total) ?></div>
                <div class="kpi-desc"><?= $count ?> transacciones incluidas</div>
            </td>
            <td style="width: 3%;">&nbsp;</td>
            <td class="kpi-card" style="background-color: #f0fdf4; border-color: #bbf7d0;">
                <div class="kpi-title" style="color: #166534;">Fecha Inicial</div>
                <div class="kpi-value" style="color: #14532d; font-size: 15px;">
                    <?= !empty($records) ? formatDate($records[count($records)-1]['transaction_date']) : '—' ?>
                </div>
            </td>
            <td style="width: 3%;">&nbsp;</td>
            <td class="kpi-card" style="background-color: #eff6ff; border-color: #bfdbfe;">
                <div class="kpi-title" style="color: #1e40af;">Fecha Final</div>
                <div class="kpi-value" style="color: #1e3a8a; font-size: 15px;">
                    <?= !empty($records) ? formatDate($records[0]['transaction_date']) : '—' ?>
                </div>
            </td>
        </tr>
    </table>

    <!-- Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%">#</th>
                <th style="width: 10%">Fecha</th>
                <th style="width: 12%">Factura</th>
                <th style="width: 12%">RUC/Cédula</th>
                <th style="width: 15%">Proveedor</th>
                <th style="width: 20%">Detalle del Gasto</th>
                <th style="width: 10%">Categoría</th>
                <th style="width: 10%">Módulo</th>
                <th style="width: 12%" class="amount-col">Monto</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($records)): ?>
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px; color: #6b7280;">
                    No se encontraron transacciones de egreso con los filtros aplicados.
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($records as $index => $r): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= formatDate($r['transaction_date']) ?></td>
                <td><?= e($r['invoice_number'] ?? 'N/A') ?></td>
                <td><?= e($r['provider_ruc'] ?? 'N/A') ?></td>
                <td><?= e($r['provider_name'] ?? 'N/A') ?></td>
                <td><?= e($r['description']) ?></td>
                <td><?= e($r['category_name']) ?></td>
                <td>
                    <?php if (($r['target_module'] ?? '') === 'ofrendas'): ?>
                    <span class="badge badge-ofrendas">Ofrendas</span>
                    <?php elseif (($r['target_module'] ?? '') === 'construccion'): ?>
                    <span class="badge badge-construccion">Construcción</span>
                    <?php elseif (($r['target_module'] ?? '') === 'donaciones'): ?>
                    <span class="badge badge-donaciones">Obras Misioneras</span>
                    <?php else: ?>
                    <span class="badge" style="background-color: #e5e7eb; color: #374151;">N/A</span>
                    <?php endif; ?>
                </td>
                <td class="amount-col">-<?= formatCurrency((float)$r['amount']) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="8" style="text-align: right;">Total Acumulado:</td>
                <td class="amount-col">-<?= formatCurrency($total) ?></td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        Este documento es un reporte financiero oficial generado por el sistema Ekkles. Información confidencial de auditoría interna.
    </div>

</body>
</html>
