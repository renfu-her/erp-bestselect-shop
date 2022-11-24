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
            ->orderBy('s.product_id')
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
