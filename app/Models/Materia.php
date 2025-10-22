<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne; // <--- Ya no es estrictamente necesario, pero se deja si lo usas en otro lado

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

    /**
     * Los atributos que deben ser visibles en la serialización JSON.
     */
    protected $visible = [
        'materia_id',
        'nombre',
        'tipo',
        'asignacionesGrupo',
        // ❌ ELIMINADA: 'primeraEstructura', 
        'grados', // ✅ AGREGADA para mostrar todos los grados.
    ];
    
    // --- Relaciones con Estructura Curricular ---

    public function estructuraCurricular(): HasMany
    {
        return $this->hasMany(EstructuraCurricular::class, 'materia_id', 'materia_id');
    }

    // ❌ RELACIÓN ELIMINADA:
    // public function primeraEstructura(): HasOne
    // {
    //     return $this->hasOne(EstructuraCurricular::class, 'materia_id', 'materia_id');
    // }

    /**
     * ✅ NUEVA RELACIÓN: Obtiene todos los Grados a los que esta materia está asignada.
     * Utiliza la tabla pivote 'estructura_curricular'.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function grados(): BelongsToMany
    {
        return $this->belongsToMany(
            Grado::class, 
            'estructura_curricular', 
            'materia_id', // foreign_key en tabla pivote
            'grado_id'    // related_key en tabla pivote
        );
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