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
            'note' => '由' . $order_sn . '訂單取得',
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

    public static function decrease($customer_id, DividendFlag $flag, $point, $note = null)
    {

        $id = self::create([
            'customer_id' => $customer_id,
            'dividend' => $point,
            'flag' => $flag,
            'flag_title' => $flag->description,
            'weight' => 0,
            'type' => 'used',
            'note' => $note,
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
            return [
                'success' => '0',
                'event' => 'dividend',
                'error_msg' => '無鴻利餘額',
                'error_status' => 'dividend'
            ];
        }

        $dividend = $dividend->dividend;

        if ($order['use_dividend'] > $dividend) {
            DB::rollBack();
            return [
                'success' => '0',
                'event' => 'dividend',
                'error_msg' => '鴻利餘額不足',
                'error_status' => 'dividend'
            ];
        }

        /*
        $d = self::where('customer_id', $customer_id)
        ->whereIn('flag', [DividendFlag::Active(), DividendFlag::Back()])
        ->orderBy('weight', 'ASC')
        ->get()->toArray();
         */

        $d = self::select(['*'])
            ->selectRaw('CASE category
                    WHEN "cyberbiz" THEN 2
                    WHEN "order" THEN 3
                    WHEN "m_b2e" THEN 1
                    WHEN "m_b2c" THEN 0 END as w')
            ->where('customer_id', $customer_id)
            ->whereIn('flag', [DividendFlag::Active(), DividendFlag::Back()])
            ->orderBy('w', 'ASC')
            ->orderByRaw('CASE WHEN active_edate is null then 1 else 0 end ASC')
            ->orderBy('active_edate', 'ASC')
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
            'note' => '由' . $order['order_sn'] . "訂單使用",
        ])->id;
        DB::commit();
        return ['success' => '1', 'id' => $id];
    }

    public static function checkExpired($customer_id, $onlyInvalid = null)
    {
        $concatString = concatStr([
            'id' => 'id',
        ]);

        $condition = DividendFlag::Active();
        if ($onlyInvalid) {
            $condition = DividendFlag::Invalid();
        }

        $exp = self::where('active_edate', '<', DB::raw("NOW()"))
            ->where('flag', $condition)
            ->selectRaw('SUM(dividend-used_dividend) as dividend')
            ->selectRaw($concatString . ' as dividends')
            ->where('customer_id', $customer_id)
            ->groupBy('customer_id')->get()->first();

        if (!$exp) {
            return;
        }
        $expPoint = $exp->dividend;

        $exp->dividends = json_decode($exp->dividends);

        if ($expPoint !== 0) {
            self::decrease($customer_id, DividendFlag::Expired(), $expPoint * -1, date('Ymd') . '失效');
        }

        array_map(function ($n) {
            self::where('id', $n->id)->update([
                'flag' => DividendFlag::Invalid(),
                'flag_title' => DividendFlag::Invalid()->description
            ]);
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

    public static function totalList($keyword = null)
    {
        //get訂單返還的訂單編號, 例：由O202312060004訂單返還
        $canceled_orders = self::where('note', 'LIKE', '由O%')
            ->where('note', 'LIKE', '%返還')
            ->get();
        $canceled_orders_ids = $canceled_orders->groupBy('id')->toArray();
        $canceled_orders_notes = $canceled_orders->groupBy('note')->toArray();

        $canceledOrders = [];
        foreach (array_keys($canceled_orders_notes) as $canceledOrder) {
            //substr 例：由O202312060004訂單返還
            $canceledOrders[] = mb_substr($canceledOrder, 1, -4, 'utf-8');
        }

        $getDividendSub = self::select(['customer_id', 'category', 'type'])
            ->whereNotIn('id', array_keys($canceled_orders_ids))
            ->whereNotIn('category_sn', $canceledOrders)
            ->selectRaw('SUM(dividend) as dividend')
            ->where('flag', "<>", DividendFlag::NonActive())
            //  ->where('type', 'get')
            ->groupBy('customer_id')
            ->groupBy('category')
            ->groupBy('type');

        $step2 = DB::query()->fromSub($getDividendSub, 'base')
            ->select(['base.customer_id'])
            ->selectRaw(concatStr(['category' => 'base.category', 'type' => 'base.type', 'dividend' => 'base.dividend']) . " as data")
            ->selectRaw('SUM(base.dividend) as total')
            ->groupBy('base.customer_id');

        $re = DB::table('usr_customers as customer')
            ->select([
                'customer.id', 'customer.name',
                'customer.email',
                'customer.sn',
            ])
            ->selectRaw('IF(data.data IS NULL,"[]",data.data) as data')
            ->selectRaw('IF(data.total IS NULL,0,data.total) as total')
            ->leftJoinSub($step2, 'data', 'customer.id', '=', 'data.customer_id');

        if ($keyword) {
            $re->where(function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%')
                    ->orWhere('sn', 'like', '%' . $keyword . '%');
            });
        }
        return $re;
    }

    public static function format(&$data)
    {

        $template = [];
        foreach (DividendCategory::asArray() as $value) {
            $template[$value . "_get"] = 0;
            if ($value == 'order') {
                $template[$value . "_used"] = 0;
            }
        }

        foreach ($data as $value) {
            $d = json_decode($value->data);
            $value->formated = $template;
            if (!is_array($d)) {
                dd($value);
            }
            foreach ($d as $v) {
                $value->formated[$v->category . "_" . $v->type] = $v->dividend;
            }
        }
    }

    /**
     * @param $category
     * @return mixed
     * 取得點數 點數明細：類別/姓名/點數/取得來源/取得日期
     */
    public static function dividencByCategory($category)
    {
        $getDividendSub = self::select([
            'customer_id',
            'category',
            'dividend',
            'note',
            'id as dividend_id',
            'created_at',
        ])
            ->where('note', 'NOT LIKE', "%返還");

        if ($category !== 'all') {
            $getDividendSub->where('category', $category);
        }

        $getDividendSub->where('type', 'get')
            ->where('flag', "<>", DividendFlag::NonActive())
            ->groupBy('dividend_id')
            ->groupBy('category')
            ->groupBy('type');

        $step2 = DB::query()->fromSub($getDividendSub, 'base')
            ->select([
                'base.customer_id',
                'note',
            ])
            ->selectRaw(concatStr([
                'dividend_id' => 'base.dividend_id',
                'category' => 'base.category',
                'note' => 'base.note',
                'dividend' => 'base.dividend',
                'created_at' => 'base.created_at',
            ]) . " as data")
            ->groupBy('base.customer_id');

        $result = DB::table('usr_customers as customer')
            ->select([
                'customer.id as customer_id',
                'customer.name',
                'customer.sn',
            ])
            ->selectRaw('IF(data.data IS NULL,"[]",data.data) as data')
            ->where('data.data', 'NOT LIKE', "[]")
            ->leftJoinSub($step2, 'data', 'customer.id', '=', 'data.customer_id')
            ->paginate(100);

        return $result;
    }

    /*
     * 使用點數
     */
    public static function usedDividendByCategory($category)
    {


        $dividendIds = DB::table('ord_dividend')
            ->select([
                'usr_cusotmer_dividend.id as dividend_id',
            ])
            ->leftJoin('usr_cusotmer_dividend', 'ord_dividend.customer_dividend_id', 'usr_cusotmer_dividend.id');

        if ($category !== 'all') {
            $dividendIds->where('category', $category);
        }

        $dividendIds = $dividendIds->orderBy('dividend_id')
            ->groupBy('dividend_id')
            ->get()
            ->toArray();
        $ids = [];
        foreach ($dividendIds as $item) {
            $ids[] = $item->dividend_id;
        }

        $orderSns = DB::table('ord_dividend')
            ->select([
                'id as ord_dividend_id',
                'order_sn',
            ])
            ->whereIn('customer_dividend_id', $ids)
            ->get()
            ->toArray();
        $idArray = [];
        $snArray = [];
        foreach ($orderSns as $orderSn) {
            $idArray[] = $orderSn->ord_dividend_id;
            $snArray[] = $orderSn->order_sn;
        }
        $getDividendSub = DB::table('usr_cusotmer_dividend')
            ->select([
                'usr_cusotmer_dividend.id',
                'usr_cusotmer_dividend.note',
                'usr_cusotmer_dividend.customer_id',
                'usr_cusotmer_dividend.category',
                'usr_cusotmer_dividend.updated_at',
                'ord_dividend.id as divid_id',
                'ord_dividend.dividend',
            ])
            ->where('type', 'used')
            ->whereIn('category_sn', $snArray)
            ->leftJoin('ord_dividend', 'ord_dividend.order_sn', 'usr_cusotmer_dividend.category_sn')
            ->whereIn('ord_dividend.id', $idArray)
            ->orderBy('customer_id');

        $step2 = DB::query()->fromSub($getDividendSub, 'base')
            ->select([
                'base.customer_id',
                'base.category',
            ])
            ->selectRaw(concatStr([
                'dividend' => 'base.dividend',
                'dividend_id' => 'base.id',
                'note' => 'base.note',
                'updated_at' => 'base.updated_at',
            ]) . " as data")
            ->groupBy('base.customer_id');

        $query = DB::table('usr_customers as customers')
            ->select([
                'customers.id as customer_id',
                'customers.sn',
                'customers.name',
                'category',
            ])
            ->selectRaw('IF(data.data IS NULL,"[]",data.data) as data')
            ->where('data.data', '<>', "[]")
            ->leftJoinSub($step2, 'data', 'customers.id', 'data.customer_id')
            ->orderBy('customer_id')
            ->paginate(100);

        return $query;
    }

    /**
     * @param $category
     * @param string $property 查詢剩餘的點數
     * @return \Illuminate\Database\Query\Builder
     */
    public static function queryDividendByCategory($category, $property)
    {
        $getDividendSub = self::select(['customer_id'])
            ->selectRaw('SUM(dividend-used_dividend) as dividend')
            ->where('flag', DividendFlag::Active())
            ->groupBy('customer_id');

        // dd($getDividendSub->get()->toArray());
        if ($category !== 'all') {
            $getDividendSub->where('category', $category);
        }

        $re = DB::table('usr_customers as customer')
            ->leftJoinSub($getDividendSub, 'data', 'customer.id', '=', 'data.customer_id')
            ->select([
                'customer.id',
                'customer.name',
                'customer.sn',
                'data.dividend'
            ])
            ->whereNotNull('data.dividend')
            ->where('data.dividend', '>', 0);

        return $re;
    }

    public static function getByCategory()
    {
        $canceled_orders = self::where('note', 'LIKE', '由O%')
            ->where('note', 'LIKE', '%返還')
            ->select([
                'id',
                'category',
                'note',
                'dividend',
                'used_dividend',
            ])
            ->get();

        $canceled_orders_ids = $canceled_orders->groupBy('id')->toArray();
        $canceled_orders_notes = $canceled_orders->groupBy('note')->toArray();
        $canceled_orders_categories = $canceled_orders->groupBy('category')->toArray();

        $canceledOrders = [];
        foreach (array_keys($canceled_orders_notes) as $canceledOrder) {
            //substr 例：由O202312060004訂單返還
            $canceledOrders[] = mb_substr($canceledOrder, 1, -4, 'utf-8');
        }

        $dividendCategory = DividendCategory::getValueWithDesc();

        $categoryCase = 'CASE ';
        $categoryNames = [];
        foreach ($dividendCategory as $key => $value) {
            $categoryCase .= ' WHEN category = "' . $key . '" THEN "' . $value . '"';
            $categoryNames[$key] = $value;
        }
        $categoryCase .= ' END as category_ch';


        $sended = self::select(['category'])
            ->selectRaw('SUM(dividend) as dividend')
            ->selectRaw($categoryCase)
            ->where('type', 'get')
            ->whereIn('flag', [DividendFlag::Active(), DividendFlag::Consume()])
            ->whereNotIn('id', array_keys($canceled_orders_ids))
            ->whereNotIn('category_sn', $canceledOrders)
            ->groupBy('category')->get()->toArray();

        $remain = self::select(['category'])
            ->selectRaw('SUM(dividend-used_dividend) as dividend')
            ->where('type', 'get')
            ->where('flag', DividendFlag::Active())

            ->groupBy('category')->get()->toArray();

        //  dd($sended,$remain);

        foreach ($sended as $key => $value) {
            // $idx = array_search(,$value['category']);

            $idx = array_search($value['category'], array_map(function ($n) {
                return $n['category'];
            }, $remain));
            $sended[$key]['remain_dividend'] = 0;
            if ($idx > -1) {
                $sended[$key]['remain_dividend'] = $remain[$idx]['dividend'];
            }

            $sended[$key]['used_dividend'] =  $sended[$key]['dividend'] - $sended[$key]['remain_dividend'];
            $sended[$key]['type'] = "get";
            $sended[$key]['usage_rate'] = number_format($sended[$key]['used_dividend'] / $sended[$key]['dividend'] * 100, 2);
        }

      

        // $dividendCategory = DividendCategory::getValueWithDesc();

        // $categoryCase = 'CASE ';
        // $categoryNames = [];
        // foreach ($dividendCategory as $key => $value) {
        //     $categoryCase .= ' WHEN category = "' . $key . '" THEN "' . $value . '"';
        //     $categoryNames[$key] = $value;
        // }
        // $categoryCase .= ' END as category_ch';

        // //get訂單返還的訂單編號, 例：由O202312060004訂單返還
        // $canceled_orders = self::where('note', 'LIKE', '由O%')
        //     ->where('note', 'LIKE', '%返還')
        //     ->select([
        //         'id',
        //         'category',
        //         'note',
        //         'dividend',
        //         'used_dividend',
        //     ])
        //     ->get();
        // $canceled_orders_ids = $canceled_orders->groupBy('id')->toArray();
        // $canceled_orders_notes = $canceled_orders->groupBy('note')->toArray();
        // $canceled_orders_categories = $canceled_orders->groupBy('category')->toArray();

        // //訂單返回要退回的購物金
        // $canceledDividendArray = [];
        // $canceledUsedDividendArray = [];
        // foreach ($canceled_orders_categories as $category => $data) {
        //     $canceledDividendArray[$categoryNames[$category]] = collect($data)->sum('dividend');
        //     $canceledUsedDividendArray[$categoryNames[$category]] = collect($data)->sum('used_dividend');
        // }

        // $canceledOrders = [];
        // foreach (array_keys($canceled_orders_notes) as $canceledOrder) {
        //     //substr 例：由O202312060004訂單返還
        //     $canceledOrders[] = mb_substr($canceledOrder, 1, -4, 'utf-8');
        // }

        // $re = self::select(['type', 'category'])
        //     ->whereNotIn('id', array_keys($canceled_orders_ids))
        //     ->whereNotIn('category_sn', $canceledOrders)
        //     ->selectRaw('SUM(dividend) as dividend')
        //     ->selectRaw('SUM(used_dividend) as used_dividend')
        //     ->selectRaw($categoryCase)
        //     ->where('flag', "<>", DividendFlag::NonActive())
        //     ->groupBy('type')
        //     ->groupBy('category')
        //     ->orderBy('type')
        //     ->get()
        //     ->toArray();
        // foreach ($re as $key => $items) {
        //     if ($items['type'] === 'get') {
        //         if (array_key_exists($items['category_ch'], $canceledUsedDividendArray)) {
        //             $canceledUsedDividend = $canceledUsedDividendArray[$items['category_ch']];
        //         } else {
        //             $canceledUsedDividend = 0;
        //         }
        //         if (array_key_exists($items['category_ch'], $canceledDividendArray)) {
        //             $canceledDividend = $canceledDividendArray[$items['category_ch']];
        //         } else {
        //             $canceledDividend = 0;
        //         }
        //         $used_dividend = intval($items['used_dividend']) + $canceledUsedDividend - $canceledDividend;
        //         $re[$key]['used_dividend'] = strval($used_dividend);
        //         $re[$key]['remain_dividend'] = $re[$key]['dividend'] - $re[$key]['used_dividend'];
        //         $re[$key]['usage_rate'] = $used_dividend * 100 / $re[$key]['dividend'];
        //     } else {
        //         unset($re[$key]);
        //     }
        // }
        // dd($re);

        return $sended;
    }
}
