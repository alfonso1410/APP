<?php
namespace App\View\Components;

use App\Models\Nivel;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

class LevelFilter extends Component
{
    public Collection $niveles;

    /**
     * El constructor ahora acepta una opción para mostrar el filtro "Sin Asignar".
     */
    public function __construct(
        public string $route, 
        public int $selectedNivel,
        // --- AÑADE ESTA LÍNEA ---
        public bool $showUnassigned = true // Por defecto, siempre se muestra
    ) {
        $this->niveles = Nivel::all();
    }

    public function render(): View
    {
        return view('components.level-filter');
    }
}