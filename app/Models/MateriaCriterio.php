<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // Asegurar que HasMany está importado

class MateriaCriterio extends Model
{
    use HasFactory;

    protected $table = 'materia_criterios';
    protected $primaryKey = 'materia_criterio_id';

    /**
     * ✅ CORRECCIÓN: 'grado_id' ELIMINADO para coincidir con la estructura de la tabla.
     */
    protected $fillable = [
        'materia_id',
        // 'grado_id', // ¡Eliminado!
        'catalogo_criterio_id',
        'ponderacion',
        'incluido_en_promedio',
    ];

    // ------------------------------------
    // RELACIONES DE PERTENENCIA (BelongsTo)
    // ------------------------------------

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia_id', 'materia_id');
    }

    public function catalogoCriterio(): BelongsTo
    {
        return $this->belongsTo(CatalogoCriterio::class, 'catalogo_criterio_id', 'catalogo_criterio_id');
    }

    // ------------------------------------
    // RELACIONES DE DEPENDENCIA (HasMany)
    // ------------------------------------
    
    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class, 'materia_criterio_id', 'materia_criterio_id');
    }
}