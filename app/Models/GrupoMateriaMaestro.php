<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoMateriaMaestro extends Model
{
    use HasFactory;

    protected $table = 'grupo_materia_maestro';
    // Por convención, la clave primaria es 'id'

    // --- INICIO DE LA CORRECCIÓN ---
    /**
     * Los atributos que deben ser visibles en la serialización JSON.
     * Esto es necesario para que el modal pueda acceder
     * a los modelos anidados de 'maestro' y 'grupo'.
     */
    protected $visible = [
        'maestro',
        'grupo',
        // Añadimos las llaves por si acaso
        'maestro_id',
        'grupo_id',
        'materia_id'
    ];
    // --- FIN DE LA CORRECCIÓN ---

    // 1. Un registro pertenece a UN Maestro (User)
    public function maestro()
    {
        return $this->belongsTo(User::class, 'maestro_id', 'id');
    }

    // 2. Un registro pertenece a UN Grupo
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id', 'grupo_id');
    }

    // 3. Un registro pertenece a UNA Materia
    public function materia()
    {
        return $this->belongsTo(Materia::class, 'materia_id', 'materia_id');
    }
}