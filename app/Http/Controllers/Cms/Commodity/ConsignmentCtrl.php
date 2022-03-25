<?php

namespace App\Http\Controllers\Cms\Commodity;

use App\Http\Controllers\Controller;
use App\Models\Depot;
use Illuminate\Http\Request;

class ConsignmentCtrl extends Controller
{

    public function index(Request $request)
    {
        return view('cms.commodity.consignment.list', []);
    }

    public function create(Request $request)
    {
        return view('cms.commodity.consignment.edit', [
            'method' => 'create',
            'depotList' => Depot::all(),
            'formAction' => Route('cms.consignment.create'),
        ]);
    }
}

