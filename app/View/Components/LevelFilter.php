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
     * El constructor ahora acepta la ruta y el ID del nivel seleccionado.
     */
    public function __construct(
        public string $route, 
        public int $selectedNivel
    ) {
        $this->niveles = Nivel::all();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.level-filter');
    }
}