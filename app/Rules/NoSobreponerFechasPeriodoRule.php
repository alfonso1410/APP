<?php

namespace App\Rules;

use App\Models\Periodo;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Support\Carbon;

class NoSobreponerFechasPeriodoRule implements InvokableRule
{
    protected $fechaInicio;
    protected $cicloEscolarId;
    protected $ignorePeriodoId;

    /**
     * @param string $fechaInicioInput La fecha de inicio del periodo.
     * @param int $cicloEscolarId El ID del ciclo al que pertenece.
     * @param int|null $ignorePeriodoId El ID del periodo a ignorar (en actualización).
     */
    public function __construct($fechaInicioInput, $cicloEscolarId, $ignorePeriodoId = null)
    {
        $this->fechaInicio = Carbon::parse($fechaInicioInput);
        $this->cicloEscolarId = $cicloEscolarId;
        $this->ignorePeriodoId = $ignorePeriodoId;
    }

    public function __invoke($attribute, $value, $fail)
    {
        $fechaFin = Carbon::parse($value);

        // Lógica de solapamiento: (InicioA <= FinB) Y (FinA >= InicioB)
        $query = Periodo::query()
            // 1. LA CLAVE: Solo buscar en el ciclo escolar actual
            ->where('ciclo_escolar_id', $this->cicloEscolarId) 
            // 2. Lógica de solapamiento estándar
            ->where('fecha_inicio', '<=', $fechaFin)
            ->where('fecha_fin', '>=', $this->fechaInicio);

        // 3. Si estamos actualizando, ignoramos el ID del propio periodo
        if ($this->ignorePeriodoId) {
            $query->where('periodo_id', '!=', $this->ignorePeriodoId); 
        }

        if ($query->exists()) {
            $fail('Las fechas de este periodo se solapan con otro periodo ya existente en este ciclo escolar.');
        }
    }
}