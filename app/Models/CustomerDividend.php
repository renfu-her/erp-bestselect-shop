<?php

namespace App\Models;

use App\Enums\Discount\DisCategory;
use App\Enums\Discount\DividendCategory;
use App\Enums\Discount\DividendFlag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CustomerDividend extends Model
{
    use HasFactory;
    protected $table = 'usr_cusotmer_dividend';
    protected $guarded = [];

    public static function getList($customer_id, $type = null)
    {
        $re = DB::table('usr_cusotmer_dividend as dividend')
            ->select('dividend.*')
            ->selectRaw('IF(active_sdate IS NULL,"",active_sdate) as active_sdate')
            ->selectRaw('IF(active_edate IS NULL,"",active_edate) as active_edate')
            ->orderBy('created_at', 'DESC');

        if ($type) {
            $re->where('type', $type);
        }

        if ($customer_id) {
            $re->where('dividend.customer_id', $customer_id);
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
            'note' => '由'.$order_sn.'訂單取得'
        ])->id;

        return $id;
    }

    public static function activeDividend(DividendCategory $category, $category_sn, $date = null)
    {
        $dividend = self::where('category', $category)
            ->where('category_sn', $category_sn)
            ->where('flag', DividendFlag::NonActive())->get()->first();

        $order = Order::where('sn', $category_sn)->get()->first();

        if (!$date) {
            $date = now();
        }

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
            $edate = date('Y-m-d 23:59:59', strtotime($date . " + $order->dividend_lifecycle days"));

        } else {
            $sdate = now();
            $edate = date('Y-m-d 23:59:59', strtotime($date . ' + 50 years'));
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

    // 訂單中使用鴻利
    public static function orderDiscount($customer_id, &$order)
    {
        if (!$order['use_dividend']) {
            return ['success' => '1'];
        }

        DB::beginTransaction();
        $dividend = self::getDividend($customer_id)->get()->first();

        if (!$dividend || !$dividend->dividend) {
            DB::rollBack();
            return ['success' => '0',
                'event' => 'dividend',
                'error_msg' => '無鴻利餘額',
                'error_status' => 'dividend'];
        }

        $dividend = $dividend->dividend;

        if ($order['use_dividend'] > $dividend) {
            DB::rollBack();
            return ['success' => '0',
                'event' => 'dividend',
                'error_msg' => '鴻利餘額不足',
                'error_status' => 'dividend'];
        }

        $d = self::where('customer_id', $customer_id)
            ->whereIn('flag', [DividendFlag::Active(), DividendFlag::Back()])
            ->orderBy('weight', 'ASC')
            ->get()->toArray();

        $remain_dividend = $order['use_dividend'];
        $arrDividend = [];

        // 替換紅利
        //購物金
        // dd($order['discounts']);
        $order['discounts'] = array_filter($order['discounts'], function ($n) {
            return $n->category_code != 'dividend';
        });

        foreach ($d as $key => $value) {
            if ($remain_dividend > 0) {
                // 每批紅利可用點數
                $can_use_point = $value['dividend'] - $value['used_dividend'];

                if ($remain_dividend <= $can_use_point) {
                    $can_use_point = $remain_dividend;
                }
                // echo $key.'='.$can_use_point."<br>";
                // dd($remain_dividend , $can_use_point);

                $update_data = [];
                $update_data['used_dividend'] = DB::raw("used_dividend + $can_use_point");

                if ($value['dividend'] == $value['used_dividend'] + $can_use_point) {
                    $update_data['flag'] = DividendFlag::Consume();
                    $update_data['flag_title'] = DividendFlag::Consume()->description;
                }

                self::where('id', $value['id'])->update($update_data);
                $_dividend = [
                    'order_sn' => $order['order_sn'],
                    'customer_dividend_id' => $value['id'],
                    'dividend' => $can_use_point,
                ];
                //  print_r($_dividend);
                DB::table('ord_dividend')->insert($_dividend);
                $_dividend['category'] = $value['category'];
                // 將紅利類別轉換成會計類別
                //  dd(DisCategory::dividend());
                switch ($value['category']) {
                    case 'order':
                    case 'cyberbiz':
                        $category_code = DisCategory::dividend()->value;
                        break;
                    default:
                        $category_code = $value['category'];
                }

                if (!isset($arrDividend[$category_code])) {
                    $arrDividend[$category_code] = [];
                }
                $arrDividend[$category_code][] = $_dividend;
                $remain_dividend -= $can_use_point;
                //   echo $remain_dividend;
            }

        }

        foreach ($arrDividend as $key => $value) {

            $dis = (object) [
                "title" => "購物金折抵",
                "category_title" => DisCategory::fromValue($key)->description,
                "category_code" => $key,
                "method_code" => "cash",
                "method_title" => "現金",
                "discount_value" => 0,
                "currentDiscount" => 0,
                "is_grand_total" => 0,
                "min_consume" => 0,
                "coupon_id" => null,
                "coupon_title" => null,
                "discount_grade_id" => null,
            ];

            foreach ($value as $val2) {
                $dis->discount_value += $val2['dividend'];
            }
            $dis->currentDiscount = $dis->discount_value;

            $order['discounts'][] = $dis;
        }

        $id = self::create([
            'customer_id' => $customer_id,
            'category' => DividendCategory::Order(),
            'category_sn' => $order['order_sn'],
            'dividend' => $order['use_dividend'] * -1,
            'flag' => DividendFlag::Discount(),
            'flag_title' => DividendFlag::Discount()->description,
            'weight' => 0,
            'type' => 'used',
            'note' => '由'.$order['order_sn']."訂單使用"
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

    public static function checkDividendFromErp($customer_sn, $password)
    {

        $url = 'https://www.besttour.com.tw/api/b2X_points.asp';
        $time = time();
        $response = Http::withoutVerifying()->get($url, [
            'id' => 1,
            'no' => $customer_sn,
            'requestid' => $time,
            'password' => $password,
        ]);

        if ($response->successful()) {
            $response = ($response->json())[0];
            if ($response['status'] != '0') {
                return $response;
            }

            $response['requestid'] = $time;

            return $response;
        }

    }

    public static function getDividendFromErp($customer_id, $edword, $point, $type, $requestid)
    {

        // https://www.besttour.com.tw/api/b2X_points.asp?id=2%20&edword=HSI-HUNG33A1653DDE95A4266A6D0F4400B071E0E81ECFE817FB53CD0E1283EC2CEC2BE0&point=10
        // https://www.besttour.com.tw/api/b2X_points.asp?id=2&edword=HSI-HUNG216A5FFEE7E03345ED7664D52E893A5F4519C5EC096D9E80F8211F300F11DF36&points=10
        $url = 'https://www.besttour.com.tw/api/b2X_points.asp';
        $response = Http::withoutVerifying()->get($url, [
            'id' => 2,
            'edword' => $edword,
            'point' => $point,
        ]);

        // dd($response);

        if ($response->successful()) {
            $response = ($response->json())[0];

            if ($response['status'] != '0') {
                return $response;
            }

            if ($point > 0) {
                $id = self::create([
                    'customer_id' => $customer_id,
                    'category' => DividendCategory::fromKey($type),
                    'category_sn' => $requestid,
                    'dividend' => $point,
                    'deadline' => 0,
                    'flag' => DividendFlag::Active(),
                    'flag_title' => DividendFlag::Active()->description,
                    'weight' => 0,
                    'type' => 'get',
                    'note' => "由" . DividendCategory::fromKey($type)->description . "取得",
                ])->id;

            }
            return ['status' => '0'];
        }
    }

}
