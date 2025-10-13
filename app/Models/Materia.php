<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;

    protected $table = 'materias';
    protected $primaryKey = 'materia_id';

    // 1. Relación con la Estructura Curricular: Una Materia aparece en muchos registros del plan de estudios.
     public function estructuraCurricular()
    {
        return $this->hasMany(EstructuraCurricular::class, 'materia_id');
    }

     public function camposFormativos()
    {
        return $this->belongsToMany(CampoFormativo::class, 'estructura_curricular', 'materia_id', 'campo_id');
    }

    // 2. Relación con los Criterios de Evaluación: Una Materia tiene varios Criterios definidos.
    public function criterios()
    {
        return $this->hasMany(MateriaCriterio::class, 'materia_id', 'materia_id');
    }
    
    // 3. Relación con Asignación a Grupos: Una Materia puede ser enseñada en varios grupos.
    public function asignacionesGrupo()
    {
        return $this->hasMany(GrupoMateriaMaestro::class, 'materia_id', 'materia_id');
    }

    public function maestros()
    {
        return $this->belongsToMany(User::class, 'grupo_materia_maestro', 'materia_id', 'maestro_id');
    }
}
