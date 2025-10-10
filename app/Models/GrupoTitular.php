<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoTitular extends Model
{
    use HasFactory;

    protected $table = 'grupo_titular';
    
    // Indicamos que esta tabla no tiene un ID autoincremental simple
    public $incrementing = false; 
    
    // Definimos las claves primarias compuestas (para el modelo)
    protected $primaryKey = ['grupo_id', 'maestro_id']; 

    // Relación: Un registro pertenece a UN Grupo
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'grupo_id', 'grupo_id');
    }

    // Relación: Un registro pertenece a UN Maestro (User)
    public function maestro()
    {
        return $this->belongsTo(User::class, 'maestro_id', 'id');
    }
}