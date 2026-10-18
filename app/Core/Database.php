<?php
/**
 * Database.php — Conexión PDO Singleton
 * ---------------------------------------
 * Gestiona una única instancia de conexión a la base de datos.
 * Implementa el patrón Singleton para evitar múltiples conexiones.
 *
 * @package App\Core
 */

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    /** @var Database|null Instancia única */
    private static ?Database $instance = null;

    /** @var PDO Conexión PDO */
    private PDO $pdo;

    /**
     * Constructor privado: crea la conexión PDO
     */
    private function __construct()
    {
        $config = require dirname(__DIR__, 2) . '/config/database.php';

        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);

            // Alinear la zona horaria de MySQL (NOW(), CURRENT_TIMESTAMP) con la de PHP (APP_TIMEZONE)
            $this->pdo->exec("SET time_zone = '" . date('P') . "'");
        } catch (PDOException $e) {
            // En producción, no exponer detalles de conexión
            $appConfig = require dirname(__DIR__, 2) . '/config/app.php';
            if ($appConfig['debug']) {
                throw $e;
            }
            throw new \RuntimeException('Error de conexión a la base de datos. Contacte al administrador.');
        }
    }

    /**
     * Obtener la instancia única de Database
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtener la conexión PDO
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Ejecutar una consulta con parámetros y retornar todos los resultados
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Ejecutar una consulta y retornar un único registro
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Ejecutar una sentencia INSERT/UPDATE/DELETE
     *
     * @return int Número de filas afectadas
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Obtener el último ID insertado
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Iniciar una transacción
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Confirmar la transacción
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Revertir la transacción
     */
    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Prevenir clonación del Singleton
     */
    private function __clone() {}
}
