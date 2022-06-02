<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerDividend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

    public static function getDividendPoint(Request $request)
    {
        if (Auth::guard('sanctum')->check()) {
            $customer = $request->user();
        } else {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'E01',
                    'message' => $validator->errors(),
                ]);
            }

            $customer = Customer::where('id', $request->input('customer_id'))->get()->first();
        }
        $dividend = CustomerDividend::getDividend($customer->id)->get()->first();
     
       
        return response()->json([
            'status' => '0',
            'data' => $dividend ? $dividend->dividend : 0,
        ]);

    }

    
}
