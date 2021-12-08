<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

class Topbar extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        // echo Route::currentRouteAction();

    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        // echo 'sa';
        // $userType = Auth::User()->userType;
        // switch ($userType) {
        //     case "admin":
        //         $logout = 'admin.logout';
        //         break;
        //     case "deliveryman":
        //         $logout = 'deliveryman.logout';
        //         break;
        //     default:
        //         $logout = "logout";
        // }

        return view('components.topbar', [
            'name' => isset(Auth::User()->name) ? Auth::User()->name : '旅客',
            'userType' => 'user',
            'logout' => 'logout'
        ]);
    }
}
