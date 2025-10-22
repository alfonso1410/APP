<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Materia; 

class Grupo extends Model
{
    use HasFactory;

    protected $table = 'grupos'; 
    protected $primaryKey = 'grupo_id';

    protected $fillable = [
        'grado_id', 
        'nombre_grupo',
        'ciclo_escolar',
        'estado',
        'tipo_grupo',
    ];

    // --- INICIO CORRECCIÓN ---
    /**
     * Atributos visibles en JSON para el modal de campos formativos.
     */
    protected $visible = [
        'grupo_id',
        'nombre_grupo',
        'grado', // <-- La relación clave
        'grado_id'
    ];
    // --- FIN CORRECCIÓN ---

    // 1. Relación con Grado (M-a-1)
    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class, 'grado_id', 'grado_id');
    }
    
    // --- Otras relaciones (sin cambios) ---
    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'grupo_materia_maestro', 'grupo_id', 'materia_id');
    }

    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(Alumno::class, 'asignacion_grupal', 'grupo_id', 'alumno_id')
                    ->withPivot('es_actual')
                    ->withTimestamps();
    }

    public function alumnosActuales()
    {
        return $this->belongsToMany(Alumno::class, 'asignacion_grupal', 'grupo_id', 'alumno_id')
                    ->wherePivot('es_actual', 1); 
    }

   public function maestrosTitulares()
    {
        // --- CORRECCIÓN: Añadir withPivot para leer la columna 'idioma' de la tabla pivote ---
        return $this->belongsToMany(
            User::class,
            'grupo_titular', // Nombre de la tabla pivote
            'grupo_id',      // Llave foránea de este modelo (Grupo)
            'maestro_id'     // <-- ¡CAMBIO AQUÍ! (en lugar de 'user_id')
        )->withPivot('idioma'); // <-- AÑADIDO
    }
    
    public function asignacionesMaestros(): HasMany
    {
        return $this->hasMany(GrupoMateriaMaestro::class, 'grupo_id', 'grupo_id');
    }
}