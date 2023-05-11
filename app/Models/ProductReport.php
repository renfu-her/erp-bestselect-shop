<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductReport extends Model
{
    use HasFactory;

    public static function createData($year = null, $quarter = null)
    {
        $product = DB::table('prd_products as product')
            ->where('public', 1);
        $products = $product->get()->count();

        $suppliers = $product->join('prd_product_supplier as ps', 'product.id', '=', 'ps.product_id')
            ->selectRaw('count(*) as total')
            ->groupBy('ps.supplier_id');

        $seasonData = DB::query()->fromSub(DB::table('rpt_user_report_monthly as rm')
                ->selectRaw('YEAR(month) as y')
                ->selectRaw('MONTH(month) as m')
                ->selectRaw('QUARTER(month) as quarter')
                ->selectRaw('SUM(total_price) as total_price')
                ->selectRaw('SUM(total_gross_profit) as total_gross_profit')
                ->groupByRaw('YEAR(month)')
                ->groupByRaw('MONTH(month)'), 'data')
            ->where('data.y', $year)
            ->where('data.quarter', $quarter);
       
        return [
            'products' => $products,
            'suppliers' => $suppliers->get()->count(),
            'seasons' => $seasonData->get()->toArray(),
        ];

    }
}
