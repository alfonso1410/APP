<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Calificacion;
use App\Models\RegistroAsistencia;

class Periodo extends Model
{
    use HasFactory;

    protected $table = 'periodos';
    protected $primaryKey = 'periodo_id';

    protected $fillable = [
        'ciclo_escolar_id', // <-- Añadido
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

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

   public function cicloEscolar(): BelongsTo // <-- Debe ser solo BelongsTo (del namespace importado)
    {
        return $this->belongsTo(CicloEscolar::class, 'ciclo_escolar_id', 'ciclo_escolar_id');
    }
    // ... (Otras relaciones si las tienes, como hasMany(Calificacion::class) ) ...

    // Añade getRouteKeyName si usas Route Model Binding con {periodo}
    public function getRouteKeyName()
    {
        return 'periodo_id';
    }
}
