<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CampoFormativo extends Model
{
    use HasFactory;

    protected $table = 'campos_formativos';
    protected $primaryKey = 'campo_id';

    /**
     * CORRECCIÓN:
     * Tu base de datos SÍ tiene timestamps,
     * por lo que eliminamos la línea 'public $timestamps = false;'.
     * Eloquent los manejará automáticamente por defecto.
     */
    // public $timestamps = false;  <-- LÍNEA ELIMINADA

    /**
     * Atributos que se pueden asignar masivamente (para store/update).
     * Esto soluciona el error 'MassAssignmentException'.
     */

  protected $fillable = [
        'nombre',
        'nivel_id',
    ];
    /**
     * Un Campo Formativo se relaciona con muchas Materias a través de la
     * tabla pivote 'estructura_curricular'.
     */
    public function materias(): BelongsToMany
    {
        return $this->belongsToMany(
            Materia::class,           // 1. Modelo relacionado
            'estructura_curricular',  // 2. Tabla pivote
            'campo_id',               // 3. Clave foránea de este modelo (CampoFormativo) en la pivote
            'materia_id'              // 4. Clave foránea del modelo relacionado (Materia) en la pivote
        );
    }

    /**
     * Relación opcional para obtener los registros pivote directos.
     */
    public function asignacionesEstructura()
    {
         return $this->hasMany(EstructuraCurricular::class, 'campo_id', 'campo_id');
    }

    public function nivel()
    {
        // Como usas 'nivel_id' como llave primaria en Niveles,
        // debemos especificarlo como 'ownerKey' (el 3er argumento).
        return $this->belongsTo(Nivel::class, 'nivel_id', 'nivel_id');
    }
}