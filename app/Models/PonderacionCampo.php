<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PonderacionCampo extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'ponderacion_campos'; // Especificamos el nombre de la tabla

    /**
     * La llave primaria asociada con la tabla.
     *
     * @var string
     */
    protected $primaryKey = 'ponderacion_campo_id'; // Especificamos la llave primaria

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ciclo_escolar_id',
        'grado_id',
        'campo_formativo_id',
        'ponderacion',
    ];

    /**
     * Define la relación: Una ponderación pertenece a un Ciclo Escolar.
     */
    public function cicloEscolar()
    {
        // Apunta al modelo CicloEscolar usando la llave 'ciclo_escolar_id'
        return $this->belongsTo(CicloEscolar::class, 'ciclo_escolar_id', 'ciclo_escolar_id');
    }

    /**
     * Define la relación: Una ponderación pertenece a un Grado.
     */
    public function grado()
    {
        // Apunta al modelo Grado usando la llave 'grado_id'
        return $this->belongsTo(Grado::class, 'grado_id', 'grado_id');
    }

    /**
     * Define la relación: Una ponderación pertenece a un Campo Formativo.
     */
    public function campoFormativo()
    {
        // Apunta al modelo CampoFormativo usando la llave 'campo_formativo_id' (la FK)
        // y 'campo_id' (la PK en la tabla 'campos_formativos')
        return $this->belongsTo(CampoFormativo::class, 'campo_formativo_id', 'campo_id');
    }
}