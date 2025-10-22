<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroAsistencia extends Model
{
     use HasFactory;

    protected $table = 'registro_asistencia';
    protected $primaryKey = 'registro_asistencia_id';

    protected $fillable = [
        'alumno_id',
        'grupo_id',
        'fecha',
        'tipo_asistencia',
    ];

    // 1. Relación con Alumno: Un registro pertenece a UN Alumno
    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id', 'alumno_id');
    }

    // 2. Relación con Grupo: La asistencia se toma dentro de UN Grupo
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id', 'grupo_id');
    }
    
    // NOTA: Aunque el campo 'periodo_id' no está en la migración de esta tabla,
    // si lo agregaras después para restringir el cálculo de reportes,
    // esta función sería necesaria:
    /* public function periodo()
    {
        return $this->belongsTo(Periodo::class, 'periodo_id', 'periodo_id');
    }
    */
}
