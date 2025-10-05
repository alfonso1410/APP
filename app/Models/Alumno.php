<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
     use HasFactory;

    protected $table = 'alumnos'; 
    protected $primaryKey = 'alumno_id';
    
    // Un Alumno pertenece a muchos Grupos (a través de la tabla pivote asignacion_grupal)
    public function grupos()
    {
        // 1. Modelo al que se relaciona (Grupo::class)
        // 2. Nombre de la tabla pivote ('asignacion_grupal')
        // 3. Clave foránea local en la tabla pivote ('alumno_id')
        // 4. Clave foránea del modelo remoto en la tabla pivote ('grupo_id')
        return $this->belongsToMany(Grupo::class, 'asignacion_grupal', 'alumno_id', 'grupo_id')
                    // Esto permite acceder a la columna extra 'es_actual' en la tabla pivote
                    ->withPivot('es_actual')
                    ->withTimestamps();
    }
}
