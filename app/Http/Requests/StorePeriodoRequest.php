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
        $fechaInicio = $this->input('fecha_inicio');

        return [
            'ciclo_escolar_id' => [
                'required',
                'integer',
                // **CORRECCIÓN:** Usamos la tabla 'ciclo_escolars' y clave 'ciclo_escolar_id'
                'exists:ciclo_escolars,ciclo_escolar_id' 
            ],
            'nombre' => [ // **CORRECCIÓN:** Usamos 'nombre' para coincidir con tu fillable
                'required',
                'string',
                'max:100', // Mantenemos 100 del original (tu modelo decía 50)
                // El nombre debe ser único DENTRO de su ciclo escolar
                Rule::unique('periodos','nombre')->where(function ($query) use ($cicloEscolarId) {
                    return $query->where('ciclo_escolar_id', $cicloEscolarId);
                }),
            ],
            'fecha_inicio' => [
                'required',
                'date',
                'before:fecha_fin',
            ],
            'fecha_fin' => [
                'required',
                'date',
                'after:fecha_inicio',
                // Aplicamos la regla para NO solapamiento y contención en el ciclo.
                new NoSobreponerFechasPeriodoRule($cicloEscolarId, $fechaInicio)
            ],
            'estado' => ['required', Rule::in(['ABIERTO', 'CERRADO'])],
        ];
    }

    public function messages(): array
    {
        return [
            // **CORRECCIÓN:** Mensaje adaptado al campo 'nombre'
            'nombre.unique' => 'Ya existe un periodo con este nombre en el ciclo escolar seleccionado.', 
            'fecha_inicio.before'   => 'La fecha de inicio debe ser anterior a la fecha de fin.',
            'fecha_fin.after'       => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ];
    }
}