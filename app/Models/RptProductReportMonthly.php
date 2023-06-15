<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RptProductReportMonthly extends Model
{
    use HasFactory;
    protected $table = 'rpt_product_report_monthly';
    protected $guarded = [];
    public $timestamps = false;

    public static function dataList($year, $quarter)
    {

        $seasonData = DB::query()->fromSub(DB::table('rpt_product_report_monthly as rm')
                ->select(['product_id'])
                ->selectRaw('SUM(price) as price')
                ->selectRaw('SUM(gross_profit) as gross_profit')
                ->selectRaw('SUM(qty) as qty')
                ->groupBy('product_id')
                ->whereRaw('YEAR(month) = ' . $year)
                ->whereRaw('QUARTER(month) = ' . $quarter), 'data')
            ->leftJoin('prd_products as product', 'data.product_id', '=', 'product.id')
            ->leftJoin('prd_categorys as category', 'product.category_id', '=', 'category.id')
            ->select(['data.*', 'product.title as product_title', 'category.category']);

        //  ->orderBy('data.m');

        return $seasonData;

    }

    public static function dataListCategory($year, $quarter)
    {

        $seasonData = DB::query()->fromSub(DB::table('rpt_product_report_monthly as rm')
                ->leftJoin('prd_products as product', 'rm.product_id', '=', 'product.id')
                ->select(['category_id'])
                ->selectRaw('SUM(price) as price')
                ->selectRaw('SUM(gross_profit) as gross_profit')
                ->selectRaw('SUM(qty) as qty')
                ->groupBy('category_id')
                ->whereRaw('YEAR(month) = ' . $year)
                ->whereRaw('QUARTER(month) = ' . $quarter), 'data')
            ->leftJoin('prd_categorys as category', 'data.category_id', '=', 'category.id')

            ->select(['data.*', 'category.category']);

        //  ->orderBy('data.m');

        return $seasonData;

    }

    public static function report($date = null, $type = "date")
    {
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

        $atomic = RptReport::atomic();
        $atomic->leftJoin('prd_product_styles as product_style', 'product_style.id', 'item.product_style_id')
            ->addSelect(['product_style.product_id']);

        //   dd($atomic->limit(10)->get()->toArray());

        $main = DB::query()->fromSub($atomic, 'atomic')
            ->select(['atomic.product_id', 'atomic.product_style_id'])
            ->selectRaw('SUM(atomic.total_price) as atomic_total_price')
            ->selectRaw('SUM(atomic.gross_profit) as atomic_gross_profit')
            ->selectRaw('SUM(atomic.qty) as atomic_qty')
            ->selectRaw('DATE_FORMAT(atomic.receipt_date, "%Y-%m-%d") as dd')
            ->whereBetween('atomic.receipt_date', [$sdate, $edate])
            ->groupBy('dd')
            ->groupBy('atomic.product_id')
            ->groupBy('atomic.product_style_id');

        self::insert(array_map(function ($n) {
            return [
                'month' => $n->dd,
                'product_id' => $n->product_id,
                'product_style_id' => $n->product_style_id,
                'price' => $n->atomic_total_price,
                'gross_profit' => $n->atomic_gross_profit,
                'qty' => $n->atomic_qty,
            ];
        }, $main->get()->toArray()));

    }
}
