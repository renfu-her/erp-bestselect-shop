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
    public function __construct($id, $height = '', $classes = '', $colorTool = true)
    {
        $this->id = $id;
        $this->height = $height;
        $this->classes = $classes;
        $this->colorTool = ($colorTool == 'true') ? true : false;
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
            'height' => $this->height,
            'classes' => $this->classes,
            'colorTool' => $this->colorTool,
        ]);
    }
}
