<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ActividadMateria;
class GuardarCalificacionActividadRequest extends FormRequest
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
            'calificaciones'                            => ['required', 'array', 'min:1'],
            'calificaciones.*.actividad_id'             => ['required', 'exists:actividades_materia,actividad_id'],
            'calificaciones.*.alumno_id'                => ['required', 'exists:alumnos,alumno_id'],
            'calificaciones.*.calificacion_obtenida'    => ['nullable', 'numeric', 'min:0'],
            'calificaciones.*.estado_entrega'           => ['required', 'in:ENTREGADO,NO_ENTREGO,FALTA_JUSTIFICADA'],
            'calificaciones.*.observaciones'            => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $items = $this->input('calificaciones', []);
            if (!is_array($items) || empty($items)) {
                return;
            }

            // Obtener todas las actividades involucradas en una sola consulta
            $actividadIds = collect($items)->pluck('actividad_id')->unique()->filter();
            $actividades = ActividadMateria::whereIn('actividad_id', $actividadIds)->with('periodo')->get()->keyBy('actividad_id');

            foreach ($items as $index => $item) {
                $actividad = $actividades->get($item['actividad_id'] ?? null);

                if ($actividad) {
                    //  Validar que el periodo no esté cerrado
                    if ($actividad->periodo && $actividad->periodo->estado !== 'ABIERTO') {
                        $validator->errors()->add("calificaciones.{$index}.periodo", 'El periodo está cerrado. No se pueden editar calificaciones.');
                    }

                    //  Validar que la calificación no supere el valor_maximo
                    $estado = $item['estado_entrega'] ?? 'ENTREGADO';
                    $valor = $item['calificacion_obtenida'] ?? null;

                    if ($estado === 'ENTREGADO' && !is_null($valor)) {
                        if ((float)$valor > (float)$actividad->valor_maximo) {
                            $validator->errors()->add(
                                "calificaciones.{$index}.calificacion_obtenida",
                                "La calificación no puede exceder el valor máximo de la actividad ({$actividad->valor_maximo} pts)."
                            );
                        }
                    }
                }
            }
        });
    }
}

