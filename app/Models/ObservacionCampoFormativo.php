<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObservacionCampoFormativo extends Model
{
    use HasFactory;

    /**
     * El nombre de la tabla asociada con el modelo.
     * Es necesario especificarlo porque el nombre del modelo
     * no sigue la convención estándar de pluralización de Laravel.
     *
     * @var string
     */
    protected $table = 'observaciones_campos_formativos';

    /**
     * Los atributos que se pueden asignar en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'alumno_id',
        'periodo_id',
        'campo_id',
        'observaciones',
    ];

    /**
     * Define la relación: Una observación pertenece a un Alumno.
     */
    public function alumno(): BelongsTo
    {
        // Asegúrate que 'alumno_id' sea la PK en tu tabla 'alumnos'
        return $this->belongsTo(Alumno::class, 'alumno_id', 'alumno_id');
    }

    /**
     * Define la relación: Una observación pertenece a un Periodo.
     */
    public function periodo(): BelongsTo
    {
        // Asegúrate que 'periodo_id' sea la PK en tu tabla 'periodos'
        return $this->belongsTo(Periodo::class, 'periodo_id', 'periodo_id');
    }

    /**
     * Define la relación: Una observación pertenece a un Campo Formativo.
     */
    public function campoFormativo(): BelongsTo
    {
        // Asegúrate que 'campo_id' sea la PK en tu tabla 'campos_formativos'
        return $this->belongsTo(CampoFormativo::class, 'campo_id', 'campo_id');
    }
}