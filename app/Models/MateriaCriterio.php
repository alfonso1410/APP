<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MateriaCriterio extends Model
{
   use HasFactory;

    protected $table = 'materia_criterios';
    protected $primaryKey = 'materia_criterio_id';

    // Un registro de Criterio pertenece a UNA Materia (la materia que se evalúa)
    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_id', 'materia_id');
    }

    // Un registro de Criterio pertenece a UN elemento del Catálogo (ej: 'Examen Escrito')
    public function catalogoCriterio()
    {
        return $this->belongsTo(CatalogoCriterio::class, 'catalogo_criterio_id', 'catalogo_criterio_id');
    }

    // Un registro de Criterio tiene muchas Calificaciones (datos transaccionales)
    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'materia_criterio_id', 'materia_criterio_id');
    }
}
