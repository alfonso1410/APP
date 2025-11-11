<?php

namespace App\Rules;

use App\Models\Periodo;
use App\Models\CicloEscolar;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Support\Carbon;

class NoSobreponerFechasPeriodoRule implements InvokableRule
{
    protected $cicloEscolarId;
    protected $fechaInicio;
    protected $ignoreId;

    /**
     * @param int $cicloEscolarId El ID del ciclo escolar al que pertenece el periodo.
     * @param string $fechaInicioInput La fecha de inicio del periodo que se está validando.
     * @param int|null $ignoreId El ID del periodo actual (para edición).
     */
    public function __construct($cicloEscolarId, $fechaInicioInput, $ignoreId = null)
    {
        $this->cicloEscolarId = $cicloEscolarId;
        // Parsear inmediatamente para trabajar con objetos Carbon
        $this->fechaInicio = Carbon::parse($fechaInicioInput); 
        $this->ignoreId = $ignoreId;
    }

    /**
     * @param string $attribute (Será 'fecha_fin')
     * @param mixed $value (El valor de fecha_fin)
     */
    public function __invoke($attribute, $value, $fail)
    {
        $fechaFin = Carbon::parse($value);

        // --- 1. VALIDACIÓN CONTRA EL CICLO ESCOLAR PADRE (Contención) ---
        
        // Asumiendo que CicloEscolar usa 'id' como primary key, o que CicloEscolar::find() funciona con 'ciclo_escolar_id'.
        // Si tu modelo CicloEscolar también usa primaryKey = 'ciclo_escolar_id', debes usar:
        // $cicloEscolar = CicloEscolar::where('ciclo_escolar_id', $this->cicloEscolarId)->first();
        // **Usaremos find() asumiendo que el ID pasado es la clave correcta para find.**
        $cicloEscolar = CicloEscolar::find($this->cicloEscolarId);

        if (!$cicloEscolar) {
            return $fail('El ciclo escolar especificado no existe.');
        }

        $cicloInicio = Carbon::parse($cicloEscolar->fecha_inicio);
        $cicloFin = Carbon::parse($cicloEscolar->fecha_fin);

        // Verificar si el periodo está completamente contenido dentro del ciclo escolar.
        if ($this->fechaInicio->lt($cicloInicio) || $fechaFin->gt($cicloFin)) {
            $fail('Las fechas del periodo deben estar completamente dentro del rango del ciclo escolar (' . $cicloInicio->format('d/m/Y') . ' - ' . $cicloFin->format('d/m/Y') . ').');
            return; // Detener la ejecución si no está dentro del ciclo.
        }

        // --- 2. VALIDACIÓN DE SOLAPAMIENTO ENTRE PERIODOS DEL MISMO CICLO ---

        // Lógica de solapamiento: (InicioA <= FinB) AND (FinA >= InicioB)
        $query = Periodo::query()
            ->where('ciclo_escolar_id', $this->cicloEscolarId) // Solo periodos del mismo ciclo
            ->where('fecha_inicio', '<=', $fechaFin) // El inicio de un periodo existente es ANTES/IGUAL al FIN del nuevo
            ->where('fecha_fin', '>=', $this->fechaInicio); // El fin de un periodo existente es DESPUÉS/IGUAL al INICIO del nuevo

        // **CORRECCIÓN CRÍTICA:** Usamos 'periodo_id' para ignorar el registro,
        // ya que tu modelo Periodo tiene protected $primaryKey = 'periodo_id';
        if ($this->ignoreId) {
            $query->where('periodo_id', '!=', $this->ignoreId); 
        }

        if ($query->exists()) {
            $fail('Las fechas seleccionadas se solapan con un periodo ya existente dentro del ciclo escolar.');
        }
    }
}