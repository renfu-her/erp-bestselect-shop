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
        //優惠券對應群組
        $dis_discount_collection = DB::table('dis_discount_collection as dis_dc')
            ->select('dis_dc.discount_id')
            ->selectRaw('GROUP_CONCAT(DISTINCT dis_dc.collection_id) as collection_ids');

        $re = DB::table('usr_customer_coupon as cc')
            ->leftJoin('dis_discounts as discount', 'discount.id', '=', 'cc.discount_id')
            ->leftJoinSub($dis_discount_collection, 'dis_dc', function($join) {
                $join->on('dis_dc.discount_id', '=', 'discount.id');
            })
            ->select('discount.*', 'discount.id as discount_id', 'cc.id as id','cc.used')
            ->selectRaw('ifnull(dis_dc.collection_ids, null) as collection_ids')
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

    public static function activeCoupon($order_id, $date = null)
    {
        $counpons = self::where('from_order_id', $order_id)
            ->whereNull('active_sdate')
            ->get();

        if (!$date) {
            $date = now();
        }

        foreach ($counpons as $counpon) {
            if ($counpon->limit_day == 0) {
                $sdate = now();
                $edate = date('Y-m-d 23:59:59', strtotime($date . ' + 10 years'));
            } else {
                $sdate = now();
                $edate = date('Y-m-d 23:59:59', strtotime($date . " + $counpon->limit_day days"));
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
