<?php

namespace App\Http\Controllers\Cms\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreditManagerCtrl extends Controller
{
    public function index(Request $request)
    {
        return view('cms.settings.credit_manager.list', [
        ]);
    }

}
