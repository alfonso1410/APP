<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany; // Asegúrate de tener este 'use'
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Asegúrate de tener este 'use'


class CampoFormativo extends Model
{
    use HasFactory;

    protected $table = 'campos_formativos';
    protected $primaryKey = 'campo_id';

    protected $fillable = [
        'nombre',
        'nivel_id',
    ];

    /**
     * Los atributos que deben ser visibles en la serialización JSON.
     * Incluye las relaciones 'materias' y 'nivel'.
     */
    protected $visible = [
        'campo_id',
        'nombre',
        'nivel_id',
        'materias', // <-- Incluye la relación materias
        'nivel'     // <-- Incluye la relación nivel
    ];

    /**
     * Relación con Materias.
     * Se añade distinct() para evitar problemas con llaves duplicadas en Alpine.
     */
    public function materias(): BelongsToMany
    {
        return $this->belongsToMany(
            Materia::class,
            'estructura_curricular',
            'campo_id',
            'materia_id'
        )->distinct(); // <-- CORRECCIÓN: Evita materias duplicadas
    }

    /**
     * Relación con Estructura Curricular (registros pivote).
     */
    public function asignacionesEstructura(): HasMany
    {
         return $this->hasMany(EstructuraCurricular::class, 'campo_id', 'campo_id');
    }

    /**
     * Relación con Nivel.
     */
    public function nivel(): BelongsTo
    {
        return $this->belongsTo(Nivel::class, 'nivel_id', 'nivel_id');
    }
}