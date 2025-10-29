<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroAsistencia extends Model
{
     use HasFactory;

    protected $table = 'registro_asistencia';
    protected $primaryKey = 'registro_asistencia_id';

    /**
     * CORRECCIÓN: 'turno' se reemplaza por 'idioma'
     */
   protected $fillable = [
        'alumno_id',
        'grupo_id',
        'periodo_id', // <-- ¡Asegúrate de añadir esto si lo necesitas!
        'fecha',
        'idioma',
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

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'periodo_id', 'periodo_id');
    }
    /* --- FIN NUEVA RELACIÓN --- */
}