<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CustomerCoupon extends Model
{
    use HasFactory;
    protected $table = 'usr_customer_coupon';
    protected $guarded = [];

    public static function getList($customer_id, $used = null, $active = null)
    {

        $re = DB::table('usr_customer_coupon as cc')
            ->leftJoin('dis_discounts as discount', 'discount.id', '=', 'cc.discount_id')
            ->select('discount.*', 'discount.id as discount_id', 'cc.id as id')
            ->selectRaw('IF(active_sdate IS NULL,"",active_sdate) as active_sdate')
            ->selectRaw('IF(active_edate IS NULL,"",active_edate) as active_edate')
            ->where('cc.customer_id', $customer_id);

        if (!is_null($used)) {
            $re->where('cc.used', $used);
        }

        if ($active) {
            $n = now();
            $re->where('cc.active_sdate', '<=', $n)
                ->where('cc.active_edate', '>=', $n);
        }

        return $re;

    }

    public static function activeCoupon($order_id, $manual = 0)
    {
        $counpons = self::where('from_order_id', $order_id)
            ->whereNull('active_sdate')
            ->get();

        foreach ($counpons as $counpon) {
            if ($counpon->limit_day == 0) {
                $sdate = now();
                $edate = date('Y-m-d 23:59:59', strtotime(now() . ' + 50 years'));
            } else {
                $sdate = now();
                $edate = date('Y-m-d 23:59:59', strtotime(now() . " + $counpon->limit_day days"));
            }

            self::where('id', $counpon->id)->update([
                'active_sdate' => $sdate,
                'active_edate' => $edate,
            ]);
        }

    }
    public static function getCouponByCustomerCouponId($id)
    {
        return DB::table('usr_customer_coupon as cc')
            ->leftJoin('dis_discounts as discount', 'cc.discount_id', '=', 'discount.id')
            ->where('cc.id', $id)
            ->select('discount.*');
    }
}
