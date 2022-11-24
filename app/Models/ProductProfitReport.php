<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ProductProfitReport extends Model
{
    /*
     * 取得售價利潤報表資料
     */
    public static function getProductProfitData($param)
    {
        $products = PurchaseInbound::productStyleListWithExistInbound([], $param)
                                ->selectRaw('CEIL((price.dealer_price-s.estimated_cost)*100/price.dealer_price) AS dealer_price_profit')
                                ->selectRaw('CEIL((price.price-s.estimated_cost)*100/price.price) AS price_profit');

        if ($param['profit'] == 'price_profit') {
            $products->orderBy('price_profit', 'desc');
        } else if ($param['profit'] == 'dealer_price_profit') {
            $products->orderBy('dealer_price_profit', 'desc');
        }

        $products->orderBy('s.product_id')
                ->orderBy('s.id');

        if ($param['stock_status'] == 'in_stock') {
            $products->where('inbound.total_in_stock_num', '>', 0);
        } else if ($param['stock_status'] == 'out_of_stock') {
            $products->where(function ($q) {
                $q->where('inbound.total_in_stock_num', '=', 0)
                    ->orWhereNull('inbound.total_in_stock_num');
            });
        }

        return $products;
    }

}
