<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Nivel extends Model
{
    use HasFactory;

    // 1. Especificar el nombre de la tabla si no sigue la convención (plural):
    protected $table = 'niveles';
    
    // 2. Especificar la clave primaria si no es 'id':
    protected $primaryKey = 'nivel_id';
    
    // 3. Definir la relación: Un Nivel tiene muchos Grados
    public function grados()
    {
        // El primer argumento es el modelo relacionado.
        // El segundo argumento es la clave foránea en la tabla 'grados'.
        // El tercer argumento es la clave local en la tabla 'niveles'.
        return $this->hasMany(Grado::class, 'nivel_id', 'nivel_id');
    }
}
