<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Grupo extends Model
{
    use HasFactory;

    protected $table = 'grupos'; 
    protected $primaryKey = 'grupo_id';

      protected $fillable = [
        'grado_id', // ¡El que faltaba!
        'nombre_grupo',
        'ciclo_escolar',
        'estado',
        'tipo_grupo',
    ];
    // 1. Relación con Grado (M-a-1)
    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class, 'grado_id', 'grado_id');
    }
    
    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(Alumno::class, 'asignacion_grupal', 'grupo_id', 'alumno_id')
                    ->withPivot('es_actual')
                    ->withTimestamps();
    }
    
    public function asignacionesMaestros(): HasMany
    {
        return $this->hasMany(GrupoMateriaMaestro::class, 'grupo_id', 'grupo_id');
    }
}