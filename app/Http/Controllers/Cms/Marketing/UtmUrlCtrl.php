<?php

namespace App\Http\Controllers\Cms\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UtmUrlCtrl extends Controller
{
    public function index(Request $request)
    {
        return view('cms.marketing.utm_url.index', [
        ]);
    }
}
