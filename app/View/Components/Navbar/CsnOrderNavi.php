<?php

namespace App\View\Components\Navbar;

use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

class CsnOrderNavi extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        //
        $this->id = $id;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $route_name = Route::getCurrentRoute()->getName();
        // dd(Route::getCurrentRoute()->getName());
        return view('components.navbar.csnorder-navi', [
            'id' => $this->id,
            'route_name' => $route_name,
        ]);
    }
}
