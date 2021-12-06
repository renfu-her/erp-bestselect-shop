<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

class Breadcrumb extends Component
{

    public $value;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($value = null)
    {

        //
        $this->value = $value;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        // dd(Route::currentRouteName());
        // $controllerName = class_basename(Route::getCurrentRoute()->getAction()['controller']);
        return view('components.breadcrumb', []);
    }
}
