<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RptOrderDailyReport extends Model
{
    use HasFactory;
    protected $table = 'rpt_order_daily';
    protected $guarded = [];
    public $timestamps = false;

    public static function dataList($year, $quarter, $salechannel_id = null)
    {

        $seasonData = DB::table('rpt_order_daily as od')
            ->selectRaw('MONTH(month) as month')
            ->selectRaw('SUM(price_0) as price_0')
            ->selectRaw('SUM(gross_profit_0) as gross_profit_0')
            ->selectRaw('SUM(qty_0) as qty_0')
            ->selectRaw('SUM(price_1) as price_1')
            ->selectRaw('SUM(gross_profit_1) as gross_profit_1')
            ->selectRaw('SUM(qty_1) as qty_1')
            ->selectRaw('SUM(qty_1) + SUM(qty_0) as total_qty')
            ->selectRaw('SUM(price_1) + SUM(price_0) as total_price')
            ->selectRaw('SUM(gross_profit_1) + SUM(gross_profit_0) as total_gross_profit')
            ->whereRaw('YEAR(month) = ' . $year)
            ->whereRaw('QUARTER(month) = ' . $quarter)
            ->groupByRaw('MONTH(month)');
    
        if ($salechannel_id) {
            $seasonData->where('salechannel_id', $salechannel_id);
        }

        return $seasonData;

    }

    public static function report($date = null, $type = "date")
    {
        // 算商品毛利淨利
        $date = $date ? $date : date("Y-m-d 00:00:00", strtotime(now() . " -1 days"));

        switch ($type) {
            case 'date':
                $sdate = date("Y-m-d 00:00:00", strtotime($date));
                $edate = date("Y-m-d 23:59:59", strtotime($date));
                $currentMonth = Date("Y-m-d", strtotime($sdate));
                break;
            case 'month':
                $sdate = date("Y-m-01 00:00:00", strtotime($date));
                $edate = date("Y-m-t 23:59:59", strtotime($date));
                $currentMonth = Date("Y-m", strtotime($sdate));
                break;
        }

        self::where('month', 'like', "%$currentMonth%")->delete();

        $atomic = RptReport::atomic()
            ->selectRaw('IF(order.mcode IS NULL,0,1) AS has_mcode');
        //  dd($atomic->get()->toArray());
        $main = DB::query()->fromSub($atomic, 'atomic')
            ->select(['atomic.has_mcode', 'atomic.sale_channel_id'])
            ->selectRaw('SUM(atomic.total_price) as atomic_total_price')
            ->selectRaw('SUM(atomic.gross_profit) as atomic_gross_profit')
            ->selectRaw('COUNT(*) as qty')
            ->selectRaw('DATE_FORMAT(atomic.receipt_date, "%Y-%m-%d") as dd')
            ->whereBetween('atomic.receipt_date', [$sdate, $edate])
            ->groupBy('dd')
            ->groupBy('atomic.sale_channel_id')
            ->groupBy('atomic.has_mcode')
            ->limit(15)
            ->get()
            ->toArray();

        $data = [];
        foreach ($main as $value) {
            if (!isset($data[$value->dd])) {
                $data[$value->dd] = [];
            }

            if (!isset($data[$value->dd][$value->sale_channel_id])) {
                $data[$value->dd][$value->sale_channel_id] = [
                    'month' => $value->dd,
                    'salechannel_id' => $value->sale_channel_id,
                    'price_0' => 0,
                    'qty_0' => 0,
                    'gross_profit_0' => 0,
                    'price_1' => 0,
                    'qty_1' => 0,
                    'gross_profit_1' => 0,
                ];
            }

            $data[$value->dd][$value->sale_channel_id]['price_' . $value->has_mcode] = $value->atomic_total_price;
            $data[$value->dd][$value->sale_channel_id]['gross_profit_' . $value->has_mcode] = $value->atomic_gross_profit;
            $data[$value->dd][$value->sale_channel_id]['qty_' . $value->has_mcode] = $value->qty;

        }
        $output = [];
        foreach ($data as $v) {
            foreach ($v as $v2) {
                $output[] = $v2;
            }
        }

        //  dd($output);

        self::insert($output);

    }
}
