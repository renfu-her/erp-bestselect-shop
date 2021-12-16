<?php

namespace App\View\Components\product;

use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

class ProductNavi extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
        //  $this->id = $id;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $id = Route::current()->parameters()['id'];
        $route_name = explode('.', Route::getCurrentRoute()->getName())[2];
        return view('components.product.product-navi', [
            'id' => $id,
            'route_name' => $route_name
        ]);
    }
}
