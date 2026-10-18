<?php
/**
 * Settings.php — Modelo para Configuración de la Iglesia
 * @package App\Modules\System\Models
 */
namespace App\Modules\System\Models;

use App\Models\Model;

class Settings extends Model
{
    /**
     * Obtener los datos de la iglesia actual
     */
    public function getChurchData(int $churchId): array
    {
        return $this->db->queryOne("SELECT * FROM churches WHERE id = ?", [$churchId]) ?: [];
    }

    /**
     * Actualizar los datos de la iglesia
     */
    public function updateChurchData(int $churchId, array $data): bool
    {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "`$key` = ?";
            $values[] = $value;
        }
        $values[] = $churchId;

        return $this->db->execute(
            "UPDATE churches SET " . implode(', ', $fields) . " WHERE id = ?",
            $values
        );
    }
}
