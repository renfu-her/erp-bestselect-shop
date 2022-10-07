<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RptProductManagerSaleDaily extends Model
{
    use HasFactory;

    public static function report($date = '2022-09-20', $type = "date")
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
                        'total_profit' => 0,
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
                    $data[$item->style_id]['total_profit'] += $item->gross_profit;

                }
            }

        }
        dd($data);

    }

}
