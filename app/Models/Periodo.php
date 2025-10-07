<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
    use HasFactory;

    protected $table = 'periodos';
    protected $primaryKey = 'periodo_id';

    // Un Periodo tiene muchas Calificaciones registradas
    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'periodo_id', 'periodo_id');
    }

    // Un Periodo tiene muchos Registros de Asistencia
    public function asistencias()
    {
        return $this->hasMany(RegistroAsistencia::class, 'periodo_id', 'periodo_id');
    }
}
