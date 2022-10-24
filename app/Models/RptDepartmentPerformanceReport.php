<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RptDepartmentPerformanceReport extends Model
{
    use HasFactory;

    public static function report($date = null, $type = "date")
    {
        $datas = DB::table('ord_orders as order')
            ->leftJoin('ord_received_orders as ro', 'order.id', '=', 'ro.source_id')
            ->leftJoin('usr_customers as customer', 'order.mcode', '=', 'customer.sn')
            ->leftJoin('usr_users as user', 'user.customer_id', '=', 'customer.id')
            ->leftJoin('usr_user_organize as organize', 'organize.title', '=', 'user.group');

        dd($datas->limit(1)->get());

    }

}
