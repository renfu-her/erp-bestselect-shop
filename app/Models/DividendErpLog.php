<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DividendErpLog extends Model
{
    use HasFactory;
    protected $table = 'dis_dividend_erp_log';
    protected $guarded = [];

    public static function dataList()
    {
        return DB::table('dis_dividend_erp_log as log')
            ->leftJoin('usr_customers as customer', 'log.customer_id', '=', 'customer.id')
            ->select(['log.*', 'customer.name']);
    }
}
