<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Periodo;
class StoreActividadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'grupo_id' => ['required','exists:grupos,grupo_id'],
            'materia_id' => ['required','exists:materias,materia_id'],
            'materia_criterio_id' => ['required','exists:materia_criterios,materia_criterio_id'],
            'periodo_id'    => ['required','exists:periodos,periodo_id'],  
          //  'maestro_id'    => ['required','exists:users,id'],
            'nombre_actividad'  => ['required','string','max:150'],
            'descripcion'   => ['nullable', 'string'],
            'fecha_actividad'   => ['required','date'],
            'valor_maximo'  => ['required','numeric','min:0.01','max:100'],
            'grupos_replicar' => ['nullable', 'array'],
            'grupos_replicar.*'  => ['exists:grupos,grupo_id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->periodo_id) {
                $periodo = Periodo::find($this->periodo_id);
                if ($periodo && $periodo->estado !== 'ABIERTO') {
                    $validator->errors()->add('periodo_id', 'El periodo seleccionado se encuentra cerrado.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'valor_maximo.min' => 'El valor máximo debe ser mayor a 0 puntos.',
            'nombre_actividad.required' => 'El nombre de la actividad es obligatorio.',
        ];
    }
}
