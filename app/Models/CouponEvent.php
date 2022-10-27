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

        $re = DB::table('dis_coupon_event as ce')
            ->leftJoin('dis_discounts as discount', 'discount.id', '=', 'ce.discount_id')
            ->select(['ce.*', 'discount.title as discount_title'])
            ->whereNull('ce.deleted_at');

        if ($title) {
            $re->where('ce.title', 'like', "%$title%");
        }

        return $re;
    }
}
