<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Toast extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        $data = null;
        if (request()->session()->has('toast_status')) {
            $data = request()->session()->get('toast_status');
        }

        // echo request()->session()->get('status');
        return view('components.toast', ['data' => $data]);
    }
}
