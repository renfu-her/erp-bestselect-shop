<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CouponEvent extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'dis_coupon_event';
    protected $guarded = [];

    public static function dataList($title)
    {   
        $sub = CouponEventLog::select(['event_id'])
        ->selectRaw('SUM(qty) as total_qty')
        ->groupBy('event_id');

       

        $re = DB::table('dis_coupon_event as ce')
            ->leftJoin('dis_discounts as discount', 'discount.id', '=', 'ce.discount_id')
            ->leftJoinSub($sub,'sub','ce.id','=','sub.event_id')
            ->select(['ce.*', 'discount.title as discount_title','sub.total_qty'])
            ->whereNull('ce.deleted_at');

        if ($title) {
            $re->where('ce.title', 'like', "%$title%");
        }

        return $re;
    }

    public static function getCoupon($customer_id, $coupon_sn)
    {
        DB::beginTransaction();

        $event = self::where('sn', $coupon_sn)
            ->where('active', '1')
            ->where(function ($query) {
                $d = date("Y-m-d H:i:00");
                $query->where('start_date', '<=', $d)
                    ->where('end_date', '>=', $d);
            })->get()->first();

        if (!$event) {
            DB::rollBack();
            return ['success' => 0, 'msg' => '查無此活動'];
        }

        if ($event->qty_limit != 0) {
            // checke used
            $usedQty = CouponEventLog::where('event_id', $event->id)
                ->selectRaw('SUM(qty) as total_qty')
                ->groupBy('event_id')->lockForUpdate()->get()->first();

            if ($usedQty) {
                if ($usedQty->total_qty + $event->qty_per_once > $event->qty_limit) {
                    DB::rollBack();
                    return ['success' => 0, 'msg' => '優惠券已經提領一空'];
                }
            }
        }

        if ($event->reuse == 0) {
            $customer_used = CouponEventLog::where('event_id', $event->id)
                ->where('customer_id', $customer_id)->get()->first();

            if ($customer_used) {
                DB::rollBack();
                return ['success' => 0, 'msg' => '已領取過'];
            }
        }

        CouponEventLog::create([
            'event_id' => $event->id,
            'discount_id' => $event->discount_id,
            'customer_id' => $customer_id,
            'qty' => $event->qty_per_once,
        ]);

        $sdate = date("Y-m-d H:i:00");
        $edate = date("Y-m-d H:i:00", strtotime($sdate . " +10 years"));

        for ($i = 0; $i < $event->qty_per_once; $i++) {
            CustomerCoupon::create([
                'from_order_id' => 0,
                'limit_day' => 0,
                'customer_id' => $customer_id,
                'discount_id' => $event->discount_id,
                'active_sdate' => $sdate,
                'active_edate' => $edate,
            ]);
        }

        DB::commit();

        return ['success' => 1];

    }

}
