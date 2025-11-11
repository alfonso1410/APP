<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NoSobreponerFechasPeriodoRule;
use Illuminate\Validation\Rule;
use App\Models\Periodo; // Importamos el modelo para claridad de tipos

class UpdatePeriodoRequest extends FormRequest
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
        /** @var Periodo $periodo */
        // Asumiendo Route Model Binding donde {periodo} inyecta el modelo Periodo
        $periodo = $this->route('periodo');

        // --- 1. Obtener claves y IDs de forma segura ---
        // Usamos la clave primaria personalizada 'periodo_id'
        $periodoId = $periodo ? $periodo->periodo_id : null;
        
        // Usamos el ciclo_escolar_id del modelo (no debería ser editable en el formulario de edición)
        $cicloEscolarId = $periodo ? $periodo->ciclo_escolar_id : null;

        // La fecha de inicio que se está validando (puede ser la nueva del request o la antigua del modelo)
        $fechaInicio = $this->input('fecha_inicio') ?? ($periodo ? $periodo->fecha_inicio : null);
        
        // Comprobación de que tenemos IDs esenciales para la regla
        if (is_null($cicloEscolarId) || is_null($periodoId)) {
            // Esto solo ocurriría si el Route Model Binding falla o la URL es incorrecta
            abort(404, 'No se pudo identificar el periodo a actualizar.');
        }

        return [
            // No validamos 'ciclo_escolar_id' ya que no se debería cambiar en la edición del periodo.
            // Si viniera en el request (ej. modal), asumimos que es el valor correcto.
            
            'nombre' => [ // **AJUSTADO:** Usamos 'nombre'
                'required',
                'string',
                'max:100',
                // Unicidad del nombre DENTRO de su ciclo, ignorando el periodo actual
                Rule::unique('periodos','nombre')
                    ->where(function ($query) use ($cicloEscolarId) {
                        return $query->where('ciclo_escolar_id', $cicloEscolarId);
                    })
                    // **AJUSTADO:** Ignoramos por la clave 'periodo_id'
                    ->ignore($periodoId, 'periodo_id'), 
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
                // Aplicamos la regla con los IDs y claves correctas
                new NoSobreponerFechasPeriodoRule($cicloEscolarId, $fechaInicio, $periodoId)
            ],
            'estado' => ['required', Rule::in(['ABIERTO', 'CERRADO'])],
        ];
    }

    public function messages(): array
    {
        return [
            // **AJUSTADO:** Mensaje adaptado al campo 'nombre'
            'nombre.unique' => 'Ya existe un periodo con este nombre en el ciclo escolar seleccionado.', 
            'fecha_inicio.before'   => 'La fecha de inicio debe ser anterior a la fecha de fin.',
            'fecha_fin.after'       => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ];
    }
}