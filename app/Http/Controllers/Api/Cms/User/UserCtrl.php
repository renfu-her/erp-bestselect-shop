<?php

namespace App\Http\Controllers\Api\Cms\User;

use App\Enums\Customer\ProfitStatus;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerIdentity;
use App\Models\CustomerProfit;
use App\Models\User;
use App\Models\UserSalechannel;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class UserCtrl extends Controller
{
    //

    public function checkCustomerBind(Request $request, $email = null)
    {

        if (!$email) {
            return response()->json([
                'status' => 'E01',
                'message' => '缺少email',
            ]);
        }

        $re = User::checkCustomerBinded($email);

        if ($re['success'] == '1') {
            return response()->json([
                'status' => '0',
                'message' => '可以綁定',
            ]);
        } else {
            return response()->json([
                'status' => $re['code'],
                'message' => $re['error_msg'],
            ]);
        }

    }

    public function getCustomerSalechannels(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'customer_id' => ['required'],

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E01',
                'message' => $validator->errors(),
            ]);
        }
        $d = $request->all();

        $re = CustomerIdentity::getSalechannels($d['customer_id'], [1])->get()->toArray();

        return response()->json([
            'status' => '0',
            'data' => $re,
        ]);

    }

    public function getUserSalechannels(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'customer_id' => ['required'],

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E01',
                'message' => $validator->errors(),
            ]);
        }
        $d = $request->all();

        $user = User::where('customer_id', $d['customer_id'])->get()->first();
        if (!$user) {
            return response()->json([
                'status' => 'E07',
                'message' => '此帳號無綁定消費者',
            ]);
        }

        $mcode = '';
        if (CustomerProfit::getProfitData($d['customer_id'], ProfitStatus::Success())) {
            $mcode = Customer::where('id', $d['customer_id'])->get()->first()->sn;
        }

        $re = UserSalechannel::getSalechannels($user->id)->get()->toArray();

        return response()->json([
            'status' => '0',
            'data' => $re,
            'mcode' => $mcode,
        ]);

    }

    public function getCustomers(Request $request)
    {

        $d = $request->all();
        $keyword = Arr::get($d, 'keyword', null);
        $profit = Arr::get($d, 'profit', null);

        return response()->json([
            'status' => '0',
            'data' => Customer::getCustomerBySearch($keyword, $profit)->select(['customer.id',
            'customer.name',
            'customer.sn as mcode',
            'customer.email'])->get(),
        ]);

    }

}
