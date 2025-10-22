<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
     use HasFactory;

    protected $table = 'alumnos'; 
    protected $primaryKey = 'alumno_id';
    protected $appends = ['promedio_general', 'materia_extracurricular'];
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
    protected $fillable = [
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'curp',
        'estado_alumno',
    ];

     public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'alumno_id');
    }

    public function getPromedioGeneralAttribute()
    {
        // Usamos la relación ya cargada para no consultar la BD de nuevo
        if ($this->calificaciones->isEmpty()) {
            return 0; // O null, como prefieras
        }
        
        // Calcula el promedio de la columna 'calificacion_obtenida'
        return $this->calificaciones->avg('calificacion_obtenida');
    }

  public function getMateriaExtracurricularAttribute()
{
    $grupoExtra = $this->grupos->firstWhere('tipo_grupo', 'EXTRA');
    $materia = $grupoExtra?->materias?->first();

    return $materia?->nombre ?? 'Ninguna';
}

     public function grupoRegularActivo()
    {
        return $this->belongsToMany(Grupo::class, 'asignacion_grupal', 'alumno_id', 'grupo_id')
                    ->wherePivot('es_actual', 1)
                    ->where('tipo_grupo', 'REGULAR');
    }
     public function grupoExtracurricularActivo()
    {
        return $this->belongsToMany(Grupo::class, 'asignacion_grupal', 'alumno_id', 'grupo_id')
                    ->wherePivot('es_actual', 1)
                    ->where('tipo_grupo', 'EXTRA');
    }
}
