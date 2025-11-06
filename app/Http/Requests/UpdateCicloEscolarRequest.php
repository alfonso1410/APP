<?php

namespace App\Http\Requests;

use App\Rules\NoSobreponerFechasCicloRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCicloEscolarRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Te recomiendo usar la lógica de roles que discutimos:
        // return $this->user()->can('es-admin');
        return auth()->check();
    }

    public function rules(): array
    {
        // --- INICIO DE LA CORRECCIÓN ---
        
        // 1. Obtenemos el modelo usando el nombre correcto del parámetro de ruta ('ciclo_escolar')
        //    que fue definido por tu Route::resource('ciclo-escolar', ...).
        $ciclo = $this->route('ciclo_escolar');

        // 2. Ahora que $ciclo es el modelo válido y no 'null', obtenemos su ID.
        $cicloId = $ciclo->ciclo_escolar_id; 
        
        // --- FIN DE LA CORRECCIÓN ---
        
        return [
            'nombre' => [
                'required',
                'string',
                'max:50',
                // Esta regla ahora funciona porque $cicloId es válido
                Rule::unique('ciclo_escolars', 'nombre')->ignore($cicloId, 'ciclo_escolar_id')
            ],
            'fecha_inicio' => [
                'required',
                'date'
            ],
            'fecha_fin' => [
                'required',
                'date',
                'after:fecha_inicio',
                // Esta regla también funciona ahora
                new NoSobreponerFechasCicloRule($this->input('fecha_inicio'), $cicloId)
            ],
            'estado' => ['required', Rule::in(['ACTIVO', 'CERRADO'])],
            'ciclo_escolar_id' => 'required|integer', // Para la lógica del modal
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'nombre.unique'   => 'El nombre de este ciclo escolar ya ha sido registrado.',
        ];
    }
}