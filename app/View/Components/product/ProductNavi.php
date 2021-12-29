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
    public function __construct($product)
    {
        //
        $this->product = $product;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $route_name = explode('.', Route::getCurrentRoute()->getName())[2];
        return view('components.product.product-navi', [
            'id' => $this->product->id,
            'type' => $this->product->type,
            'route_name' => $route_name,
        ]);
    }
}
