<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Number extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($val, $prefix = null)
    {
        //
        $this->val = $val;
        $this->prefix = $prefix;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $val = $this->val;
        $style = '';
        if (is_numeric($val)) {
            if ($val < 0) {
                $style = 'text-danger fw-bold';
            }
        }

        return view('components.number', ['val' => $val, 'style' => $style, 'prefix' => $this->prefix]);

    }
}
