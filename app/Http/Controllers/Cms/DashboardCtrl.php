<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardCtrl extends Controller
{
    //
    public function __invoke(Request $request)
    {
        return view('cms.dashboard');
        
    }

}
