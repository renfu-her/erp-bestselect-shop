<?php

namespace App\View\Components\Forms;

use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

class SearchAccount extends Component
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
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.forms.search-account', [
            'routeName' => class_basename(Route::getCurrentRoute()->getName()),
        ]);
    }
}
