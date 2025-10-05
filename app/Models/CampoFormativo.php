<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class CampoFormativo extends Model
{
   use HasFactory;

    protected $table = 'campos_formativos';
    protected $primaryKey = 'campo_id';

    // Un Campo Formativo tiene muchas Materias, ya que Materias pertenece a este Campo en un Grado específico.
    public function materias()
    {
        // Usamos hasMany para la relación M:M a través de la tabla de enlace EstructuraCurricular
        return $this->hasMany(EstructuraCurricular::class, 'campo_id', 'campo_id');
    }
}
