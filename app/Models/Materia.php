<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materia extends Model
{
    use HasFactory;

    protected $table = 'materias';
    protected $primaryKey = 'materia_id';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'tipo', 
    ];

    // --- INICIO DE LA CORRECCIÓN ---
    /**
     * Los atributos que deben ser visibles en la serialización JSON.
     * Esto es crucial para que el modal "Ver Materias" en Campos Formativos
     * pueda acceder a las asignaciones de esta materia.
     */
    protected $visible = [
        'materia_id',
        'nombre',
        'tipo',
        'asignacionesGrupo', // <-- La clave para el modal
        // No necesitamos 'camposFormativos' aquí para evitar bucles
    ];
    // --- FIN DE LA CORRECCIÓN ---
    
    // --- Relaciones con Estructura Curricular ---

    public function estructuraCurricular(): HasMany
    {
        return $this->hasMany(EstructuraCurricular::class, 'materia_id', 'materia_id');
    }

    public function camposFormativos(): BelongsToMany
    {
        return $this->belongsToMany(
            CampoFormativo::class,
            'estructura_curricular',
            'materia_id',
            'campo_id'
        );
    }

    // --- Relaciones con Criterios de Evaluación ---

    public function criterios(): HasMany
    {
        return $this->hasMany(MateriaCriterio::class, 'materia_id', 'materia_id');
    }

    // --- Relaciones con Asignación de Maestros ---

    /**
     * Esta es la relación (camelCase) que cargamos en el controlador
     * y que ahora será visible en el JSON.
     */
    public function asignacionesGrupo(): HasMany
    {
        return $this->hasMany(GrupoMateriaMaestro::class, 'materia_id', 'materia_id');
    }

    public function maestros(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'grupo_materia_maestro',
            'materia_id',
            'maestro_id'
        );
    }
}