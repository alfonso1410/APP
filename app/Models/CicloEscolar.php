<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CicloEscolar extends Model
{
use HasFactory;

    // Especifica el nombre de la tabla si no es 'ciclo_escolares' (plural estándar)
    // protected $table = 'ciclo_escolars'; // <-- Descomenta si tu tabla se llama así

    protected $primaryKey = 'ciclo_escolar_id';
    protected $table = 'ciclo_escolars';    
    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    /**
     * Un Ciclo Escolar tiene muchos Grupos.
     */
    public function grupos(): HasMany
    {
        return $this->hasMany(Grupo::class, 'ciclo_escolar_id', 'ciclo_escolar_id');
    }

    /**
     * Un Ciclo Escolar tiene muchos Periodos.
     */
    public function periodos(): HasMany
    {
        return $this->hasMany(Periodo::class, 'ciclo_escolar_id', 'ciclo_escolar_id');
    }
}