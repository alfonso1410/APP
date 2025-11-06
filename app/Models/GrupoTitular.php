<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrupoTitular extends Model
{
    // Nombre de la tabla
    protected $table = 'grupo_titular';

    // Desactivar auto-incremento ya que la PK es compuesta
    public $incrementing = false;

    // Llave primaria compuesta (¡Esta es la parte clave!)
    protected $primaryKey = 'grupo_id';

    // Campos permitidos para asignación masiva (updateOrCreate)
    protected $fillable = [
        'grupo_id',
        'idioma',
        'maestro_titular_id',
        'maestro_auxiliar_id', // <-- Importante
    ];

    /**
     * Relación al Grupo
     */
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id');
    }

    /**
     * Relación al Maestro TITULAR
     */
    public function titular()
    {
        // Se conecta a la columna 'maestro_titular_id'
        return $this->belongsTo(User::class, 'maestro_titular_id');
    }

    /**
     * Relación al Maestro AUXILIAR (NUEVO)
     */
    public function auxiliar()
    {
        // Se conecta a la columna 'maestro_auxiliar_id'
        return $this->belongsTo(User::class, 'maestro_auxiliar_id');
    }
}