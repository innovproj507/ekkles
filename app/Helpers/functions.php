<?php
/**
 * functions.php — Funciones Globales de Ayuda para Vistas
 * ----------------------------------------------------------
 * Atajos de formato, etiquetas y URLs usadas en todas las vistas.
 * Se carga automáticamente vía autoload.files de Composer.
 *
 * @package Ekkles
 */

if (!function_exists('e')) {
    /**
     * Escapar HTML (XSS prevention)
     */
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('url')) {
    /**
     * Generar URL absoluta a la raíz de la aplicación
     */
    function url(string $path = ''): string
    {
        $base = rtrim($_ENV['APP_URL'] ?? '', '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('avatarUrl')) {
    /**
     * URL del avatar de un miembro (foto o inicial generada)
     */
    function avatarUrl(?string $photo, string $name): string
    {
        if ($photo) {
            $base = rtrim($_ENV['APP_URL'] ?? '', '/');
            return $base . '/uploads/members/' . $photo;
        }
        // Avatar basado en iniciales via UI Avatars
        $encoded = urlencode(substr($name, 0, 2));
        return "https://ui-avatars.com/api/?name={$encoded}&background=4338ca&color=ffffff&size=128&bold=true&format=webp";
    }
}

if (!function_exists('formatCurrency')) {
    /**
     * Formatear monto monetario en USD
     */
    function formatCurrency(float $amount, string $currency = 'USD'): string
    {
        return '$' . number_format($amount, 2, '.', ',') . ' ' . $currency;
    }
}

if (!function_exists('formatDate')) {
    /**
     * Formatear fecha en español
     */
    function formatDate(?string $date): string
    {
        if (!$date || $date === '0000-00-00') return '—';
        try {
            $d = new \DateTime($date);
            return $d->format('d/m/Y');
        } catch (\Exception $e) {
            return '—';
        }
    }
}

if (!function_exists('formatDateTime')) {
    /**
     * Formatear fecha y hora en español
     */
    function formatDateTime(?string $datetime): string
    {
        if (!$datetime || $datetime === '0000-00-00 00:00:00') return '—';
        try {
            $d = new \DateTime($datetime);
            return $d->format('d/m/Y H:i');
        } catch (\Exception $e) {
            return '—';
        }
    }
}

if (!function_exists('calcAge')) {
    /**
     * Calcular edad a partir de fecha de nacimiento
     */
    function calcAge(?string $birthDate): int
    {
        if (!$birthDate) return 0;
        try {
            $birth = new \DateTime($birthDate);
            $now   = new \DateTime();
            return (int)$now->diff($birth)->y;
        } catch (\Exception $e) {
            return 0;
        }
    }
}

if (!function_exists('truncate')) {
    /**
     * Truncar texto largo con puntos suspensivos
     */
    function truncate(?string $text, int $length = 50, string $suffix = '...'): string
    {
        if (!$text) return '';
        return mb_strlen($text) > $length
            ? mb_substr($text, 0, $length) . $suffix
            : $text;
    }
}

if (!function_exists('memberStatusLabel')) {
    /**
     * Etiqueta legible del estado de un miembro
     */
    function memberStatusLabel(string $status): string
    {
        return match($status) {
            'activo'         => 'Activo',
            'inactivo'       => 'Inactivo',
            'visitante'      => 'Visitante',
            'nuevo_creyente' => 'Nuevo Creyente',
            'trasladado'     => 'Trasladado',
            'fallecido'      => 'Fallecido',
            default          => ucfirst($status),
        };
    }
}

if (!function_exists('memberStatusBadge')) {
    /**
     * Clases Tailwind del badge de estado
     */
    function memberStatusBadge(string $status): string
    {
        return match($status) {
            'activo'         => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
            'inactivo'       => 'bg-red-500/10 text-red-400 border border-red-500/20',
            'visitante'      => 'bg-blue-500/10 text-blue-400 border border-blue-500/20',
            'nuevo_creyente' => 'bg-purple-500/10 text-purple-400 border border-purple-500/20',
            'trasladado'     => 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
            'fallecido'      => 'bg-gray-500/10 text-gray-400 border border-gray-500/20',
            default          => 'bg-gray-500/10 text-gray-400',
        };
    }
}

if (!function_exists('donationTypeLabel')) {
    /**
     * Etiqueta legible del tipo de donación
     */
    function donationTypeLabel(string $type): string
    {
        return match($type) {
            'diezmo'            => 'Ofrenda',
            'ofrenda_general'   => 'Ofrenda General',
            'ofrenda_especial'  => 'Ofrenda Especial',
            'primicias'         => 'Primicias',
            'amor_y_fe'         => 'Amor y Fe',
            'misionero'         => 'Fondo Misionero',
            'construccion'      => 'Construcción',
            default             => ucfirst(str_replace('_', ' ', $type)),
        };
    }
}

if (!function_exists('paymentMethodLabel')) {
    /**
     * Etiqueta legible del método de pago
     */
    function paymentMethodLabel(string $method): string
    {
        return match($method) {
            'efectivo'       => 'Efectivo',
            'transferencia'  => 'Transferencia',
            'tarjeta'        => 'Tarjeta',
            'cheque'         => 'Cheque',
            'qr_nequi'       => 'Nequi QR',
            'qr_daviplata'   => 'Daviplata QR',
            'otro'           => 'Otro',
            default          => ucfirst($method),
        };
    }
}

if (!function_exists('monthName')) {
    /**
     * Nombre del mes en español (1-12)
     */
    function monthName(int $month): string
    {
        $months = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic',
        ];
        return $months[$month] ?? '';
    }
}

if (!function_exists('pipelineStageLabel')) {
    /**
     * Etiqueta legible de la etapa del pipeline pastoral
     */
    function pipelineStageLabel(string $stage): string
    {
        return match($stage) {
            'visitante'   => 'Visitante',
            'contactado'  => 'Contactado',
            'integrado'   => 'Integrado',
            'discipulado' => 'Discipulado',
            'miembro'     => 'Miembro',
            default       => ucfirst($stage),
        };
    }
}

if (!function_exists('request_ip')) {
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
}
