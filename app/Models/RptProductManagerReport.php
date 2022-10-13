<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RptProductManagerReport extends Model
{
    use HasFactory;

    public static function managers()
    {
        return DB::table('usr_product_manager_user as mu')
            ->select(['user.name', 'user.id'])
            ->join('usr_users as user', 'mu.user_id', '=', 'user.id');

    }

    public static function managerList($sdate, $edate, $options = [])
    {
        $sub = DB::table('rpt_product_sale_daily_combine as sd')
            ->join('prd_products as product', 'sd.product_id', '=', 'product.id')
            ->select('product.user_id')
            ->selectRaw('SUM(sd.on_qty) as on_qty')
            ->selectRaw('SUM(sd.on_price) as on_price')
            ->selectRaw('SUM(sd.on_estimated_cost) as on_estimated_cost')
            ->selectRaw('SUM(sd.on_gross_profit) as on_gross_profit')
            ->selectRaw('SUM(sd.off_qty) as off_qty')
            ->selectRaw('SUM(sd.off_price) as off_price')
            ->selectRaw('SUM(sd.off_estimated_cost) as off_estimated_cost')
            ->selectRaw('SUM(sd.off_gross_profit) as off_gross_profit')
            ->selectRaw('SUM(sd.total_price) as total_price')
            ->selectRaw('SUM(sd.total_gross_profit) as total_gross_profit')
            ->groupBy('product.user_id')
            ->whereBetween('sd.date', [$sdate, $edate]);

        $re = DB::table('usr_product_manager_user as mu')
            ->leftJoinSub($sub, 'report', 'mu.user_id', '=', 'report.user_id')
            ->leftJoin('usr_users as user','user.id','=','mu.user_id')
            ->select(['user.name','user.id as user_id'])
            ->selectRaw('IFNULL(report.on_price, 0) as on_price')
            ->selectRaw('IFNULL(report.on_gross_profit, 0) as on_gross_profit')
            ->selectRaw('IFNULL(report.off_price, 0) as off_price')
            ->selectRaw('IFNULL(report.off_gross_profit, 0) as off_gross_profit')
            ->selectRaw('IFNULL(report.total_gross_profit, 0) as total_gross_profit')
            ->selectRaw('IFNULL(report.total_price, 0) as total_price');

        if (isset($options['user_id']) && count($options['user_id']) > 0) {
            $re->whereIn('user.id', $options['user_id']);
        }

        return $re;
    }

    public static function productList($sdate, $edate, $options = []){

    }


    /*
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
 */
}
