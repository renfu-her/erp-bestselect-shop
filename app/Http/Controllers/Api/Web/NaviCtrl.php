<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\NaviNode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NaviCtrl extends Controller
{
    //
    public function __invoke(Request $request)
    {
        if (!Cache::has('tree')) {
            NaviNode::cacheProcess();
        }

        return response()->json(['status' => '0', 'data' => Cache::get('tree')]);
    }
}
