<?php

namespace App\View\Components;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
        $domain = '';
        switch ($_SERVER['SERVER_NAME']) {
            case '127.0.0.1':
            case 'localhost':
                $domain = 'http://localhost:3000';
                break;
            case 'dev-shop.bestselection.com.tw':
                $domain = 'https://dev-shopp.bestselection.com.tw';
                break;

            case 'release.bestselection.com.tw':
            default:
                $domain = 'https://shopp.bestselection.com.tw';
                // $domain = 'https://www.bestselection.com.tw';
                break;
        }

        if (Auth::user()) {
            $customer = User::getUserCustomer(Auth::user()->id);
            $domain = $domain . '?mcode=' . ($customer ? $customer->sn : '');
        }
        return view('components.topbar', [
            'name' => isset(Auth::User()->name) ? Auth::User()->name : '旅客',
            'userType' => 'user',
            'url' => $domain,
            'logout' => 'logout',
        ]);
    }
}
