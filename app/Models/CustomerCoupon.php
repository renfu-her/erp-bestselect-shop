<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Enums\Discount\DisStatus;

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
            ->leftJoinSub($dis_discount_collection, 'dis_dc', function ($join) {
                $join->on('dis_dc.discount_id', '=', 'discount.id');
            })
            ->select('discount.*', 'discount.id as discount_id', 'cc.id as id', 'cc.used')
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
                ->where('cc.active_edate', '>=', $n)
                ->where('cc.discount', 1);
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


    public static function discount_expiring(
        $category = null,
        $title = null,
        $method_code = null,
        $status_code = null,
        $is_global = null,
        $mail_sended = 'all',
        $start_date = null,
        $end_date = null,
        $customer_coupon_id = null
    ) {

        $now = date('Y-m-d H:i:s');

        $selectStatus = "CASE
            WHEN active=0
            THEN '" . DisStatus::D03()->description . "'
            WHEN '$now' BETWEEN start_date AND end_date THEN '" . DisStatus::D01()->description . "'
            WHEN '$now' > end_date THEN '" . DisStatus::D02()->description . "'
            ELSE '" . DisStatus::D00()->description . "' END as status";

        $selectStatusCode = "CASE
            WHEN active=0
            THEN '" . DisStatus::D03()->value . "'
            WHEN '$now' BETWEEN start_date AND end_date THEN '" . DisStatus::D01()->value . "'
            WHEN '$now' > end_date THEN '" . DisStatus::D02()->value . "'
            ELSE '" . DisStatus::D00()->value . "' END as status_code";

        $sub = Discount::select('*')
            ->selectRaw($selectStatus)
            ->selectRaw($selectStatusCode)
            ->withTrashed();

        $query = DB::table('usr_customer_coupon as u_coupon')
            ->leftJoin('usr_customers as customer', 'customer.id', '=', 'u_coupon.customer_id')
            ->leftJoinSub("{$sub->toSql()}", 'discount', function ($join) {
                $join->on('discount.id', 'u_coupon.discount_id');
            })
            ->leftJoin('ord_orders as used_orders', 'used_orders.id', '=', 'u_coupon.order_id')
            ->leftJoin('ord_orders as from_orders', 'from_orders.id', '=', 'u_coupon.from_order_id')
            ->where('u_coupon.used', '=', 0)
            ->select(
                'u_coupon.id',
                'u_coupon.limit_day',
                'u_coupon.active_sdate',
                'u_coupon.active_edate',
                'u_coupon.used_at',
                'u_coupon.mail_subject',
                'u_coupon.mail_content',
                'u_coupon.mail_sended_at',

                'discount.title',
                'discount.sn',
                'discount.category_code',
                'discount.category_title',
                'discount.method_code',
                'discount.method_title',
                'discount.discount_value',
                'discount.is_grand_total',
                'discount.active',
                'discount.usage_count',
                'discount.max_usage',
                'discount.min_consume',
                'discount.min_qty',
                'discount.life_cycle',
                'discount.is_global',
                'discount.start_date',
                'discount.end_date',
                'discount.mail_subject AS default_mail_subject',
                'discount.mail_content AS default_mail_content',

                'discount.status',
                'discount.status_code',

                DB::raw('IF(u_coupon.order_id IS NULL, null, used_orders.id) AS used_order_id'),
                DB::raw('IF(u_coupon.order_id IS NULL, null, used_orders.sn) AS used_order_sn'),
                DB::raw('IF(u_coupon.from_order_id = 0, null, from_orders.id) AS from_order_id'),
                DB::raw('IF(u_coupon.from_order_id = 0, null, from_orders.sn) AS from_order_sn'),

                'customer.email',
                'customer.name'
            )
            ->orderBy('u_coupon.active_edate', 'asc');

        if ($category) {
            if (is_array($category)) {
                $query->whereIn('discount.category_code', $category);
            } else {
                $query->where('discount.category_code', $category);
            }
        }

        if ($title) {
            $query->where('discount.title', 'like', "%$title%");
        }

        if ($method_code) {
            if (is_array($method_code)) {
                $query->whereIn('discount.method_code', $method_code);
            } else {
                $query->where('discount.method_code', $method_code);
            }
        }

        if ($status_code) {
            if (is_array($status_code)) {
                $query->whereIn('discount.status_code', $status_code);
            } else {
                $query->where('discount.status_code', $status_code);
            }
        }

        if (!is_null($is_global)) {
            $query->where('discount.is_global', $is_global);
        }

        if ($mail_sended != 'all') {
            if ($mail_sended == 0) {
                $query->whereNull('u_coupon.mail_sended_at');
            } else if ($mail_sended == 1) {
                $query->whereNotNull('u_coupon.mail_sended_at');
            }
        }

        if ($start_date) {
            $query->where('u_coupon.active_edate', '>=', $start_date . ' 00:00:00');
        }

        if ($end_date) {
            $query->where('u_coupon.active_edate', '<=', $end_date . ' 23:59:59');
        }

        if ($customer_coupon_id) {
            if (is_array($customer_coupon_id)) {
                $query->whereIn('u_coupon.id', $customer_coupon_id);
            } else {
                $query->where('u_coupon.id', $customer_coupon_id);
            }
        }

        return $query;
    }
}
