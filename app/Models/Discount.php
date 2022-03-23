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


    

    public static function dataList($category = null,
        $disStatus = null,
        $title = null,
        $start_date = null,
        $end_date = null,
        $method_code = null
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

        $sub = self::select('*')
            ->selectRaw($selectStatus)
            ->selectRaw($selectStatusCode);

        $re = DB::table(DB::raw("({$sub->toSql()}) as sub"));

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
                $re->whereIn('category_code', array_map(function ($n) {
                    return $n->value;
                }, $category));
            } else {
                $re->where('category_code', $category->value);
            }
        }

        if ($start_date) {
            $re->where('start_date', '>=', $start_date);
        }

        if ($end_date) {
            $re->where('end_date', '<=', $end_date);
        }

        if ($method_code) {
            if (is_array($method_code)) {
                $re->whereIn('method_code', $method_code);
            } else {
                $re->where('method_code', $method_code);
            }
        }

        return $re;

        // ->whereRaw("($selectStatusCode) as bb", "=", "D03");

        // dd($re->get()->toArray());
    }

    public static function createDiscount($title, $min_consume, DisMethod $method, $value, $start_date = null, $end_date = null, $is_grand_total = 1, $collection_ids = [])
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

    public static function createCoupon($title, $min_consume, DisMethod $method, $value, $is_grand_total = 1, $collection_ids = [], $life_cycle = 0)
    {

        DB::transaction(function () use ($title, $min_consume, $method, $value, $is_grand_total, $collection_ids, $life_cycle) {
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

    private static function _createDiscount($title,
        DisCategory $disCategory,
        DisMethod $method,
        $discount_value,
        $options = []) {
        $data = [
            'title' => $title,
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
}
