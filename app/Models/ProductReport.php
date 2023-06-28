<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductReport extends Model
{
    use HasFactory;

    public static function dataList($year = null, $quarter = null)
    {
        $product = DB::table('prd_products as product')
            ->where('public', 1);
        $products = $product->get()->count();

        $suppliers = $product->join('prd_product_supplier as ps', 'product.id', '=', 'ps.product_id')
            ->selectRaw('count(*) as total')
            ->groupBy('ps.supplier_id');

        $seasonData = DB::query()->fromSub(DB::table('rpt_product_sale_daily as rm')
                ->selectRaw('YEAR(date) as y')
                ->selectRaw('MONTH(date) as m')
                ->selectRaw('QUARTER(date) as quarter')
                ->selectRaw('SUM(price) as total_price')
                ->selectRaw('SUM(gross_profit) as total_gross_profit')
                ->groupByRaw('YEAR(date)')
                ->groupByRaw('MONTH(date)'), 'data')
            ->where('data.y', $year)
            ->where('data.quarter', $quarter)
            ->orderBy('data.m');
       
        return [
            'products' => $products,
            'suppliers' => $suppliers->get()->count(),
            'seasons' => $seasonData->get()->toArray(),
        ];

    }
}
