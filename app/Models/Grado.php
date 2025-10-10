<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grado extends Model
{
   use HasFactory;

    // 1. Especificar el nombre de la tabla:
    protected $table = 'grados'; 
    
    // 2. Especificar la clave primaria:
    protected $primaryKey = 'grado_id';
    
     protected $fillable = [
        'nombre',
        'nivel_id',
    ];
    // 3. Definir la relación con Nivel: Un Grado pertenece a UN Nivel
    public function nivel()
    {
        // El primer argumento es el modelo relacionado.
        // El segundo argumento es la clave foránea en la tabla 'grados' (local).
        // El tercer argumento es la clave local en la tabla 'niveles'.
        return $this->belongsTo(Nivel::class, 'nivel_id', 'nivel_id');
    }

    // 4. PREPARACIÓN: Un Grado también tendrá muchos Grupos
   public function grupos(): HasMany
    {
        return $this->hasMany(Grupo::class, 'grado_id', 'grado_id');
    }

    public function estructuraCurricular(): HasMany
    {
        return $this->hasMany(EstructuraCurricular::class, 'grado_id', 'grado_id');
    }
}
