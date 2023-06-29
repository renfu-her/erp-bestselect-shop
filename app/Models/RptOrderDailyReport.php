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
            ->select(['atomic.has_mcode'])
            ->selectRaw('SUM(atomic.total_price) as atomic_total_price')
            ->selectRaw('SUM(atomic.gross_profit) as atomic_gross_profit')
            ->selectRaw('COUNT(*) as qty')
            ->selectRaw('DATE_FORMAT(atomic.receipt_date, "%Y-%m-%d") as dd')
            ->whereBetween('atomic.receipt_date', [$sdate, $edate])
            ->groupBy('dd')
            ->groupBy('atomic.has_mcode')
            ->get()
            ->toArray();
        //  dd($main);
        $data = [];
        foreach ($main as $value) {
            if (!isset($data[$value->dd])) {
                $data[$value->dd] = [
                    'month' => $value->dd,
                    'price_0' => 0,
                    'qty_0' => 0,
                    'gross_profit_0' => 0,
                    'price_1' => 0,
                    'qty_1' => 0,
                    'gross_profit_1' => 0,
                ];

                $data[$value->dd]['price_' . $value->has_mcode] = $value->atomic_total_price;
                $data[$value->dd]['gross_profit_' . $value->has_mcode] = $value->atomic_gross_profit;
                $data[$value->dd]['qty_' . $value->has_mcode] = $value->qty;

            }
        }

        self::insert(array_map(function ($n) {
            return $n;
        }, $data));

      

    }
}
