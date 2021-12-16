<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\View\Components\Forms\Input as B_Input;
use App\View\Components\Forms\InsertField as B_InsertField;
use App\View\Components\Forms\FormGroup as B_FormGroup;
use App\View\Components\Forms\SearchAccount as B_SearchAccount;
use App\View\Components\Topbar as B_Topbar;
use App\View\Components\Breadcrumb as B_Breadcrumb;
use App\View\Components\Sidebar as B_Sidebar;
use App\View\Components\Calendar as B_Calendar;
use App\View\Components\Editor as B_Editor;
use App\View\Components\Modal as B_Modal;
use App\View\Components\Toast as B_Toast;
use App\View\Components\product\ProductNavi;

class ComponentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::component('b-input', B_Input::class);
        Blade::component('b-insert-field', B_InsertField::class);
        Blade::component('b-form-group', B_FormGroup::class);
        Blade::component('b-search-account', B_SearchAccount::class);
        Blade::component('b-topbar', B_Topbar::class);
        Blade::component('b-sidebar', B_SideBar::class);
        Blade::component('b-breadcrumb', B_Breadcrumb::class);
        Blade::component('b-calendar', B_Calendar::class);
        Blade::component('b-editor', B_Editor::class);
        Blade::component('b-modal', B_Modal::class);
        Blade::component('b-toast', B_Toast::class);
        Blade::component('b-prd-navi', ProductNavi::class);
        
    }
}
