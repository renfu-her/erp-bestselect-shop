<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Modal extends Component
{
    public $id;
    public $closeBtn;
    public $cancelBtn;
    public $size;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($id, $size = '', $closeBtn = 'TRUE', $cancelBtn = 'TRUE')
    {
        $this->id = $id;
        $this->closeBtn = strtoupper($closeBtn);
        $this->cancelBtn = strtoupper($cancelBtn);
        $this->size = $size;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        
        return view('components.modal', [
            'id' => $this->id,
            'closeBtn' => $this->closeBtn,
            'cancelBtn' => $this->cancelBtn,
            'size' => $this->size
        ]);
    }
}
