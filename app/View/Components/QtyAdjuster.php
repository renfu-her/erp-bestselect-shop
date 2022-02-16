<?php

namespace App\View\Components;

use Illuminate\View\Component;

class QtyAdjuster extends Component
{
    public $name;
    public $value;
    public $setMin;
    public $setMax;
    public $size;
    public $minus;
    public $plus;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($name, $value = 0, $min = null, $max = null, $size = '', $minus = null, $plus = null)
    {
        $this->name = $name;
        $this->value = $value;
        $this->setMin = $min;
        $this->setMax = $max;
        $size = ($size === 'input-group-lg') ? 'lg' : $size;
        $size = ($size === 'input-group-sm') ? 'sm' : $size;
        $this->size = $size;
        $this->minus = $minus;
        $this->plus = $plus;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.qty-adjuster', [
            'name' => $this->name,
            'value' => $this->value,
            'min' => $this->setMin,
            'max' => $this->setMax,
            'size' => $this->size,
            'minus' => $this->minus,
            'plus' => $this->plus,
        ]);
    }
}
