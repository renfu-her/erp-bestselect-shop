<?php

namespace App\Models;

use App\Enums\Discount\DisStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CustomerCoupon extends Model
{
    use HasFactory;
    protected $table = 'usr_customer_coupon';
    protected $guarded = [];

    public static function getList($customer_id, $used = null, DisStatus $status = null)
    {
        $sub = Discount::_discountStatus();

        //  $re = DB::table(DB::raw("({$sub->toSql()}) as sub"))

        $re = DB::table('usr_customer_coupon as cc')
            ->leftJoin(DB::raw("({$sub->toSql()}) as discount"), function ($join) {
                $join->on('cc.discount_id', '=', 'discount.id');
            })
            ->select('discount.*')
            ->where('customer_id', $customer_id);

        if (!is_null($used)) {
            $re->where('cc.used', $used);
        }

        if ($status) {
            $re->where('status_code', $status);
        }

        return $re;

    }
}
