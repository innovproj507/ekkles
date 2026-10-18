<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calendario de Eventos - <?= e($monthName) ?> <?= $year ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            font-size: 13px; 
            color: #1f2937; 
            line-height: 1.5;
            background-color: #f9fafb;
        }
        .page-container {
            width: 100%;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
        }
        
        /* Encabezado */
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 3px solid #6366f1;
            margin-bottom: 30px;
        }
        .church-name {
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #6b7280;
            margin-bottom: 5px;
        }
        .month-title {
            font-size: 28px;
            font-weight: 800;
            color: #4338ca;
            text-transform: uppercase;
        }

        /* Días vacíos */
        .no-events {
            text-align: center;
            padding: 40px 20px;
            background: #f3f4f6;
            border-radius: 12px;
            color: #6b7280;
            font-size: 14px;
        }

        /* Lista de Eventos */
        .date-group {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .date-header {
            font-size: 16px;
            font-weight: bold;
            color: #111827;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
            margin-bottom: 15px;
            display: inline-block;
            width: 100%;
        }
        .date-header .day-name {
            color: #6366f1;
            margin-right: 5px;
        }

        .event-card {
            background: #ffffff;
            border-left: 4px solid #6366f1;
            border: 1px solid #e5e7eb;
            border-left-width: 4px;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 10px;
            display: table;
            width: 100%;
            page-break-inside: avoid;
        }
        
        .event-time-col {
            display: table-cell;
            width: 80px;
            vertical-align: top;
            font-weight: bold;
            color: #4b5563;
        }
        .event-time {
            font-size: 15px;
            color: #111827;
        }
        .event-info-col {
            display: table-cell;
            vertical-align: top;
        }
        
        .event-title {
            font-size: 15px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 4px;
        }
        .event-type {
            display: inline-block;
            font-size: 10px;
            text-transform: uppercase;
            background: #eef2ff;
            color: #4338ca;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: bold;
            margin-bottom: 6px;
        }
        .event-location {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }
        .event-description {
            font-size: 12px;
            color: #4b5563;
            margin-top: 6px;
            border-top: 1px dashed #e5e7eb;
            padding-top: 6px;
        }

        /* Pie de página */
        .footer {
            text-align: center;
            color: #9ca3af;
            font-size: 10px;
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }
        
        /* Funciones de color de tipo de evento */
        .type-culto { border-left-color: #10b981; }
        .type-culto .event-type { background: #d1fae5; color: #047857; }
        
        .type-reunion { border-left-color: #3b82f6; }
        .type-reunion .event-type { background: #dbeafe; color: #1d4ed8; }
        
        .type-conferencia { border-left-color: #8b5cf6; }
        .type-conferencia .event-type { background: #ede9fe; color: #6d28d9; }
        
        .type-especial { border-left-color: #f59e0b; }
        .type-especial .event-type { background: #fef3c7; color: #b45309; }

        /* Eventos con aporte de comida de las familias */
        .event-card.food { background: #fff7ed; border-left: 6px solid #f97316 !important; }
        .food-badge { display: inline-block; background: #ffedd5; color: #c2410c; font-weight: bold; font-size: 10px; padding: 2px 6px; border-radius: 4px; margin-left: 4px; }
    </style>
</head>
<body>
<div class="page-container">
    <!-- Encabezado -->
    <div class="header">
        <?php if (!empty($logoBase64)): ?>
            <div style="margin-bottom: 10px;">
                <img src="<?= $logoBase64 ?>" alt="Logo Iglesia" style="max-height: 80px; max-width: 250px;">
            </div>
        <?php else: ?>
            <div class="church-name"><?= e($church['name'] ?? 'Iglesia') ?></div>
        <?php endif; ?>
        <div class="month-title">Calendario <?= e($monthName) ?> <?= $year ?></div>
        <?php if (!empty($church['slogan'])): ?>
            <p style="color: #6b7280; font-style: italic; font-size: 11px; margin-top: 5px;"><?= e($church['slogan']) ?></p>
        <?php endif; ?>
    </div>

    <!-- Lista de eventos agrupados -->
    <?php if (empty($groupedEvents)): ?>
        <div class="no-events">
            <p>No hay eventos programados para este mes.</p>
        </div>
    <?php else: ?>
        <?php foreach ($groupedEvents as $date => $dayEvents): 
            $timestamp = strtotime($date);
            $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            $dayName = $dias[date('w', $timestamp)];
            $dayNum = date('d', $timestamp);
        ?>
            <div class="date-group">
                <div class="date-header">
                    <span class="day-name"><?= $dayName ?></span> <?= $dayNum ?> de <?= e($monthName) ?>
                </div>

                <?php foreach ($dayEvents as $event): 
                    $typeClass = 'type-' . strtolower($event['type']);
                    $time = date('H:i', strtotime($event['start_datetime']));
                ?>
                    <div class="event-card <?= $typeClass ?><?= !empty($event['food_contribution']) ? ' food' : '' ?>" style="<?= ($event['ministry_color'] && empty($event['food_contribution'])) ? 'border-left-color: ' . e($event['ministry_color']) . ';' : '' ?>">
                        <div class="event-time-col">
                            <div class="event-time"><?= $time ?></div>
                        </div>
                        <div class="event-info-col">
                            <div class="event-title"><?= e($event['title']) ?></div>
                            <span class="event-type" style="background-color: <?= e($event['type_color'] ?? '#6366f1') ?>22; color: <?= e($event['type_color'] ?? '#6366f1') ?>;">
                                <?= e($event['type']) ?>
                            </span>
                            
                            <?php if ($event['ministry_name']): ?>
                                <span class="event-type" style="background: #f3f4f6; color: #4b5563; margin-left: 4px;">
                                    <?= e($event['ministry_name']) ?>
                                </span>
                            <?php endif; ?>

                            <?php if (!empty($event['food_contribution'])): ?>
                                <span class="food-badge">APORTE DE COMIDA</span>
                            <?php endif; ?>

                            <?php if ($event['room_name'] || $event['location']): ?>
                                <div class="event-location">
                                    📍 <?= e(trim(($event['room_name'] ?? '') . ($event['room_name'] && $event['location'] ? ' · ' : '') . ($event['location'] ?? ''))) ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($event['description']): ?>
                                <div class="event-description">
                                    <?= nl2br(e($event['description'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Pie de página -->
    <div class="footer">
        <p>Generado el <?= date('d/m/Y H:i') ?> · Sistema Ekkles</p>
    </div>
</div>
</body>
</html>
