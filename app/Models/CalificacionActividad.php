<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class CalificacionActividad extends Model
{
    protected $table = 'calificaciones_actividad';
    protected $primaryKey = 'calificacion_actividad_id';
    
    protected $fillable = [
        'actividad_id',
        'alumno_id',
        'calificacion_obtenida',
        'estado_entrega',
        'observaciones'

    ];

    protected function casts(): array{
        return [
            'calificacion_obtenida' => 'decimal:2',
        ];
    }

    //relaciones
    public function actividad(): BelongsTo{
        return $this->belongsTo(ActividadMateria::class, 'actividad_id', 'actividad_id');
    }

    public function alumno(): BelongsTo{
        return $this->belongsTo(Alumno::class, 'alumno_id', 'alumno_id');
    }
}
