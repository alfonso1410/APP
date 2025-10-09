<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SearchBar extends Component
{
    public string $action;
    public ?string $value;
    public string $placeholder;

    /**
     * Create a new component instance.
     */
    public function __construct(string $action, ?string $value = '', string $placeholder = 'Buscar...')
    {
        $this->action = $action;
        $this->value = $value;
        $this->placeholder = $placeholder;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.search-bar');
    }
}