<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderCustomerProfitReport extends Model
{
    use HasFactory;
    protected $table = 'ord_customer_profit_report';
    protected $guarded = [];

    public static function dataList($month_profit_report_id, $bank_type = null)
    {

        $re = DB::table('ord_customer_profit_report as report')
            ->select(['report.*',
                'customer.id as customer_id',
                'customer.name',
                'customer.sn as mcode',
                'bank.title as bank_title',
                'bank.code as bank_code',
                'p_report.report_at as report_at',
                'c_profit.bank_account',
                'c_profit.bank_account_name',
                'c_profit.identity_sn',
            ])
            ->selectRaw('DATE_FORMAT(report.created_at,"%Y%m%d") as created_at')
        //  ->selectRaw('CONCAT(bank.code,c_profit.bank_account) as full_bank_account')
            ->selectRaw('LEFT(CONCAT(bank.code,c_profit.bank_account),7) as new_bank_code')
        //   ->selectRaw('SUBSTRING(CONCAT(bank.code,c_profit.bank_account),6) as new_bank_account')
            ->leftJoin('usr_customers as customer', 'report.customer_id', '=', 'customer.id')
            ->leftJoin('usr_customer_profit as c_profit', 'c_profit.customer_id', '=', 'customer.id')
            ->leftJoin('acc_banks as bank', 'c_profit.bank_id', '=', 'bank.id')
            ->leftJoin('ord_month_profit_report as p_report', 'p_report.id', '=', 'report.month_profit_report_id')
            ->where('month_profit_report_id', $month_profit_report_id);

        if ($bank_type) {
            if ($bank_type == "a") {
                $re->where('bank.code', '006');
            } else {
                $re->where('bank.code', "<>", '006');
            }
        }

        return $re;
    }

    public static function createCustomerReport($month_id, $date)
    {

        DB::beginTransaction();
        $sdate = date("Y-m-1 00:00:00", strtotime($date));
        $edate = date("Y-m-t 23:59:59", strtotime($date));

        $profits = DB::table('ord_order_profit as profit')
            ->leftJoin('ord_sub_orders as sub_order', 'profit.sub_order_id', '=', 'sub_order.id')
            ->leftJoin('ord_orders as order', 'profit.order_id', '=', 'order.id')
            ->select('profit.customer_id')
            ->selectRaw('SUM(profit.bonus) as bonus')
            ->selectRaw('count(*) as qty')
            ->whereBetween('sub_order.dlv_audit_date', [$sdate, $edate])
            ->where('bonus', '>', 0)
            ->groupBy('profit.customer_id')->get();

        foreach ($profits as $profit) {

            self::create([
                'customer_id' => $profit->customer_id,
                'bonus' => $profit->bonus,
                'qty' => $profit->qty,
                'month_profit_report_id' => $month_id,
            ]);
        }

        DB::commit();
    }
}
