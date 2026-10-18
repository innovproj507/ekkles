<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #1f2937; }
        .receipt { width: 100%; max-width: 420px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding-bottom: 16px; border-bottom: 2px solid #6366f1; margin-bottom: 16px; }
        .church-name { font-size: 18px; font-weight: bold; color: #4338ca; margin-bottom: 4px; }
        .receipt-title { font-size: 13px; color: #6b7280; font-weight: normal; }
        .receipt-number { display: inline-block; background: #eef2ff; color: #4338ca; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; margin-top: 8px; }
        .info-grid { display: table; width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; padding: 6px 8px; background: #f9fafb; color: #6b7280; font-weight: 600; width: 40%; border-bottom: 1px solid #e5e7eb; }
        .info-value { display: table-cell; padding: 6px 8px; color: #111827; border-bottom: 1px solid #e5e7eb; }
        .amount-box { background: linear-gradient(135deg, #4338ca, #7c3aed); color: white; border-radius: 12px; padding: 16px; text-align: center; margin: 16px 0; }
        .amount-label { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.85; margin-bottom: 4px; }
        .amount-value { font-size: 28px; font-weight: bold; }
        .footer { text-align: center; color: #9ca3af; font-size: 9px; margin-top: 20px; padding-top: 12px; border-top: 1px dashed #e5e7eb; }
        .seal { display: inline-block; border: 2px solid #10b981; color: #10b981; border-radius: 50%; width: 60px; height: 60px; line-height: 56px; text-align: center; font-weight: bold; font-size: 9px; margin: 8px 0; }
        .blessing { font-style: italic; color: #6b7280; font-size: 10px; margin-top: 8px; }
    </style>
</head>
<body>
<div class="receipt">
    <!-- Encabezado -->
    <div class="header">
        <?php if (!empty($logoBase64)): ?>
            <div style="margin-bottom: 8px;">
                <img src="<?= $logoBase64 ?>" alt="Logo Iglesia" style="max-height: 60px; max-width: 200px;">
            </div>
        <?php else: ?>
            <div class="church-name"><?= e($church['name'] ?? 'Iglesia Ekkles') ?></div>
        <?php endif; ?>
        <?php if (!empty($church['slogan'])): ?>
            <div style="font-size: 10px; color: #6b7280; font-style: italic; margin-bottom: 4px;"><?= e($church['slogan']) ?></div>
        <?php endif; ?>
        <div class="receipt-title">Comprobante de Donación</div>
        <div class="receipt-number"># <?= e($donation['receipt_number']) ?></div>
    </div>

    <!-- Información del donante -->
    <div class="info-grid">
        <div class="info-row">
            <div class="info-label">Donante</div>
            <div class="info-value">
                <?= $donation['is_anonymous']
                    ? 'Anónimo'
                    : e($donation['full_donor_name'] ?? $donation['donor_name'] ?? 'No especificado') ?>
            </div>
        </div>
        <?php if (!empty($donation['ruc']) && !$donation['is_anonymous']): ?>
        <div class="info-row">
            <div class="info-label">RUC / Doc</div>
            <div class="info-value"><?= e($donation['ruc']) ?></div>
        </div>
        <?php endif; ?>
        <div class="info-row">
            <div class="info-label">Categoría</div>
            <div class="info-value"><?= e($donation['category_name'] ?? donationTypeLabel($donation['type'])) ?></div>
        </div>
        <?php if (!empty($donation['detail'])): ?>
        <div class="info-row">
            <div class="info-label">Detalle</div>
            <div class="info-value"><?= e($donation['detail']) ?></div>
        </div>
        <?php endif; ?>
        <div class="info-row">
            <div class="info-label">Método</div>
            <div class="info-value"><?= paymentMethodLabel($donation['payment_method']) ?></div>
        </div>
        <?php if ($donation['reference']): ?>
        <div class="info-row">
            <div class="info-label">Referencia</div>
            <div class="info-value"><?= e($donation['reference']) ?></div>
        </div>
        <?php endif; ?>
        <div class="info-row">
            <div class="info-label">Fecha</div>
            <div class="info-value"><?= formatDate($donation['donation_date']) ?></div>
        </div>
    </div>

    <!-- Monto -->
    <div class="amount-box">
        <div class="amount-label">Valor Donado</div>
        <div class="amount-value"><?= formatCurrency((float)$donation['amount']) ?></div>
    </div>

    <!-- Sello de confirmación -->
    <div style="text-align:center; margin: 12px 0;">
        <div class="seal">VÁLIDO</div>
        <div class="blessing">"El que siembra escasamente, escasamente cosechará." — 2 Corintios 9:6</div>
    </div>

    <!-- Pie de página -->
    <div class="footer">
        <p>Este recibo es válido como comprobante de su donación a la iglesia.</p>
        <p style="margin-top:4px;">Generado el <?= date('d/m/Y H:i') ?> · Sistema Ekkles</p>
    </div>
</div>
</body>
</html>
