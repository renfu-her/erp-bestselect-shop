<?php

namespace App\Http\Middleware;

use App\Models\CustomerIdentity;
use Closure;
use Illuminate\Http\Request;

class AuthIdentityApiCustomer
{

    public function handle(Request $request, Closure $next)
    {
        $user = CustomerIdentity::where('customer_id', $request->user()->id)->where('identity', 'customer');
        $userGet = $user->get()->first();
        if (null != $userGet && $userGet) {
            return $next($request);
        } else {
            return response()->json(['status' => 'A02', 'message' => '身分驗證失敗'], 401);
        }
    }
}
