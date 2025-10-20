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

    /**
     * Define si el modelo debe tener timestamps (created_at y updated_at).
     * Según tu schema , esta tabla no los tiene.
     */
    public $timestamps = false;

    /**
     * Atributos que se pueden asignar masivamente (para store/update).
     */
    protected $fillable = ['nombre'];

    
    // --- Relaciones con Estructura Curricular ---

    /**
     * 1. Relación (1:N) con los registros de la tabla pivote 'estructura_curricular'.
     * Útil si necesitas los registros pivote en sí.
     */
    public function estructuraCurricular(): HasMany
    {
        // El FK en 'estructura_curricular' es 'materia_id' [cite: 10]
        return $this->hasMany(EstructuraCurricular::class, 'materia_id', 'materia_id');
    }

    /**
     * 2. Relación (N:M) para obtener los Campos Formativos
     * a través de la tabla 'estructura_curricular'.
     */
    public function camposFormativos(): BelongsToMany
    {
        return $this->belongsToMany(
            CampoFormativo::class,      // Modelo relacionado
            'estructura_curricular',    // Tabla pivote
            'materia_id',               // FK de este modelo (Materia) en la pivote [cite: 10]
            'campo_id'                  // FK del modelo relacionado (CampoFormativo) en la pivote [cite: 10]
        );
    }

    // --- Relaciones con Criterios de Evaluación ---

    /**
     * 3. Relación (1:N) con los registros de la tabla pivote 'materia_criterios'.
     */
    public function criterios(): HasMany
    {
        // El FK en 'materia_criterios' es 'materia_id' [cite: 12]
        return $this->hasMany(MateriaCriterio::class, 'materia_id', 'materia_id');
    }

    // --- Relaciones con Asignación de Maestros ---

    /**
     * 4. Relación (1:N) con los registros de la tabla pivote 'grupo_materia_maestro'.
     */
    public function asignacionesGrupo(): HasMany
    {
        // El FK en 'grupo_materia_maestro' es 'materia_id' [cite: 13]
        return $this->hasMany(GrupoMateriaMaestro::class, 'materia_id', 'materia_id');
    }

    /**
     * 5. Relación (N:M) para obtener los Maestros (User)
     * a través de la tabla 'grupo_materia_maestro'.
     */
    public function maestros(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,                // Modelo relacionado
            'grupo_materia_maestro',    // Tabla pivote
            'materia_id',               // FK de este modelo (Materia) en la pivote [cite: 13]
            'maestro_id'                // FK del modelo relacionado (User) en la pivote [cite: 13]
        );
    }
}