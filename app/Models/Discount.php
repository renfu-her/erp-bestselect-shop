<?php

namespace App\Models;

use App\Enums\Discount\DisCategory;
use App\Enums\Discount\DisMethod;
use App\Enums\Discount\DisStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Discount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dis_discounts';
    protected $guarded = [];

    public static function _discountStatus()
    {
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

        return self::select('*')
            ->selectRaw($selectStatus)
            ->selectRaw($selectStatusCode);

    }

    private static function _dataWithCoupon()
    {

        $sub = self::_discountStatus();

        $coupons = DB::table(DB::raw("({$sub->toSql()}) as sub"))
            ->leftJoin('dis_discounts as dis', 'sub.discount_value', '=', 'dis.id')
            ->select('sub.*', 'dis.id as coupon_id', 'dis.title as coupon_title')
            ->where('sub.method_code', '=', DisMethod::coupon()->value);

        $nonCoupon = DB::table(DB::raw("({$sub->toSql()}) as sub"))
            ->select('sub.*', DB::raw("@coupon_id:=null as coupon_id"), DB::raw("@coupon_title:=null as coupon_title"))
            ->where('sub.method_code', '<>', DisMethod::coupon()->value)
            ->union($coupons);

        $nonCoupon->bindings['where'][] = $nonCoupon->bindings['union'][0];
        $nonCoupon->bindings['union'] = [];

        return $nonCoupon;
    }

    public static function getDiscountStatus($id)
    {

        $sub = self::_dataWithCoupon();
        $re = DB::table(DB::raw("({$sub->toSql()}) as sub"))
            ->select(['sub.id', 'status', 'status_code'])
            ->mergeBindings($sub)
            ->where('id', $id);

        // dd(IttmsUtils::getEloquentSqlWithBindings($re));

        return $re->get()->first();
    }

    public static function getDiscounts($type = null, $product_id = null)
    {

        $sub = self::_dataWithCoupon();

        $select = [
            'sub.id',
            'sub.sn',
            'sub.title',
            'sub.category_title',
            'sub.category_code',
            'sub.method_code',
            'sub.method_title',
            'sub.discount_value',
            'sub.is_grand_total',
            'sub.min_consume',
            'sub.coupon_id',
            'sub.coupon_title',
            'sub.discount_grade_id',
            'sub.active'
        ];

        $re = DB::table(DB::raw("({$sub->toSql()}) as sub"))
            ->select($select)
            ->mergeBindings($sub)
            ->where('sub.status_code', DisStatus::D01()->value)
            ->where('sub.active', 1);
       
        if ($product_id) {
            $re->leftJoin('dis_discount_collection as dc', 'sub.id', '=', 'dc.discount_id')
                ->leftJoin('collection_prd as cp', 'dc.collection_id', '=', 'cp.collection_id_fk')
                ->where('cp.product_id_fk', $product_id);
        }

        switch ($type) {
            case 'global-normal': //全館優惠
                $re->where('sub.is_global', 1)
                    ->where('sub.category_code', DisCategory::normal()->value);
                break;
            case 'non-global-normal': //非全館優惠
                $re->where('sub.is_global', 0)
                    ->where('sub.category_code', DisCategory::normal()->value);
                break;
        }
        //   dd(DisStatus::D01()->value);

        //dd(IttmsUtils::getEloquentSqlWithBindings($re));

        $output = [];

        foreach ($re->get()->toArray() as $value) {
            if (!isset($output[$value->method_code])) {
                $output[$value->method_code] = [];
            }
            $output[$value->method_code][] = $value;
        }

        return $output;

    }

    public static function dataList($category = null,
        $disStatus = null,
        $title = null,
        $start_date = null,
        $end_date = null,
        $method_code = null,
        $is_global = null
    ) {

        $sub = self::_dataWithCoupon();

        $re = DB::table(DB::raw("({$sub->toSql()}) as sub"))
            ->mergeBindings($sub);

        if ($disStatus) {
            if (is_array($disStatus)) {
                $re->whereIn('status_code', $disStatus);
            } else {
                $re->where('status_code', $disStatus);
            }
        }

        if ($title) {
            $re->where('title', 'like', "%$title%");
        }

        if ($category) {
            if (is_array($category)) {
                $re->whereIn('category_code', $category);
            } else {
                $re->where('category_code', $category);
            }
        }

        if ($start_date) {
            $re->where('start_date', '>=', $start_date);
        }

        if ($end_date) {
            $re->where('end_date', '<=', date('Y-m-d 59:59:59', strtotime($end_date)));
        }

        if ($method_code) {
            if (is_array($method_code)) {
                $re->whereIn('method_code', $method_code);
            } else {
                $re->where('method_code', $method_code);
            }
        }
        if (!is_null($is_global)) {
            $re->where('is_global', $is_global);
        }

        return $re;

        // ->whereRaw("($selectStatusCode) as bb", "=", "D03");

        // dd($re->get()->toArray());
    }

    public static function createDiscount($title, $min_consume, DisMethod $method, $value, $start_date = null, $end_date = null, $is_grand_total = 0, $collection_ids = [])
    {
        if (count($collection_ids) > 0) {
            $is_global = 0;
        } else {
            $is_global = 1;
        }

        $data = self::_createDiscount($title, DisCategory::normal(), $method, $value, [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'is_grand_total' => $is_grand_total,
            'min_consume' => $min_consume,
            'is_global' => $is_global,
        ]);

        $id = self::create($data)->id;

        self::updateDiscountCollection($id, $collection_ids);
        return $id;

    }

    public static function createCoupon($title, $min_consume, DisMethod $method, $value, $is_grand_total = 0, $collection_ids = [], $life_cycle = 0)
    {

        return DB::transaction(function () use ($title, $min_consume, $method, $value, $is_grand_total, $collection_ids, $life_cycle) {
            if (count($collection_ids) > 0) {
                $is_global = 0;
            } else {
                $is_global = 1;
            }

            $sn = '';

            $sn = date("ymd") . str_pad((self::whereDate('created_at', '=', date('Y-m-d'))
                    ->where('category_code', DisCategory::coupon()->value)
                    ->withTrashed()
                    ->get()
                    ->count()) + 1, 3, '0', STR_PAD_LEFT);

            $data = self::_createDiscount($title, DisCategory::coupon(), $method, $value, [
                'is_grand_total' => $is_grand_total,
                'min_consume' => $min_consume,
                'is_global' => $is_global,
                'life_cycle' => $life_cycle,
                'sn' => $sn,
            ]);

            $id = self::create($data)->id;

            self::updateDiscountCollection($id, $collection_ids);
            return $id;

        });

    }

    public static function createCode($sn, $title, $min_consume, DisMethod $method, $value, $start_date = null, $end_date = null, $is_grand_total = 1, $collection_ids = [], $max_usage = 0)
    {

        DB::transaction(function () use ($sn, $title, $min_consume, $method, $value, $is_grand_total, $collection_ids, $start_date, $end_date, $max_usage) {
            if (count($collection_ids) > 0) {
                $is_global = 0;
            } else {
                $is_global = 1;
            }

            $data = self::_createDiscount($title, DisCategory::code(), $method, $value, [
                'is_grand_total' => $is_grand_total,
                'min_consume' => $min_consume,
                'is_global' => $is_global,
                'sn' => $sn,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'max_usage' => $max_usage,
            ]);

            $id = self::create($data)->id;

            self::updateDiscountCollection($id, $collection_ids);
            return $id;

        });

    }

    public static function checkCode($sn = null, $product_id = null)
    {
        $sub = self::_discountStatus();

        $select = [
            'sub.id',
            'sub.sn',
            'sub.title',
            'sub.category_title',
            'sub.category_code',
            'sub.method_code',
            'sub.method_title',
            'sub.discount_value',
            'sub.is_grand_total',
            'sub.min_consume',
            'sub.usage_count',
            'sub.max_usage',
            'sub.is_global',
            'sub.discount_grade_id',
        ];

        $re = DB::table(DB::raw("({$sub->toSql()}) as sub"))
            ->select($select)
            ->where('status_code', DisStatus::D01()->value)
            ->where('category_code', DisCategory::code()->value)
            ->where('sub.sn', $sn)
            ->get()->first();

        if (!$re) {
            return [
                'success' => '0',
                'message' => "查無序號,或此序號尚未在活動期間",
            ];
        }

        if ($re->max_usage != 0 && $re->usage_count > $re->max_usage) {
            return [
                'success' => '0',
                'message' => "序號超出使用次數",
            ];
        }
        if ($re->is_global == '1') {
            return [
                'success' => '1',
                'data' => $re,
            ];
        }

        if (!$product_id) {
            return [
                'success' => '0',
                'message' => "缺少product_id",
            ];
        }

        $collection = DB::table('dis_discount_collection as dc')
            ->leftJoin('collection_prd as cp', 'dc.collection_id', '=', 'cp.collection_id_fk')
            ->select('cp.product_id_fk as product_id')
            ->where('dc.discount_id', $re->id)
            ->whereIn('cp.product_id_fk', $product_id)
            ->distinct();

        $re->product_ids = array_map(function ($n) {
            return $n->product_id;
        }, $collection->get()->toArray());

        return [
            'success' => '1',
            'data' => $re,
        ];

    }

    private static function _createDiscount($title,
        DisCategory $disCategory,
        DisMethod $method,
        $discount_value,
        $options = []) {

        switch ($disCategory) {
            case DisCategory::normal();
                $disc = "global";
                break;
            case DisCategory::code():
            case DisCategory::coupon():
                $disc = "coupon";
                break;
            case DisCategory::combine():
                $disc = "optional";
                break;
        }
        $discount_grade_id = null;

        $data = [
            'title' => $title,
            'discount_grade_id' => $discount_grade_id,
            //   'status_code' => DisStatus::D00()->value,
            //  'status_title' => DisStatus::D00()->description,
            'category_code' => $disCategory->value,
            'category_title' => $disCategory->description,
            'method_code' => $method->value,
            'method_title' => $method->description,
            'discount_value' => $discount_value,
            'active' => 1,
        ];

        if (isset($options['is_global'])) {
            $data['is_global'] = $options['is_global'];
        }

        if (isset($options['start_date']) && $options['start_date']) {
            $data['start_date'] = date("Y-m-d 00:00:00", strtotime($options['start_date']));
        } else {
            $data['start_date'] = date("Y-m-d 00:00:00");
        }

        if (isset($options['end_date']) && $options['end_date']) {
            $data['end_date'] = date("Y-m-d 23:59:59", strtotime($options['end_date']));
        } else {
            $data['end_date'] = date('Y-m-d 23:59:59', strtotime(date('Y-m-d') . " +3 years"));
        }

        if ($method->value == DisMethod::fromKey('cash')->value) {
            if (isset($options['is_grand_total'])) {
                $data['is_grand_total'] = $options['is_grand_total'];
            }
        }

        if (isset($options['usage_count'])) {
            $data['usage_count'] = $options['usage_count'];
        }

        if (isset($options['max_usage'])) {
            $data['max_usage'] = $options['max_usage'];
        }

        if (isset($options['max_usage'])) {
            $data['max_usage'] = $options['max_usage'];
        }

        if (isset($options['min_consume'])) {
            $data['min_consume'] = $options['min_consume'];
        }

        if (isset($options['life_cycle'])) {
            $data['life_cycle'] = $options['life_cycle'];
        }

        if (isset($options['sn'])) {
            $data['sn'] = $options['sn'];
        }
        if (isset($options['max_usage'])) {
            $data['max_usage'] = $options['max_usage'];
        }

        return $data;
    }

    public static function updateDiscountCollection($discount_id, $collection_ids = [])
    {
        DB::table('dis_discount_collection')->where('discount_id', $discount_id)
            ->delete();

        if (is_array($collection_ids) && count($collection_ids) > 0) {
            DB::table('dis_discount_collection')->insert(array_map(function ($n) use ($discount_id) {
                return [
                    'discount_id' => $discount_id,
                    'collection_id' => $n,
                ];
            }, $collection_ids));
        }

    }

    public static function getDicountCollections($dis_id)
    {
        return DB::table('dis_discount_collection')->where('discount_id', $dis_id);
    }

    public static function delProcess($id)
    {
        self::where('id', $id)->delete();
        DB::table('dis_discount_collection')->where('discount_id', $id)->delete();
    }

    public static function createOrderDiscount($type, $order_id, $customer, $datas = [], $sub_order_id = null, $order_item_id = null)
    {

        // dd($datas);

        if (!$datas || count($datas) == 0) {
            return;
        }

        DB::table('ord_discounts')->insert(array_map(function ($n) use ($type, $order_id, $sub_order_id, $order_item_id, $customer) {
            $category = $n->category_code;
            $method = $n->method_code;

            $discount_grade_id = null;
            $receivedDefault = ReceivedDefault::where('name', $n->category_code)->get()->first();

            if ($receivedDefault) {
                $discount_grade_id = $receivedDefault->default_grade_id;
            }

            $d = [
                'order_type' => $type,
                'order_id' => $order_id,
                'title' => $n->title,
                'sn' => isset($n->sn) ? $n->sn : null,
                'sort' => DisCategory::getSort(DisCategory::$category()),
                'category_title' => $n->category_title,
                'category_code' => $n->category_code,
                'method_title' => $n->method_title,
                'method_code' => $n->method_code,
                'discount_value' => isset($n->currentDiscount) ? $n->currentDiscount : null,
                'is_grand_total' => $n->is_grand_total,
                'discount_grade_id' => $discount_grade_id,
                'sub_order_id' => $sub_order_id,
                'order_item_id' => $order_item_id,
                'discount_taxation' => 1,
            ];

            switch ($method) {
                case DisCategory::coupon()->value: //取得優惠券
                    $d['extra_title'] = $n->coupon_title;
                    $d['extra_id'] = $n->coupon_id;
                    $_coupon = Discount::where('id', $n->coupon_id)->get()->first();
                    if ($_coupon) {
                        CustomerCoupon::create([
                            'from_order_id' => $order_id,
                            'limit_day' => $_coupon->life_cycle,
                            'customer_id' => $customer->id,
                            'discount_id' => $n->coupon_id,
                        ]);
                    }
                    break;

                default:
                    $d['extra_title'] = null;
                    $d['extra_id'] = null;

            }
            // 處理coupon 使用優惠券
            if ($n->category_code == DisCategory::coupon() && !$sub_order_id && !$order_item_id) {
               
                CustomerCoupon::where('id', $n->user_coupon_id)->update([
                    'used' => 1,
                    'used_at' => now(),
                    'order_id' => $order_id,
                ]);
            }

            return $d;

        }, $datas));

    }

    public static function orderDiscountList($type, $order_id)
    {
        return DB::table('ord_discounts')->where('order_type', $type)
            ->where('order_id', $order_id);
           
    }


    public static function update_order_discount_taxation($parm)
    {
        DB::table('ord_discounts')->where('id', $parm['discount_id'])->update([
            'discount_grade_id'=>$parm['grade_id'],
            'discount_taxation'=>$parm['taxation'],
        ]);
    }
}
