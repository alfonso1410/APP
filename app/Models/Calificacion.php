<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
     use HasFactory;

    protected $table = 'calificaciones';
    protected $primaryKey = 'calificacion_id';

    // 1. Relación con Alumno: Una calificación pertenece a UN Alumno
    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id', 'alumno_id');
    }

    // 2. Relación con MateriaCriterio: La calificación fue obtenida bajo UN Criterio Específico
    public function materiaCriterio()
    {
        return $this->belongsTo(MateriaCriterio::class, 'materia_criterio_id', 'materia_criterio_id');
    }

    // 3. Relación con Periodo: La calificación se registró en UN Periodo (Trimestre, Bimestre, etc.)
    public function periodo()
    {
        return $this->belongsTo(Periodo::class, 'periodo_id', 'periodo_id');
    }
}
