<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'orden',      // <-- Añadir
        'tipo_grado', // <-- Añadir
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

    public function gradosRegularesMapeados()
    {
        return $this->belongsToMany(
            Grado::class,           // El modelo al que nos conectamos
            'grado_mapeo',          // La tabla pivote
            'extra_grado_id',       // La clave foránea de este modelo en la tabla pivote
            'regular_grado_id'      // La clave foránea del modelo relacionado en la tabla pivote
        );
    }

    public function materias()
{
    return $this->belongsToMany(Materia::class, 'estructura_curricular', 'grado_id', 'materia_id');
}
}
