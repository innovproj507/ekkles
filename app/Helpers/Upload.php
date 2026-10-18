<?php
/**
 * Upload.php — Gestión de Subida de Archivos
 * -------------------------------------------
 * Helper para subir y validar archivos (fotos de miembros, etc.)
 *
 * @package App\Helpers
 */

namespace App\Helpers;

class Upload
{
    /** @var array Configuración de subida */
    private array $config;

    public function __construct()
    {
        $appConfig    = require dirname(__DIR__, 2) . '/config/app.php';
        $this->config = $appConfig['upload'];
    }

    /**
     * Subir una imagen de perfil de miembro
     *
     * @param array $file   Elemento de $_FILES
     * @param string $folder Subcarpeta dentro de uploads/
     * @return array ['success' => bool, 'filename' => string, 'error' => string]
     */
    public function uploadImage(array $file, string $folder = 'members'): array
    {
        // Verificar errores de subida
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => $this->uploadErrorMessage($file['error'])];
        }

        // Verificar tamaño
        if ($file['size'] > $this->config['max_size']) {
            $maxMb = round($this->config['max_size'] / 1048576, 1);
            return ['success' => false, 'error' => "El archivo no puede superar {$maxMb}MB."];
        }

        // Verificar tipo MIME real (no confiar en la extensión del cliente)
        $mimeType = mime_content_type($file['tmp_name']);
        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];

        if (!isset($allowedMimes[$mimeType])) {
            return ['success' => false, 'error' => 'Solo se permiten imágenes JPG, PNG, GIF o WebP.'];
        }

        // Generar nombre único
        $extension = $allowedMimes[$mimeType];
        $filename   = uniqid('img_', true) . '.' . $extension;

        // Crear directorio si no existe
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/' . $folder . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destination = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'error' => 'Error al mover el archivo. Verifique los permisos.'];
        }

        return [
            'success'  => true,
            'filename' => $folder . '/' . $filename,
            'error'    => '',
        ];
    }

    /** Carpeta privada de recibos (fuera de public/, solo accesible vía controlador) */
    public const RECEIPTS_DIR = 'storage/uploads/recibos';

    /**
     * Subir el recibo de un egreso (PDF o imagen), ordenado por fecha:
     * storage/uploads/recibos/AAAA/MM/AAAA-MM-DD_<referencia>_<único>.<ext>
     *
     * @param string $date      Fecha del egreso (Y-m-d): define carpeta y prefijo del nombre
     * @param string $reference Texto para identificar el archivo (proveedor, factura…)
     * @return array ['success' => bool, 'path' => string, 'name' => string, 'size' => int, 'error' => string]
     */
    public function uploadReceipt(array $file, string $date, string $reference = ''): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => $this->uploadErrorMessage($file['error'])];
        }

        $maxSize = max((int)$this->config['max_size'], 10 * 1048576); // recibos: hasta 10MB
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'error' => 'El recibo no puede superar ' . round($maxSize / 1048576) . 'MB.'];
        }

        $allowedMimes = [
            'application/pdf' => 'pdf',
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'image/webp'      => 'webp',
        ];
        $mimeType = mime_content_type($file['tmp_name']);
        if (!isset($allowedMimes[$mimeType])) {
            return ['success' => false, 'error' => 'El recibo debe ser PDF, JPG, PNG o WebP.'];
        }

        $ts   = strtotime($date) ?: time();
        $slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $reference) ?: '')), '-');
        $name = date('Y-m-d', $ts) . ($slug !== '' ? '_' . substr($slug, 0, 40) : '') . '_' . bin2hex(random_bytes(4)) . '.' . $allowedMimes[$mimeType];
        $relative = date('Y', $ts) . '/' . date('m', $ts) . '/' . $name;

        $dir = dirname(__DIR__, 2) . '/' . self::RECEIPTS_DIR . '/' . dirname($relative);
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            return ['success' => false, 'error' => 'No se pudo crear la carpeta de recibos. Verifique los permisos.'];
        }

        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
            return ['success' => false, 'error' => 'Error al guardar el recibo. Verifique los permisos.'];
        }

        return [
            'success' => true,
            'path'    => $relative,
            'name'    => basename($file['name']),
            'size'    => (int)$file['size'],
            'error'   => '',
        ];
    }

    /** Ruta absoluta de un recibo a partir de su ruta relativa guardada */
    public static function receiptAbsolutePath(string $relative): ?string
    {
        $base = realpath(dirname(__DIR__, 2) . '/' . self::RECEIPTS_DIR);
        $path = $base ? realpath($base . '/' . $relative) : false;
        // Evitar salir de la carpeta de recibos (path traversal)
        return ($path && str_starts_with($path, $base . DIRECTORY_SEPARATOR) && is_file($path)) ? $path : null;
    }

    /**
     * Eliminar un archivo de uploads
     */
    public function delete(string $filename): bool
    {
        $path = dirname(__DIR__, 2) . '/public/uploads/' . $filename;
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }

    /**
     * Mensaje de error de subida legible
     */
    private function uploadErrorMessage(int $code): string
    {
        return match($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo es demasiado grande.',
            UPLOAD_ERR_PARTIAL  => 'La subida fue interrumpida.',
            UPLOAD_ERR_NO_FILE  => 'No se seleccionó ningún archivo.',
            UPLOAD_ERR_NO_TMP_DIR => 'Directorio temporal no disponible.',
            UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo.',
            default             => 'Error desconocido al subir el archivo.',
        };
    }
}
