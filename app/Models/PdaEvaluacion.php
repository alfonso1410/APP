<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PdaEvaluacion extends Model
{
    use HasFactory;

    protected $table = 'pda_evaluaciones';

    protected $fillable = [
        'alumno_id', 
        'periodo_id', 
        'materia_id', 
        'campo_formativo_id', 
        'observacion'
    ];
}