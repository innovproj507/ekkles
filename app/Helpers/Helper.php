<?php
/**
 * Helper.php — Funciones Globales de Ayuda
 * -----------------------------------------
 * Funciones de utilidad disponibles globalmente en la aplicación.
 *
 * @package App\Helpers
 */

// ============================================================
// FORMATEO
// ============================================================

/**
 * Formatear un número como USD (o la configurada)
 */
function formatCurrency(float $amount, string $currency = 'USD'): string
{
    $formatted = number_format($amount, 2, '.', ',');
    return "$ {$formatted} {$currency}";
}

/**
 * Formatear una fecha en español
 */
function formatDate(?string $date, string $format = 'd/m/Y'): string
{
    if (!$date) return '—';
    return date($format, strtotime($date));
}

/**
 * Formatear fecha y hora en español
 */
function formatDateTime(?string $datetime): string
{
    if (!$datetime) return '—';
    return date('d/m/Y H:i', strtotime($datetime));
}

/**
 * Calcular la edad a partir de la fecha de nacimiento
 */
function calcAge(?string $birthDate): ?int
{
    if (!$birthDate) return null;
    return (int)date_diff(date_create($birthDate), date_create('today'))->y;
}

/**
 * Obtener iniciales de un nombre completo
 */
function initials(string $name): string
{
    $words = explode(' ', trim($name));
    $initials = '';
    foreach (array_slice($words, 0, 2) as $word) {
        $initials .= mb_strtoupper(mb_substr($word, 0, 1));
    }
    return $initials;
}

/**
 * Truncar texto a una longitud máxima
 */
function truncate(string $text, int $length = 100, string $suffix = '...'): string
{
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . $suffix;
}

// ============================================================
// SEGURIDAD
// ============================================================

/**
 * Escapar HTML para prevenir XSS
 */
function e(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Obtener la IP real del cliente
 */
function request_ip(): string
{
    return $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['HTTP_X_REAL_IP']
        ?? $_SERVER['REMOTE_ADDR']
        ?? '0.0.0.0';
}

/**
 * Generar un slug a partir de un texto
 */
function slugify(string $text): string
{
    $text = mb_strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

// ============================================================
// RUTAS Y URLS
// ============================================================

/**
 * Generar una URL relativa a la raíz de la aplicación
 */
function url(string $path = ''): string
{
    $config = require dirname(__DIR__, 2) . '/config/app.php';
    $base = rtrim($config['url'] ?? '', '/');
    return $base . '/' . ltrim($path, '/');
}

/**
 * Obtener la URL del archivo de perfil del usuario/miembro
 */
function avatarUrl(?string $photo, string $name = 'U'): string
{
    if ($photo && file_exists(dirname(__DIR__, 2) . '/public/uploads/' . $photo)) {
        return url('uploads/' . $photo);
    }
    // Generar avatar con iniciales usando UI Avatars
    $initials = urlencode(initials($name) ?: 'U');
    return "https://ui-avatars.com/api/?name={$initials}&background=6366f1&color=fff&size=128&bold=true";
}

// ============================================================
// ETIQUETAS Y ESTADOS
// ============================================================

/**
 * Obtener la clase CSS de Tailwind para un estado de miembro
 */
function memberStatusBadge(string $status): string
{
    return match($status) {
        'activo'        => 'bg-emerald-100 text-emerald-800',
        'inactivo'      => 'bg-red-100 text-red-800',
        'visitante'     => 'bg-blue-100 text-blue-800',
        'nuevo_creyente'=> 'bg-purple-100 text-purple-800',
        'trasladado'    => 'bg-yellow-100 text-yellow-800',
        'fallecido'     => 'bg-gray-100 text-gray-500',
        default         => 'bg-gray-100 text-gray-600',
    };
}

/**
 * Obtener la etiqueta en español de un estado de miembro
 */
function memberStatusLabel(string $status): string
{
    return match($status) {
        'activo'        => 'Activo',
        'inactivo'      => 'Inactivo',
        'visitante'     => 'Visitante',
        'nuevo_creyente'=> 'Nuevo Creyente',
        'trasladado'    => 'Trasladado',
        'fallecido'     => 'Fallecido',
        default         => ucfirst($status),
    };
}

/**
 * Obtener la etiqueta de una etapa del pipeline pastoral
 */
function pipelineStageLabel(string $stage): string
{
    return match($stage) {
        'visitante'   => 'Visitante',
        'contactado'  => 'Contactado',
        'integrado'   => 'Integrado',
        'discipulado' => 'En Discipulado',
        'miembro'     => 'Miembro',
        default       => ucfirst($stage),
    };
}

/**
 * Obtener la etiqueta de un tipo de donación
 */
function donationTypeLabel_old(string $type): string
{
    return match($type) {
        'diezmo'           => 'Ofrenda',
        'ofrenda_general'  => 'Ofrenda General',
        'ofrenda_especial' => 'Ofrenda Especial',
        'promesa'          => 'Promesa de Fe',
        'donacion'         => 'Donación',
        'construccion'     => 'Construcción',
        'misiones'         => 'Misiones',
        'proyecto'         => 'Proyecto Especial',
        default            => ucfirst(str_replace('_', ' ', $type)),
    };
}

/**
 * Obtener la etiqueta del método de pago
 */
function paymentMethodLabel(string $method): string
{
    return match($method) {
        'efectivo'     => 'Efectivo',
        'transferencia'=> 'Transferencia',
        'tarjeta'      => 'Tarjeta',
        'pago_movil'   => 'Pago Móvil',
        'qr'           => 'QR',
        'cheque'       => 'Cheque',
        default        => ucfirst($method),
    };
}

/**
 * Meses en español
 */
function monthName(int $month): string
{
    $months = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];
    return $months[$month] ?? '';
}
