<?php

namespace App\Http\Requests;

use App\Rules\NoSobreponerFechasCicloRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCicloEscolarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check(); // O tu lógica de roles de admin
    }

    public function rules(): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'max:50',
                'unique:ciclo_escolars,nombre' // Tu tabla es ciclo_escolars
            ],
            'fecha_inicio' => [
                'required',
                'date'
            ],
            'fecha_fin' => [
                'required',
                'date',
                'after:fecha_inicio', // 'after' es más estricto que 'after_or_equal'
                new NoSobreponerFechasCicloRule($this->input('fecha_inicio'))
            ],
            'form_type' => 'required|string', // Lo mantenemos por la lógica de modales
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