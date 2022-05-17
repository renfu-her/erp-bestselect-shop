<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CustomerCoupon extends Model
{
    use HasFactory;
    protected $table = 'usr_customer_coupon';
    protected $guarded = [];

    public static function getList($customer_id)
    {
        $sub = Discount::_discountStatus();
      
        //  $re = DB::table(DB::raw("({$sub->toSql()}) as sub"))

        return DB::table('usr_customer_coupon as cc')
            ->leftJoin(DB::raw("({$sub->toSql()}) as discount"), function ($join) {
                $join->on('cc.discount_id', '=', 'discount.id');
            })
            ->select('discount.*')
            ->where('cc.used', 0)
            ->where('customer_id', $customer_id);
           
        
    }
}
