<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NoSobreponerFechasPeriodoRule;
use Illuminate\Validation\Rule;

class StorePeriodoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check(); // O tu lógica de roles de admin
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Parámetros necesarios para la regla de solapamiento y rango:
        $cicloEscolarId = $this->input('ciclo_escolar_id');
        // 🛑 CAMBIO CRÍTICO: Obtenemos el valor de la fecha de inicio como string
        $fechaInicioInput = $this->input('fecha_inicio'); 

        return [
            'ciclo_escolar_id' => [
                'required',
                'integer',
                // Usamos la tabla 'ciclo_escolars' y clave 'ciclo_escolar_id'
                'exists:ciclo_escolars,ciclo_escolar_id' 
            ],
            'nombre' => [
                'required',
                'string',
                'max:100', 
                // El nombre debe ser único DENTRO de su ciclo escolar
                Rule::unique('periodos','nombre')->where(function ($query) use ($cicloEscolarId) {
                    return $query->where('ciclo_escolar_id', $cicloEscolarId);
                }),
            ],
            'fecha_inicio' => [
                'required',
                'date',
                // La regla 'before' evita que pasemos fechas inválidas a Carbon en la regla personalizada
                'before:fecha_fin', 
            ],
            'fecha_fin' => [
                'required',
                'date',
                'after:fecha_inicio',
                // 🛑 APLICACIÓN DE LA REGLA: Usamos $fechaInicioInput (el string)
                new NoSobreponerFechasPeriodoRule($cicloEscolarId, $fechaInicioInput)
            ],
            'estado' => ['required', Rule::in(['ABIERTO', 'CERRADO'])],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.unique' => 'Ya existe un periodo con este nombre en el ciclo escolar seleccionado.', 
            'fecha_inicio.before'   => 'La fecha de inicio debe ser anterior a la fecha de fin.',
            'fecha_fin.after'       => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ];
    }
}