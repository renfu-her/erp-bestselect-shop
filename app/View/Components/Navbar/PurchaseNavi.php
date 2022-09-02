<?php

namespace App\View\Components\Navbar;

use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

class PurchaseNavi extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($id, $purchaseData = null)
    {
        //
        $this->id = $id;
        $this->purchaseData = $purchaseData;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $route_name = explode('.', Route::getCurrentRoute()->getName())[2];
        return view('components.navbar.purchase-navi', [
            'id' => $this->id,
            'purchaseData' => $this->purchaseData,
            'route_name' => $route_name,
        ]);
    }
}
