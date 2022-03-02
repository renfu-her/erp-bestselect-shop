<?php

namespace App\Http\Controllers\Api\Cms\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

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

}
