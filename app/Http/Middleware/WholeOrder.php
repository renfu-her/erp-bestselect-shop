<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use Spatie\Permission\Exceptions\UnauthorizedException;

class WholeOrder
{
    public function handle(Request $request, Closure $next){
        $user = Auth::user();
        if (preg_match("/cms\\/order\\/detail\\/(\d+).*/",
            $request->path(),
            $match)) {
            $requestOrderId = $match[1];
        } else {
            $requestOrderId = '';
        }

        if (Order::canViewWholeOrder()) {
            return $next($request);
        } else {
            if (DB::table('usr_users')
                ->where('usr_users.id', $user->id)
                ->whereNotNull('usr_users.customer_id')
                ->join('usr_customers', 'usr_customers.id', '=', 'usr_users.customer_id')
                ->exists()
            ) {
                $selfData = DB::table('usr_users')
                    ->where('usr_users.id', $user->id)
                    ->whereNotNull('usr_users.customer_id')
                    ->join('usr_customers', 'usr_customers.id', '=', 'usr_users.customer_id')
                    ->select([
                        'usr_customers.id',
                        'usr_customers.email',
                    ])
                    ->get()
                    ->first();

                //只能看到自己和分潤人是自己的訂單
                $isSelfOrder = DB::table('ord_orders')
                    ->where('id', $requestOrderId)
                    ->where('email', $selfData->email)
                    ->exists();
                $isSelfProfitOrder = DB::table('ord_order_profit')
                    ->where('order_id', $requestOrderId)
                    ->where('ord_order_profit.customer_id', $selfData->id)
                    ->exists();
                if ($isSelfOrder || $isSelfProfitOrder) {
                    return $next($request);
                } else {
                    throw UnauthorizedException::forPermissions(['訂單管理', '權限不夠,不能瀏覽其他人的訂單']);
                }
            } else {
                //沒有瀏覽全部訂單權限，會員又沒綁定
                throw UnauthorizedException::forPermissions(['訂單管理', '沒有會員綁定無法確認訂單權限']);
            }
        }
    }
}
