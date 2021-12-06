<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Calendar extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($id, $readOnly = 'true', $create = 'false', $classes = '')
    {
        $this->id = $id;
        $this->readOnly = ($readOnly == 'true') ? true : false;
        $this->create = ($create == 'true') ? true : false;
        $this->classes = $classes;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.calendar', [
            'id' => $this->id,
            'readOnly' => $this->readOnly,
            'create' => $this->create,
            'classes' => $this->classes
        ]);
    }
}
