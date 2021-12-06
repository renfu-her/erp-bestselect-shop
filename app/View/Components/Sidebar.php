<?php

namespace App\View\Components\Cms;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

class Sidebar extends Component
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
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        // $adminAuths = request()->session()->get('adminAuths');
        $controllerName = class_basename(Route::getCurrentRoute()->getAction()['controller']);
        $controllerName = explode('@', $controllerName)[0];
       
        $menuId = '';
        /*
        if (isset($adminAuths->controllers->{$controllerName})) {
            $menuId = $adminAuths->controllers->{$controllerName}->menu_id;
        }
        */

        return view('components.sidebar', [
            'tree' => Auth::user()->menuTree(),
            'menuId' => 1,
            'controllerName' => $controllerName,
        ]);
    }

    
}
