<?php

namespace App\Modules\Events\Models;

use App\Models\Model;

class EventType extends Model
{
    protected string $table = 'event_types';
    protected array $fillable = ['church_id', 'name', 'color', 'is_active'];

    public function getAllActive(int $churchId): array
    {
        return $this->db->query("SELECT * FROM {$this->table} WHERE church_id = ? AND is_active = 1 ORDER BY name", [$churchId]);
    }
    
    public function getAll(int $churchId): array
    {
        return $this->db->query("SELECT * FROM {$this->table} WHERE church_id = ? ORDER BY name", [$churchId]);
    }

    /**
     * event_types no tiene deleted_at: buscar sin ese filtro
     */
    public function find(int $id): ?array
    {
        return $this->db->queryOne("SELECT * FROM {$this->table} WHERE id = ? LIMIT 1", [$id]);
    }

    /**
     * event_types no tiene deleted_at: borrado físico (solo se permite si no hay eventos que lo usen)
     */
    public function delete(int $id): int
    {
        return $this->db->execute("DELETE FROM {$this->table} WHERE id = ?", [$id]);
    }
}
