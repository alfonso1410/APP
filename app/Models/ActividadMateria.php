<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ActividadMateria extends Model
{
    protected $table = 'actividades_materia';
    protected $primaryKey = 'actividad_id';

   protected $fillable = [
    'grupo_id',
    'periodo_id',
    'materia_id',
    'materia_criterio_id',
    'maestro_id',
    'nombre_actividad',
    'descripcion',
    'fecha_actividad',
    'valor_maximo',
    'calculado_por',
    'calculado_en'
   ];
    //casts
   protected function casts(): array{
    return [
        'fecha_actividad' => 'date',
        'valor_maximo' => 'decimal:2',
        'calculado_en' => 'datetime'
    ];
   }

   // relaciones
    public function calificaciones():HasMany {
        return $this->hasMany(CalificacionActividad::class, 'actividad_id', 'actividad_id');
    }

    public function grupo(): BelongsTo{
        return $this->belongsTo(Grupo::class,'grupo_id', 'grupo_id');
    }

    public function materia(): BelongsTo{
        return $this->belongsTo(Materia::class,'materia_id', 'materia_id');
    }

    public function materiaCriterio(): BelongsTo{
        return $this->belongsTo(MateriaCriterio::class, 'materia_criterio_id', 'materia_criterio_id');
    }

    public function periodo():BelongsTo{
        return $this->belongsTo(Periodo::class, 'periodo_id', 'periodo_id');
    }

    public function maestro(): BelongsTo{
        return $this->belongsTo(User::class, 'maestro_id', 'id');
    }

    public function calculadoPor(): BelongsTo{
        return $this->belongsTo(User::class, 'calculado_por', 'id');
    }


    public function getEstaDesincronizadaAttribute(): bool{
        if(is_null($this->calculado_en)){
            return true;
        }
        return $this->updated_at->gt($this->calculado_en);
    }
}
