<?php
/**
 * User.php — Modelo de Usuarios del Sistema
 * -------------------------------------------
 * Gestiona los usuarios con autenticación, roles y MFA.
 *
 * @package App\Models
 */

namespace App\Modules\System\Models;

use App\Models\Model;

use App\Core\Database;

class User extends Model
{
    protected string $table = 'users';

    /**
     * Buscar usuario por email e iglesia
     */
    public function findByEmailAndChurch(string $email, int $churchId): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM users WHERE email = ? AND church_id = ? AND deleted_at IS NULL LIMIT 1",
            [$email, $churchId]
        );
    }

    /**
     * Buscar usuario por email en cualquier iglesia
     */
    public function findByEmail(string $email): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1",
            [$email]
        );
    }

    /**
     * Incrementar el contador de intentos de login fallidos
     * Bloquear la cuenta después de 5 intentos
     */
    public function incrementLoginAttempts(int $userId): void
    {
        $user = $this->find($userId);
        $attempts = ($user['login_attempts'] ?? 0) + 1;

        if ($attempts >= 5) {
            // Bloquear por 30 minutos
            $this->db->execute(
                "UPDATE users SET login_attempts = ?, locked_until = DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE id = ?",
                [$attempts, $userId]
            );
        } else {
            $this->db->execute(
                "UPDATE users SET login_attempts = ? WHERE id = ?",
                [$attempts, $userId]
            );
        }
    }

    /**
     * Resetear intentos de login
     */
    public function resetLoginAttempts(int $userId): void
    {
        $this->db->execute(
            "UPDATE users SET login_attempts = 0, locked_until = NULL WHERE id = ?",
            [$userId]
        );
    }

    /**
     * Actualizar datos del último login
     */
    public function updateLastLogin(int $userId, string $ip): void
    {
        $this->db->execute(
            "UPDATE users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?",
            [$ip, $userId]
        );
    }

    /**
     * Obtener todos los usuarios de una iglesia
     */
    public function getByChurch(int $churchId): array
    {
        return $this->db->query(
            "SELECT id, uuid, name, email, role, avatar, phone, is_active, last_login_at, created_at, last_activity_at, force_logout
             FROM users
             WHERE church_id = ? AND deleted_at IS NULL
             ORDER BY name ASC",
            [$churchId]
        );
    }

    /**
     * Crear un nuevo usuario con contraseña hasheada
     */
    public function createUser(array $data): int
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        $data['uuid']     = \Ramsey\Uuid\Uuid::uuid4()->toString();
        return $this->create($data);
    }

    /**
     * Actualizar contraseña del usuario
     */
    public function updatePassword(int $userId, string $newPassword): void
    {
        $this->db->execute(
            "UPDATE users SET password = ? WHERE id = ?",
            [password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]), $userId]
        );
    }

    /**
     * Guardar el secreto MFA del usuario
     */
    public function saveMfaSecret(int $userId, string $secret): void
    {
        $this->db->execute(
            "UPDATE users SET mfa_secret = ?, mfa_enabled = 1 WHERE id = ?",
            [$secret, $userId]
        );
    }

    /**
     * Desactivar MFA
     */
    public function disableMfa(int $userId): void
    {
        $this->db->execute(
            "UPDATE users SET mfa_secret = NULL, mfa_enabled = 0 WHERE id = ?",
            [$userId]
        );
    }
}

