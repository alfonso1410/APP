<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// --- AÑADIR IMPORTS ---
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Grado;
use App\Models\CampoFormativo;
use App\Models\Materia;

class EstructuraCurricular extends Model
{
    use HasFactory;

    protected $table = 'estructura_curricular';

    /**
     * Tu tabla SÍ tiene timestamps (created_at/updated_at),
     * así que dejamos que Eloquent los maneje (no añadimos $timestamps = false).
     */

    // --- INICIO DE LA CORRECCIÓN ---
    /**
     * Define qué atributos/relaciones deben ser visibles
     * cuando este modelo se convierte a JSON (para Alpine.js).
     */
    protected $visible = [
        'grado_id',
        'campo_id',
        'materia_id',
        'grado', // <-- ¡LA CLAVE! Asegura que la relación se incluya en el JSON.
    ];
    // --- FIN DE LA CORRECCIÓN ---

    // 1. Relación con Grado: Pertenece a un Grado.
    public function grado(): BelongsTo // <-- Añadido Type-hint
    {
        return $this->belongsTo(Grado::class, 'grado_id', 'grado_id');
    }

    // 2. Relación con Campo Formativo: Pertenece a un Campo Formativo.
    public function campoFormativo(): BelongsTo // <-- Añadido Type-hint
    {
        return $this->belongsTo(CampoFormativo::class, 'campo_id', 'campo_id');
    }

    // 3. Relación con Materia: Pertenece a una Materia.
    public function materia(): BelongsTo // <-- Añadido Type-hint
    {
        return $this->belongsTo(Materia::class, 'materia_id', 'materia_id');
    }
    
    // Indica a Eloquent que la clave no es autoincremental
    public $incrementing = false; 

    // --- CORRECCIÓN ---
    // Se elimina la definición de clave primaria compuesta, 
    // ya que Eloquent no la soporta.
    // protected $primaryKey = ['grado_id', 'campo_id', 'materia_id']; // <-- LÍNEA ELIMINADA
}