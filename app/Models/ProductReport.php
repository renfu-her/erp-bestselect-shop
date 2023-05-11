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
        $products  = $product->get()->count();

        $suppliers = $product->join('prd_product_supplier as ps', 'product.id', '=', 'ps.product_id')
            ->selectRaw('count(*) as total')
            ->groupBy('ps.supplier_id');

        $seasonData = DB::table('rpt_user_report_monthly as rm')
            ->selectRaw('MONTH(month) as m')
            ->selectRaw('SUM(total_price) as total_price')
            ->selectRaw('SUM(total_gross_profit) as total_gross_profit')
            ->whereRaw('QUARTER(month) = ' . $quarter)
            ->whereRaw('YEAR(month) = ' . $year)
            ->groupByRaw('MONTH(month)');

        return [
            'products' => $products,
            'suppliers' => $suppliers->get()->count(),
            'seasons' => $seasonData->get()->toArray(),
        ];

    }
}
