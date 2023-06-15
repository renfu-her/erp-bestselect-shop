<?php

namespace App\Models;

use App\Enums\Order\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RptReport extends Model
{
    use HasFactory;

    public static function atomic()
    {

        return DB::table('ord_orders as order')
            ->leftJoin('ord_received_orders as ro', 'order.id', '=', 'ro.source_id')
            ->leftJoin('ord_sub_orders as sub_order', 'order.id', '=', 'sub_order.order_id')
            ->leftJoin('ord_items as item', 'sub_order.id', '=', 'item.sub_order_id')
            ->leftJoin('prd_product_styles as style', 'style.id', '=', 'item.product_style_id')
            ->select([
                'order.sale_channel_id',
                'order.email',
                'order.sn as order_sn',
                'order.id as order_id',
                'item.qty',
                'order.mcode',
                'item.price',
                'item.product_style_id',
                'ro.receipt_date',
                'style.estimated_cost',
            ])
            ->selectRaw("item.price * item.qty - style.estimated_cost * item.qty as gross_profit")
            ->selectRaw("item.price * item.qty as total_price")
            ->whereNotNull('style.estimated_cost')
            ->whereNotNull('order.mcode')
            ->where('ro.source_type', 'ord_orders')
            ->where('order.payment_status', PaymentStatus::Received())
            ->whereIn('order.status_code', ['received', 'back_processing', 'cancle_back', 'backed'])
            ->whereNotNull('ro.receipt_date')
            ->whereNull('style.deleted_at');

    }

    public static function dateRange($type = null, $year = null, $options = [])
    {
        switch ($type) {
            case 'year':
                $sdate = date("Y-01-01", strtotime($year));
                $edate = date("Y-12-31", strtotime($year));
                break;
            case 'month':
                if (isset($options['month'])) {
                    $sdate = date("Y-" . $options['month'] . "-01", strtotime($year));
                    $edate = date("Y-" . $options['month'] . "-t", strtotime($year));
                }
                break;
            case 'season':
                if (isset($options['season'])) {
                    switch ($options['season']) {
                        case '1':
                            $_m = [1, 3];
                            break;
                        case '2':
                            $_m = [4, 6];
                            break;
                        case '3':
                            $_m = [7, 9];
                            break;
                        case '4':
                            $_m = [10, 12];
                            break;
                    }

                    $sdate = date("Y-$_m[0]-01", strtotime($year));
                    $edate = date("Y-$_m[1]-t", strtotime($year));

                }
                break;

        }
        return [$sdate, $edate];
    }

    public static function report($date = '2022-09-01')
    {

        if (!$date) {
            $sdate = Date("Y-m-1 00:00:00");
            $edate = Date("Y-m-t 23:59:59");
        } else {
            $sdate = Date("Y-m-1 00:00:00", strtotime($date));
            $edate = Date("Y-m-t 23:59:59", strtotime($date));
        }

        $atomic = self::atomic();

        $re = DB::table(DB::raw("({$atomic->toSql()}) as atomic"))
            ->mergeBindings($atomic)
            ->leftJoin('prd_sale_channels as sh', 'atomic.sale_channel_id', '=', 'sh.id')
            ->leftJoin('usr_customers as customer', 'customer.email', '=', 'atomic.email')
            ->join('usr_users as user', 'user.customer_id', '=', 'customer.id')
            ->select(['customer.email', 'user.id as user_id', 'sh.sales_type'])
            ->selectRaw('SUM(atomic.total_price) as total_price')
            ->selectRaw('SUM(atomic.gross_profit) as gross_profit')
            ->selectRaw('DATE_FORMAT(atomic.receipt_date, "%Y-%m-01") as dd')
            ->whereBetween('atomic.receipt_date', [$sdate, $edate])
        //    ->where('atomic.email', 'p0931700502@gmail.com')
            ->groupBy('dd')
            ->groupBy('atomic.email')
            ->groupBy('sh.sales_type')->get();

        $user = [];
        foreach ($re as $value) {
            if (!isset($user[$value->user_id])) {
                $user[$value->user_id] = ['0' => [], '1' => []];
            }

            $user[$value->user_id][$value->sales_type] = $value;
        }

        dd($user);

    }

}
