<?php

namespace App\Http\Controllers\Cms\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomepageCtrl extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query();

        return view('cms.settings.homepage.list', [
        ]);
    }
}
