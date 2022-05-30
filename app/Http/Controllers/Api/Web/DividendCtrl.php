<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\CustomerDividend;
use Illuminate\Http\Request;

class DividendCtrl extends Controller
{
    //

    public function getDividend(Request $request)
    {

        $dividend = CustomerDividend::getDividend($request->user()->id)->get()->first()->dividend;
        $typeGet = CustomerDividend::getList($request->user()->id, 'get')->get();
        $typeUsed = CustomerDividend::getList($request->user()->id, 'used')->get();
        return [
            'status' => '0',
            'data' => [
                'dividend' => $dividend,
                'get_record' => $typeGet,
                'use_record' => $typeUsed,
            ],
        ];
    }
}
