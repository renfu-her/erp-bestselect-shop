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

    public static function forOrder($customer_id, $category_sn, $point, $deadline = 1)
    {
        if ($deadline == 1) {
            $weight = 0;
        } else {
            $weight = 999;
        }

        $id = self::create([
            'customer_id' => $customer_id,
            'category' => DividendCategory::Order(),
            'category_sn' => $category_sn,
            'points' => $point,
            'deadline' => $deadline,
            'flag' => DividendFlag::NonActive(),
            'flag_title' => DividendFlag::NonActive()->description,
            'weight' => $weight,
        ])->id;

        return $id;
    }

    public static function activeDividend($dividend_id, $expired = 0)
    {
        $dividend = self::where('id', $dividend_id)
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

        self::where('id', $dividend_id)
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

    public static function getPoints($customer_id)
    {

        return self::where('flag', "<>", DividendFlag::NonActive())
            ->selectRaw("SUM(points) as points")
            ->groupBy('customer_id')
            ->where('customer_id', $customer_id);

    }

// decrease

    public static function decrease($customer_id, DividendFlag $flag, $point)
    {

        $id = self::create([
            'customer_id' => $customer_id,
            'points' => $point,
            'flag' => $flag,
            'flag_title' => $flag->description,
            'weight' => 0,
        ])->id;

        return $id;
    }

    public static function orderDiscount($customer_id, $order_id, $discount_point)
    {
        $points = self::getPoints($customer_id)->get()->first()->points;

        if ($discount_point > $points) {
            return;
        }

        $id = self::create([
            'customer_id' => $customer_id,
            'category' => DividendCategory::Order(),
            'category_sn' => $order_id,
            'points' => $discount_point * -1,
            'flag' => DividendFlag::Discount(),
            'flag_title' => DividendFlag::Discount()->description,
            'weight' => 0,
        ])->id;

        return $id;
    }


    public static function checkExpired($customer_id)
    {
        $concatString = concatStr([
            'id' => 'id',
        ]);

        $exp = self::where('active_edate', '<', DB::raw("NOW()"))
            ->where('flag', DividendFlag::Active())
            ->selectRaw('SUM(points) as points')
            ->selectRaw($concatString . ' as dividends')
            ->where('customer_id', $customer_id)
            ->groupBy('customer_id')->get()->first();

        if(!$exp){
            return ;
        }
        $expPoint = $exp->points;

        $exp->dividends = json_decode($exp->dividends);

        $canDescPoint = self::whereNotIn('flag', [DividendFlag::NonActive(), DividendFlag::Invalid()])
            ->where('deadline', '=', 1)
            ->selectRaw('SUM(points) as points')
            ->where('customer_id', $customer_id)
            ->groupBy('customer_id')->get()->first()->points;

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
