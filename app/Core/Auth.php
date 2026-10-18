<?php
/**
 * Auth.php — Capa de Autenticación
 * ----------------------------------
 * Gestiona el login, logout, verificación de permisos RBAC
 * y bloqueo de cuenta por intentos fallidos.
 *
 * @package App\Core
 */

namespace App\Core;

use App\Modules\System\Models\User;
use App\Modules\System\Models\AuditLog;

class Auth
{
    /** @var array|null Usuario autenticado en la sesión actual */
    private static ?array $user = null;

    /** @var array Configuración de roles y permisos */
    private static array $rolesConfig = [];

    /**
     * Intentar autenticar al usuario con email y contraseña
     *
     * @return array ['success' => bool, 'requires_mfa' => bool, 'message' => string]
     */
    public static function attempt(string $email, string $password, int $churchId): array
    {
        $userModel = new User();
        $user = $userModel->findByEmailAndChurch($email, $churchId);

        if (!$user) {
            return ['success' => false, 'message' => 'Credenciales inválidas.'];
        }

        // Verificar si la cuenta está bloqueada
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $minutes = ceil((strtotime($user['locked_until']) - time()) / 60);
            return ['success' => false, 'message' => "Cuenta bloqueada. Intente en {$minutes} minutos."];
        }

        // Verificar contraseña
        if (!password_verify($password, $user['password'])) {
            $userModel->incrementLoginAttempts($user['id']);
            $remaining = 5 - ($user['login_attempts'] + 1);
            if ($remaining <= 0) {
                return ['success' => false, 'message' => 'Cuenta bloqueada por múltiples intentos fallidos.'];
            }
            return ['success' => false, 'message' => "Contraseña incorrecta. Intentos restantes: {$remaining}"];
        }

        // Verificar que el usuario esté activo
        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Su cuenta está desactivada. Contacte al administrador.'];
        }

        // Resetear intentos fallidos
        $userModel->resetLoginAttempts($user['id']);

        // Si MFA está habilitado, guardar usuario en sesión temporal
        if ($user['mfa_enabled']) {
            Session::set('mfa_pending_user_id', $user['id']);
            return ['success' => true, 'requires_mfa' => true];
        }

        // Login exitoso sin MFA
        self::loginUser($user);

        return ['success' => true, 'requires_mfa' => false];
    }

    /**
     * Verificar el código TOTP de MFA
     */
    public static function verifyMfa(string $code): bool
    {
        $pendingUserId = Session::get('mfa_pending_user_id');
        if (!$pendingUserId) {
            return false;
        }

        $userModel = new User();
        $user = $userModel->find($pendingUserId);

        if (!$user || !$user['mfa_secret']) {
            return false;
        }

        // Verificar código TOTP usando la librería OTPHP
        $totp = \OTPHP\TOTP::create($user['mfa_secret']);
        if (!$totp->verify($code, null, 1)) { // Ventana de 1 período de tolerancia
            return false;
        }

        Session::forget('mfa_pending_user_id');
        self::loginUser($user);
        return true;
    }

    /**
     * Establecer el usuario autenticado en sesión
     */
    private static function loginUser(array $user): void
    {
        // No guardar la contraseña ni el secreto MFA en sesión
        unset($user['password'], $user['mfa_secret'], $user['remember_token']);

        Session::set('auth_user', $user);
        Session::set('auth_church_id', $user['church_id']);

        // Regenerar ID de sesión al loguearse (seguridad)
        session_regenerate_id(true);

        // Actualizar último login
        $userModel = new User();
        $userModel->updateLastLogin($user['id'], request_ip());

        // Registrar en auditoría
        AuditLog::record('login', 'auth', $user['id'], null, null, null);
    }

    /**
     * Cerrar sesión del usuario actual
     */
    public static function logout(): void
    {
        $userId = self::id();
        if ($userId) {
            AuditLog::record('logout', 'auth', $userId, null, null, null);
        }
        Session::destroy();
        self::$user = null;
    }

    /**
     * Verificar si hay un usuario autenticado
     */
    public static function check(): bool
    {
        return Session::has('auth_user');
    }

    /**
     * Verificar si el MFA está pendiente
     */
    public static function mfaPending(): bool
    {
        return Session::has('mfa_pending_user_id');
    }

    /**
     * Obtener el usuario autenticado actual
     */
    public static function user(): ?array
    {
        if (self::$user === null && self::check()) {
            self::$user = Session::get('auth_user');
        }
        return self::$user;
    }

    /**
     * Obtener el ID del usuario autenticado
     */
    public static function id(): ?int
    {
        return self::user()['id'] ?? null;
    }

    /**
     * Obtener el ID de la iglesia del usuario autenticado
     */
    public static function churchId(): int
    {
        return Session::get('auth_church_id', 1);
    }

    /**
     * Obtener el rol del usuario autenticado
     */
    public static function role(): ?string
    {
        return self::user()['role'] ?? null;
    }

    /**
     * Obtener el nivel numérico del rol actual
     */
    public static function roleLevel(): int
    {
        $config = self::getRolesConfig();
        $role = self::role();
        return $config['roles'][$role]['level'] ?? 0;
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public static function can(string $permission): bool
    {
        if (!self::check()) {
            return false;
        }

        $config = self::getRolesConfig();
        $role = self::role();

        // 1. Verificar denegaciones explícitas (pierde acceso aunque su nivel sea suficiente)
        $denyList = $config['roles'][$role]['exceptions']['deny'] ?? [];
        if (in_array($permission, $denyList) || in_array('*', $denyList)) {
            // Permitir override si está en allow
            $allowList = $config['roles'][$role]['exceptions']['allow'] ?? [];
            if (!in_array($permission, $allowList)) {
                return false;
            }
        }

        // 2. Verificar permisos explícitos (gana acceso aunque su nivel no sea suficiente)
        $allowList = $config['roles'][$role]['exceptions']['allow'] ?? [];
        if (in_array($permission, $allowList)) {
            return true;
        }

        // 3. Verificación jerárquica normal por niveles
        $requiredLevel = $config['permissions'][$permission] ?? PHP_INT_MAX;

        return self::roleLevel() >= $requiredLevel;
    }

    /**
     * Verificar si el usuario tiene al menos el nivel de rol indicado
     */
    public static function hasRole(string $role): bool
    {
        return self::role() === $role || self::roleLevel() >= self::getRolesConfig()['roles'][$role]['level'] ?? 0;
    }

    /**
     * Cargar la configuración de roles (con caché)
     */
    private static function getRolesConfig(): array
    {
        if (empty(self::$rolesConfig)) {
            self::$rolesConfig = require dirname(__DIR__, 2) . '/config/roles.php';
        }
        return self::$rolesConfig;
    }
}
