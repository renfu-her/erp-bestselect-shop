<?php

namespace App\Http\Controllers\Cms\Frontend\Homepage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NavbarCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();

        return view('cms.frontend.homepage.navbar', [
        ]);
    }
}
