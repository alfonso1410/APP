<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Grupo extends Model
{
    use HasFactory;

    protected $table = 'grupos'; 
    protected $primaryKey = 'grupo_id';

    // 1. Relación con Grado (M-a-1)
    public function grado()
    {
        return $this->belongsTo(Grado::class, 'grado_id', 'grado_id');
    }
    
    // 2. Relación con Alumnos (M-a-M)
    public function alumnos()
    {
        // 1. Modelo al que se relaciona (Alumno::class)
        // 2. Nombre de la tabla pivote ('asignacion_grupal')
        // 3. Clave foránea local en la tabla pivote ('grupo_id')
        // 4. Clave foránea del modelo remoto en la tabla pivote ('alumno_id')
        return $this->belongsToMany(Alumno::class, 'asignacion_grupal', 'grupo_id', 'alumno_id')
                    ->withPivot('es_actual')
                    ->withTimestamps();
    }
    public function asignacionesMaestros()
{
    // Un Grupo tiene muchas asignaciones de Materia/Maestro
    return $this->hasMany(GrupoMateriaMaestro::class, 'grupo_id', 'grupo_id');
}
}
