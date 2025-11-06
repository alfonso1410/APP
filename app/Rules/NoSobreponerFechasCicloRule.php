<?php

namespace App\Rules;

use App\Models\CicloEscolar;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Support\Carbon;

class NoSobreponerFechasCicloRule implements InvokableRule
{
    protected $ignoreId;
    protected $fechaInicio;

    public function __construct($fechaInicioInput, $ignoreId = null)
    {
        $this->fechaInicio = Carbon::parse($fechaInicioInput);
        $this->ignoreId = $ignoreId;
    }

    /**
     * @param  string  $attribute (Será 'fecha_fin')
     * @param  mixed  $value (El valor de fecha_fin)
     */
    public function __invoke($attribute, $value, $fail)
    {
        $fechaFin = Carbon::parse($value);

        // Lógica de solapamiento: (InicioA <= FinB) Y (FinA >= InicioB)
        $query = CicloEscolar::query()
            ->where('fecha_inicio', '<=', $fechaFin) // El inicio de un ciclo existente es ANTES/IGUAL al FIN del nuevo
            ->where('fecha_fin', '>=', $this->fechaInicio); // El fin de un ciclo existente es DESPUÉS/IGUAL al INICIO del nuevo

        if ($this->ignoreId) {
            $query->where('ciclo_escolar_id', '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            $fail('Las fechas seleccionadas se solapan con un ciclo escolar ya existente.');
        }
    }
}