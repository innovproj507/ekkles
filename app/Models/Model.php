<?php
/**
 * Model.php — Modelo Base
 * ------------------------
 * Clase abstracta con operaciones CRUD básicas usando PDO.
 * Todos los modelos extienden esta clase.
 *
 * @package App\Models
 */

namespace App\Models;

use App\Core\Database;

abstract class Model
{
    /** @var string Nombre de la tabla en la base de datos */
    protected string $table = '';

    /** @var string Clave primaria de la tabla */
    protected string $primaryKey = 'id';

    /** @var Database Instancia de la base de datos */
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Encontrar un registro por su ID
     */
    public function find(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? AND `deleted_at` IS NULL LIMIT 1",
            [$id]
        );
    }

    /**
     * Encontrar un registro por su ID (incluyendo eliminados)
     */
    public function findWithTrashed(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1",
            [$id]
        );
    }

    /**
     * Obtener todos los registros de la tabla
     */
    public function all(int $churchId = 0): array
    {
        if ($churchId > 0) {
            return $this->db->query(
                "SELECT * FROM `{$this->table}` WHERE `church_id` = ? AND `deleted_at` IS NULL ORDER BY `id` DESC",
                [$churchId]
            );
        }
        return $this->db->query(
            "SELECT * FROM `{$this->table}` WHERE `deleted_at` IS NULL ORDER BY `id` DESC"
        );
    }

    /**
     * Crear un nuevo registro
     *
     * @return int ID del registro creado
     */
    public function create(array $data): int
    {
        $columns = implode('`, `', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $this->db->execute(
            "INSERT INTO `{$this->table}` (`{$columns}`) VALUES ({$placeholders})",
            array_values($data)
        );

        return (int)$this->db->lastInsertId();
    }

    /**
     * Actualizar un registro por ID
     */
    public function update(int $id, array $data): int
    {
        $sets = implode(' = ?, ', array_map(fn($col) => "`{$col}`", array_keys($data))) . ' = ?';

        return $this->db->execute(
            "UPDATE `{$this->table}` SET {$sets} WHERE `{$this->primaryKey}` = ?",
            [...array_values($data), $id]
        );
    }

    /**
     * Eliminación lógica (soft delete) — nunca borrado físico
     */
    public function delete(int $id): int
    {
        return $this->db->execute(
            "UPDATE `{$this->table}` SET `deleted_at` = NOW() WHERE `{$this->primaryKey}` = ?",
            [$id]
        );
    }

    /**
     * Contar registros con filtro opcional de iglesia
     */
    public function count(int $churchId = 0, array $conditions = []): int
    {
        $where = ['deleted_at IS NULL'];
        $params = [];

        if ($churchId > 0) {
            $where[] = 'church_id = ?';
            $params[] = $churchId;
        }

        foreach ($conditions as $col => $val) {
            $where[] = "`{$col}` = ?";
            $params[] = $val;
        }

        $whereStr = implode(' AND ', $where);
        $result = $this->db->queryOne(
            "SELECT COUNT(*) as total FROM `{$this->table}` WHERE {$whereStr}",
            $params
        );

        return (int)($result['total'] ?? 0);
    }

    /**
     * Paginación básica
     *
     * @return array ['data' => array, 'total' => int, 'pages' => int, 'current' => int]
     */
    public function paginate(int $page, int $perPage, int $churchId, string $extraWhere = '', array $params = []): array
    {
        $offset = ($page - 1) * $perPage;
        $where  = "church_id = ? AND deleted_at IS NULL";
        $allParams = [$churchId];

        if ($extraWhere) {
            $where .= " AND ({$extraWhere})";
            $allParams = array_merge($allParams, $params);
        }

        $total = (int)($this->db->queryOne(
            "SELECT COUNT(*) as total FROM `{$this->table}` WHERE {$where}",
            $allParams
        )['total'] ?? 0);

        $data = $this->db->query(
            "SELECT * FROM `{$this->table}` WHERE {$where} ORDER BY id DESC LIMIT ? OFFSET ?",
            [...$allParams, $perPage, $offset]
        );

        return [
            'data'    => $data,
            'total'   => $total,
            'pages'   => (int)ceil($total / $perPage),
            'current' => $page,
            'perPage' => $perPage,
        ];
    }
}
