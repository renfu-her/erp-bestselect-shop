<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Editor extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($id, $classes = '')
    {
        $this->id = $id;
        $this->classes = $classes;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.editor', [
            'id' => $this->id,
            'classes' => $this->classes
        ]);
    }
}
