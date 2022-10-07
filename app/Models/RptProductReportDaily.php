<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RptProductReportDaily extends Model
{
    use HasFactory;

    public static function report($date = null, $type = "date")
    {

        switch ($type) {
            case 'date':
                if (!$date) {
                    $d = strtotime(date('Y-m-d') . " -1 day");
                    $sdate = Date("Y-m-d 00:00:00", $d);
                    $edate = Date("Y-m-d 23:59:59", $d);
                } else {
                    $sdate = Date("Y-m-d 00:00:00", strtotime($date));
                    $edate = Date("Y-m-d 23:59:59", strtotime($date));
                }
                $currentDate = Date("Y-m-d", strtotime($sdate));

                break;

            case 'month':
                if (!$date) {
                    $d = strtotime(date('Y-m-d') . " -1 day");
                    $sdate = Date("Y-m-1 00:00:00", $d);
                    $edate = Date("Y-m-t 23:59:59", $d);
                } else {
                    $sdate = Date("Y-m-1 00:00:00", strtotime($date));
                    $edate = Date("Y-m-t 23:59:59", strtotime($date));
                }
                $currentDate = Date("Y-m", strtotime($sdate));

                break;

        }
        dd($currentDate);

        $re = DB::table('prd_products as product')
            ->leftJoin('usr_users as user', 'product.user_id', '=', 'user.id')
            ->leftJoin('prd_product_styles as style', 'product.id', '=', 'style.product_id')
            ->leftJoin('ord_items as item', 'item.product_style_id', '=', 'style.id')
            ->leftJoin('ord_orders as order', 'item.order_id', '=', 'order.id')
            ->leftJoin('prd_sale_channels as sale_channel', 'order.sale_channel_id', '=', 'sale_channel.id')
            ->leftJoin('ord_received_orders as ro', 'ro.source_id', '=', 'order.id')
            ->select([
                'product.id as product_id',
                'style.id as style_id',
            ])
            ->selectRaw('DATE_FORMAT(ro.receipt_date, "%Y-%m-%d") as date')
            ->selectRaw('SUM(item.qty) as qty')
            ->selectRaw('SUM(item.qty * item.price) as price')
            ->selectRaw('SUM(item.qty * style.estimated_cost) as estimated_cost')
            ->selectRaw('SUM(item.qty * item.price - item.qty * style.estimated_cost) as gross_profit')
            ->whereNotNull('ro.receipt_date')
            ->whereBetween('ro.receipt_date', [$sdate, $edate])
            ->groupBy('date')
            ->groupBy('product.id')
            ->groupBy('style.id');

        dd($re->limit(10)->get());
    }
}
