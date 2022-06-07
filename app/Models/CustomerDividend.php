<?php

namespace App\Models;

use App\Enums\Discount\DividendCategory;
use App\Enums\Discount\DividendFlag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CustomerDividend extends Model
{
    use HasFactory;
    protected $table = 'usr_cusotmer_dividend';
    protected $guarded = [];

    public static function getList($customer_id, $type = null)
    {
        $re = DB::table('usr_cusotmer_dividend as dividend')
            ->orderBy('created_at', 'DESC');

        if ($type) {
            $re->where('type', $type);
        }

        return $re;
    }
    // 從訂單取得點數
    public static function fromOrder($customer_id, $order_sn, $point, $deadline = 1)
    {
        /*
        if ($deadline == 1) {
        $weight = 0;
        } else {
        $weight = 999;
        }
         */
        $id = self::create([
            'customer_id' => $customer_id,
            'category' => DividendCategory::Order(),
            'category_sn' => $order_sn,
            'dividend' => $point,
            'deadline' => $deadline,
            'flag' => DividendFlag::NonActive(),
            'flag_title' => DividendFlag::NonActive()->description,
            'weight' => 0,
            'type' => 'get',
        ])->id;

        return $id;
    }

    public static function activeDividend(DividendCategory $category, $category_sn, $expired = 0)
    {
        $dividend = self::where('category', $category)
            ->where('category_sn', $category_sn)
            ->where('flag', DividendFlag::NonActive())->get()->first();

        $order = Order::where('sn', $category_sn)->get()->first();

        if (!$dividend || !$order) {
            return;
        }
        if ($order->dividend_lifecycle == 0) {
            $deadline = 0;
        } else {
            $deadline = 1;
        }

        if ($deadline == 1) {
            $sdate = now();
            $edate = date('Y-m-d 23:59:59', strtotime(now() . " + $order->dividend_lifecycle days"));

        } else {
            $sdate = now();
            $edate = date('Y-m-d 23:59:59', strtotime(now() . ' + 50 years'));
        }

        if ($expired == 1) {
            $sdate = date('Y-m-d 23:59:59', strtotime(now() . ' - 30 days'));
            $edate = date('Y-m-d 23:59:59', strtotime(now() . ' - 10 days'));
        }

        //   print_r($sdate);

        self::where('id', $dividend->id)
            ->where('flag', DividendFlag::NonActive())
            ->update(
                [
                    'active_sdate' => $sdate,
                    'active_edate' => $edate,
                    'flag' => DividendFlag::Active(),
                    'flag_title' => DividendFlag::Active()->description,
                ]
            );

        if (DividendCategory::Order() == $category && $category_sn) {
            Order::where('sn', $category_sn)->update([
                'allotted_dividend' => 1,
            ]);
        }
    }

    public static function getDividend($customer_id)
    {

        return self::where('flag', "<>", DividendFlag::NonActive())
            ->selectRaw("SUM(dividend) as dividend")
            ->groupBy('customer_id')
            ->where('customer_id', $customer_id);

    }

// decrease

    public static function decrease($customer_id, DividendFlag $flag, $point)
    {

        $id = self::create([
            'customer_id' => $customer_id,
            'dividend' => $point,
            'flag' => $flag,
            'flag_title' => $flag->description,
            'weight' => 0,
            'type' => 'used',
        ])->id;

        return $id;
    }

    // 訂單中使用紅利
    public static function orderDiscount($customer_id, $order_sn, $discount_point)
    {
        if(!$discount_point){
            return ['success' => '1'];
        }
        
        DB::beginTransaction();
        $dividend = self::getDividend($customer_id)->get()->first();

        if (!$dividend || !$dividend->dividend) {
            DB::rollBack();
            return ['success' => '0',
                'event' => 'dividend',
                'error_msg' => '無紅利餘額',
                'error_stauts' => 'dividend'];
        }

        $dividend = $dividend->dividend;

        if ($discount_point > $dividend) {
            DB::rollBack();
            return ['success' => '0',
                'event' => 'dividend',
                'error_msg' => '紅利餘額不足',
                'error_stauts' => 'dividend'];
        }

        $d = self::where('customer_id', $customer_id)
            ->where('flag', DividendFlag::Active())
            ->orderBy('weight', 'ASC')
            ->get()->toArray();

        $remain_dividend = $discount_point;
        foreach ($d as $value) {
            if ($remain_dividend > 0) {

                $can_use_point = $value['dividend'] - $value['used_dividend'];

                if ($remain_dividend < $value['dividend']) {
                    $can_use_point = $remain_dividend;
                }

                $update_data = [];
                $update_data['used_dividend'] = DB::raw("used_dividend + $can_use_point");

                if ($value['dividend'] == $value['used_dividend'] + $can_use_point) {

                    $update_data['flag'] = DividendFlag::Consume();
                    $update_data['flag_title'] = DividendFlag::Consume()->description;
                }

                self::where('id', $value['id'])->update($update_data);
                $remain_dividend -= $can_use_point;

            }

        }

        $id = self::create([
            'customer_id' => $customer_id,
            'category' => DividendCategory::Order(),
            'category_sn' => $order_sn,
            'dividend' => $discount_point * -1,
            'flag' => DividendFlag::Discount(),
            'flag_title' => DividendFlag::Discount()->description,
            'weight' => 0,
            'type' => 'used',
        ])->id;
        DB::commit();
        return ['success' => '1', 'id' => $id];
    }

    public static function checkExpired($customer_id)
    {
        $concatString = concatStr([
            'id' => 'id',
        ]);

        $exp = self::where('active_edate', '<', DB::raw("NOW()"))
            ->where('flag', DividendFlag::Active())
            ->selectRaw('SUM(dividend) as dividend')
            ->selectRaw($concatString . ' as dividends')
            ->where('customer_id', $customer_id)
            ->groupBy('customer_id')->get()->first();

        if (!$exp) {
            return;
        }
        $expPoint = $exp->dividend;

        $exp->dividends = json_decode($exp->dividends);

        $canDescPoint = self::whereNotIn('flag', [DividendFlag::NonActive(), DividendFlag::Invalid()])
            ->where('deadline', '=', 1)
            ->selectRaw('SUM(dividend) as dividend')
            ->where('customer_id', $customer_id)
            ->groupBy('customer_id')->get()->first()->dividend;

        //   dd($canDescPoint, $expPoint);

        if ($canDescPoint > $expPoint) {
            $expPoint = $expPoint * -1;
        } else {
            $expPoint = $canDescPoint * -1;
        }

        if ($expPoint !== 0) {
            self::decrease($customer_id, DividendFlag::Expired(), $expPoint);
        }

        array_map(function ($n) {
            self::where('id', $n->id)->update(['flag' => DividendFlag::Invalid(),
                'flag_title' => DividendFlag::Invalid()->description]);
        }, $exp->dividends);

    }
}
