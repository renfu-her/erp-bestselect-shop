<?php

namespace App\View\Components\Navbar;

use App\Models\Customer;
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
        // dd(Auth::user()->getUserCustomer(Auth::user()->customer_id));
        $param = Route::current()->parameters();
        $customer_data = Customer::where('id', $param['id'])->get()->first();

        return view('components.navbar.customer-navi', [
            'customer' => $this->customer,
            'route_name' => $route_name,
            'customer_data' => $customer_data,
        ]);
    }
}
