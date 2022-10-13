<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RptProductReportDaily extends Model
{
    use HasFactory;
    protected $table = 'rpt_product_sale_daily';
    protected $guarded = [];
    public $timestamps = false;

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

        self::getRawData($sdate, $edate, $currentDate);
        self::CombineSaleChannel($sdate, $edate, $currentDate);
        self::getManager();
    }

    public static function getRawData($sdate, $edate, $currentDate)
    {

        self::where('date', 'like', "%$currentDate%")->delete();

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
                'sale_channel.sales_type',
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
            ->groupBy('style.id')
            ->groupBy('sale_channel.sales_type')->get()->toArray();

        self::insert(array_map(function ($n) {
            return (array) $n;
        }, $re));
    }

    public static function CombineSaleChannel($sdate, $edate, $currentDate)
    {

        DB::table('rpt_product_sale_daily_combine')->where('date', 'like', "%$currentDate%")->delete();

        $tt = concatStr([
            'date' => 'sd.date',
            'sales_type' => 'sd.sales_type',
            'product_id' => 'sd.product_id',
            'style_id' => 'sd.style_id',
            'price' => 'sd.price',
            'estimated_cost' => 'sd.estimated_cost',
            'gross_profit' => 'sd.gross_profit',
            'qty' => 'sd.qty',
        ]);

        $re = DB::table('rpt_product_sale_daily as sd')
            ->select(['sd.date'])
            ->selectRaw("$tt as ddd")
            ->whereBetween('sd.date', [$sdate, $edate])
            ->groupBy('sd.date')->limit(3)->get();

        foreach ($re as $value) {
            $items = json_decode($value->ddd);
            $data = [];
            foreach ($items as $item) {

                if (!isset($data[$item->style_id])) {
                    $data[$item->style_id] = [
                        'date' => $item->date,
                        'product_id' => $item->product_id,
                        'style_id' => $item->style_id,
                        'on_price' => 0,
                        'on_estimated_cost' => 0,
                        'on_gross_profit' => 0,
                        'on_qty' => 0,
                        'off_price' => 0,
                        'off_estimated_cost' => 0,
                        'off_gross_profit' => 0,
                        'off_qty' => 0,
                        'total_price' => 0,
                        'total_gross_profit' => 0,
                        'total_qty' => 0,
                    ];

                    if ($item->sales_type == 0) {
                        $data[$item->style_id]['off_price'] = $item->price;
                        $data[$item->style_id]['off_estimated_cost'] = $item->estimated_cost;
                        $data[$item->style_id]['off_gross_profit'] = $item->gross_profit;
                        $data[$item->style_id]['off_qty'] = $item->qty;

                    } else {
                        $data[$item->style_id]['on_price'] = $item->price;
                        $data[$item->style_id]['on_estimated_cost'] = $item->estimated_cost;
                        $data[$item->style_id]['on_gross_profit'] = $item->gross_profit;
                        $data[$item->style_id]['on_qty'] = $item->qty;
                    }

                    $data[$item->style_id]['total_price'] += $item->price;
                    $data[$item->style_id]['total_gross_profit'] += $item->gross_profit;
                    $data[$item->style_id]['total_qty'] += $item->qty;

                }
            }

            DB::table('rpt_product_sale_daily_combine')->insert($data);

        }

    }

    //  usr_product_manager_user
    public static function getManager()
    {
        $sub = DB::table('prd_products as product')
            ->select('product.user_id')
            ->groupBy('product.user_id')->get()->toArray();

        DB::table('usr_product_manager_user')->truncate();

        DB::table('usr_product_manager_user')->insert(array_map(function ($n) {
            return ['user_id' => $n->user_id];
        }, $sub));
    }

}
