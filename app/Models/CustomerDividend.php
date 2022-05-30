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

    public static function fromOrder($customer_id, $order_id, $point, $deadline = 1)
    {
        if ($deadline == 1) {
            $weight = 0;
        } else {
            $weight = 999;
        }

        $id = self::create([
            'customer_id' => $customer_id,
            'category' => DividendCategory::Order(),
            'category_sn' => $order_id,
            'dividend' => $point,
            'deadline' => $deadline,
            'flag' => DividendFlag::NonActive(),
            'flag_title' => DividendFlag::NonActive()->description,
            'weight' => $weight,
            'type' => 'get',
        ])->id;

        return $id;
    }

    public static function activeDividend(DividendCategory $category, $category_sn, $expired = 0)
    {
        $dividend = self::where('category', $category)
            ->where('category_sn', $category_sn)
            ->where('flag', DividendFlag::NonActive())->get()->first();

        if (!$dividend) {
            return;
        }

        $deadline = $dividend->deadline;

        if ($deadline == 1) {
            $sdate = now();
            $edate = date('Y-m-d 23:59:59', strtotime(now() . ' + 180 days'));

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

    public static function orderDiscount($customer_id, $order_id, $discount_point)
    {
        DB::beginTransaction();
        $dividend = self::getDividend($customer_id)->get()->first()->dividend;

        if ($discount_point > $dividend) {
            DB::rollBack();
            return;
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
            'category_sn' => $order_id,
            'dividend' => $discount_point * -1,
            'flag' => DividendFlag::Discount(),
            'flag_title' => DividendFlag::Discount()->description,
            'weight' => 0,
            'type' => 'used',
        ])->id;
        DB::commit();
        return $id;
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
