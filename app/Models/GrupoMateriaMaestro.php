<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoMateriaMaestro extends Model
{
   use HasFactory;

    protected $table = 'grupo_materia_maestro';
    // Por convención, la clave primaria es 'id'

    // 1. Un registro pertenece a UN Maestro (User)
    public function maestro()
    {
        // La clave foránea es 'maestro_id' en esta tabla.
        // La clave local es 'id' en la tabla 'users'.
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
