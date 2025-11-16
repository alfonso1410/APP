<?php

namespace App\Rules;

use App\Models\Periodo;
use App\Models\CicloEscolar;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Support\Carbon;

class NoSobreponerFechasPeriodoRule implements InvokableRule
{
    protected $cicloEscolarId;
    protected $fechaInicioInput; // Almacena el string de la fecha de inicio
    protected $ignoreId;

    /**
     * @param int $cicloEscolarId El ID del ciclo escolar al que pertenece el periodo.
     * @param string $fechaInicioInput La fecha de inicio del periodo que se está validando (como string).
     * @param int|null $ignoreId El ID del periodo actual (para edición).
     */
    public function __construct($cicloEscolarId, $fechaInicioInput, $ignoreId = null)
    {
        $this->cicloEscolarId = $cicloEscolarId;
        $this->fechaInicioInput = $fechaInicioInput; 
        $this->ignoreId = $ignoreId;
    }

    /**
     * Lógica principal de la validación.
     *
     * @param string $attribute (Será 'fecha_fin')
     * @param mixed $value (El valor de fecha_fin)
     */
    public function __invoke($attribute, $value, $fail)
    {
        // 🛑 CORRECCIÓN DE SEGURIDAD: Prevenir fallo si la fecha de inicio es null/vacía (aunque 'required' debe atraparlo)
        if (empty($this->fechaInicioInput)) {
            return; 
        }
        
        // 1. Parseo de fechas (Aquí ya son strings válidos gracias a las reglas 'date' y 'before' previas)
        $fechaInicio = Carbon::parse($this->fechaInicioInput);
        $fechaFin = Carbon::parse($value);

        // --- 2. VALIDACIÓN CONTRA EL CICLO ESCOLAR PADRE (Contención) ---
        
        // 🛑 MEJORA: Usamos where() en lugar de find() para asegurar la búsqueda por la clave personalizada 'ciclo_escolar_id'
        $cicloEscolar = CicloEscolar::where('ciclo_escolar_id', $this->cicloEscolarId)->first();

        if (!$cicloEscolar) {
            return $fail('El ciclo escolar especificado no existe o no se pudo encontrar.');
        }

        $cicloInicio = Carbon::parse($cicloEscolar->fecha_inicio);
        $cicloFin = Carbon::parse($cicloEscolar->fecha_fin);

        // La fecha de inicio del periodo debe ser >= a la fecha de inicio del ciclo.
        // La fecha de fin del periodo debe ser <= a la fecha de fin del ciclo.
        if ($fechaInicio->lt($cicloInicio) || $fechaFin->gt($cicloFin)) {
            $fail('Las fechas del periodo deben estar completamente dentro del rango del ciclo escolar (' . $cicloInicio->format('d/m/Y') . ' - ' . $cicloFin->format('d/m/Y') . ').');
            return; 
        }

        // --- 3. VALIDACIÓN DE SOLAPAMIENTO ENTRE PERIODOS DEL MISMO CICLO ---

        // Lógica de Solapamiento: Busca periodos existentes (P) tal que:
        // (P.fecha_inicio <= Nuevo.fecha_fin) AND (P.fecha_fin >= Nuevo.fecha_inicio)
        $query = Periodo::query()
            ->where('ciclo_escolar_id', $this->cicloEscolarId) 
            ->where('fecha_inicio', '<=', $fechaFin) 
            ->where('fecha_fin', '>=', $fechaInicio); 

        // Ignoramos el periodo que se está editando (solo para Update)
        if ($this->ignoreId) {
            // Se usa 'periodo_id' ya que es la clave primaria del modelo Periodo
            $query->where('periodo_id', '!=', $this->ignoreId); 
        }

        if ($query->exists()) {
            $fail('Las fechas seleccionadas se solapan con un periodo ya existente dentro del ciclo escolar.');
        }
    }
}