<?php

namespace App\Http\Controllers\Cms\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomepageCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();

        return view('cms.frontend.homepage.list', [
        ]);
    }
}
