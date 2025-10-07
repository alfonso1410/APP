<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class CatalogoCriterio extends Model
{
     use HasFactory;

    protected $table = 'catalogo_criterios';
    protected $primaryKey = 'catalogo_criterio_id';

    // Un Criterio de Catálogo puede ser usado en muchos registros de MateriaCriterio
    public function materiaCriterios()
    {
        return $this->hasMany(MateriaCriterio::class, 'catalogo_criterio_id', 'catalogo_criterio_id');
    }
}
