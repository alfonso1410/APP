<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstructuraCurricular extends Model
{
    use HasFactory;

    protected $table = 'estructura_curricular';

    // 1. Relación con Grado: Pertenece a un Grado.
    public function grado()
    {
        return $this->belongsTo(Grado::class, 'grado_id', 'grado_id');
    }

    // 2. Relación con Campo Formativo: Pertenece a un Campo Formativo.
    public function campoFormativo()
    {
        return $this->belongsTo(CampoFormativo::class, 'campo_id', 'campo_id');
    }

    // 3. Relación con Materia: Pertenece a una Materia.
    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_id', 'materia_id');
    }
    
    // Indica a Eloquent que las claves no son autoincrementales
    public $incrementing = false; 

    // Define las claves primarias compuestas
    protected $primaryKey = ['grado_id', 'campo_id', 'materia_id']; 
}
