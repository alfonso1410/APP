<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CatalogoCriterio extends Model
{
    use HasFactory;

    protected $table = 'catalogo_criterios';
    protected $primaryKey = 'catalogo_criterio_id';

    /**
     * The attributes that are mass assignable.
     * Añadidos 'nombre' y 'descripcion' para la creación desde el formulario.
     * Estos son los campos que se definen en la vista 'materia-criterios.create'.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
    ];

    // Desactivamos timestamps si tu tabla no los tiene. Asumo que sí los tiene, 
    // pero si no es así, descomenta la siguiente línea:
    // public $timestamps = false;

    /**
     * Relación: Un Criterio de Catálogo puede ser usado en muchos registros de MateriaCriterio.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function materiaCriterios()
    {
        return $this->hasMany(MateriaCriterio::class, 'catalogo_criterio_id', 'catalogo_criterio_id');
    }
}