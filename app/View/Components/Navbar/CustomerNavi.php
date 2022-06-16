<?php

namespace App\View\Components\Navbar;

use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

class CustomerNavi extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($customer)
    {
        $this->customer = $customer;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $route_name = explode('.', Route::getCurrentRoute()->getName())[2];

        return view('components.navbar.customer-navi', [
            'customer' => $this->customer,
            'route_name' => $route_name,
        ]);
    }
}
