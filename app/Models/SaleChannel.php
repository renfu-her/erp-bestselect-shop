<?php

namespace App\Models;

use App\Enums\Customer\Bonus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class SaleChannel extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'prd_sale_channels';
    protected $guarded = [];

    public static function saleList()
    {
        return self::select('*')->selectRaw('IF(is_realtime=1,"即時","非即時") as is_realtime_title');

    }

    public static function changeStock($sale_id, $style_id, $qty)
    {

        return DB::transaction(function () use ($sale_id, $style_id, $qty) {
            $tableName = 'prd_salechannel_style_stock';

            if (!DB::table($tableName)
                ->where('style_id', $style_id)
                ->where('sale_channel_id', $sale_id)
                ->get()
                ->first()) {

                DB::table($tableName)
                    ->insert(['style_id' => $style_id,
                        'sale_channel_id' => $sale_id,
                        'in_stock' => $qty]);

            } else {
                DB::table($tableName)
                    ->where('style_id', $style_id)
                    ->where('sale_channel_id', $sale_id)
                    ->update([
                        'in_stock' => DB::raw("in_stock + $qty")]);

            }
        });
    }
    /**
     * 非即時庫存
     */
    public static function styleStockList($style_id)
    {
        return DB::table('prd_sale_channels as c')
            ->leftJoin('prd_salechannel_style_stock as stock', 'c.id', '=', 'stock.sale_channel_id', 'left outer')
            ->select('c.id as sale_id', 'c.title')
            ->selectRaw('IF(stock.in_stock,stock.in_stock,0) as in_stock')
            ->whereNull('c.deleted_at')
            ->where('c.is_realtime', '0')
            ->where(function ($q) use ($style_id) {
                $q->where('stock.style_id', $style_id)
                    ->orWhereNull('stock.style_id');
            });
    }

    /**
     * 訂單未出庫
     */
    public static function notCompleteDelivery($style_id)
    {
        return DB::table('prd_sale_channels as c')
            ->select('c.id as sale_id', 'c.title')
            ->whereNull('c.deleted_at')
            ->where('c.is_realtime', '1');
    }

    /**
     * 價格列表
     */

    public static function stylePriceList($style_id)
    {
        return DB::table('prd_sale_channels as c')
            ->leftJoin('prd_salechannel_style_price as price', 'c.id', '=', 'price.sale_channel_id', 'left outer')
            ->select('c.id as sale_id', 'c.title', 'c.is_realtime')
            ->selectRaw('IF(price.dealer_price,price.dealer_price,0) as dealer_price')
            ->selectRaw('IF(price.origin_price,price.origin_price,0) as origin_price')
            ->selectRaw('IF(price.price,price.price,0) as price')
            ->selectRaw('IF(price.bonus,price.bonus,0) as bonus')
            ->selectRaw('IF(price.dividend,price.dividend,0) as dividend')
            ->whereNull('c.deleted_at')
            ->where(function ($q) use ($style_id) {
                $q->where('price.style_id', $style_id)
                    ->orWhereNull('price.style_id');
            });
    }

    public static function changePrice($sale_id, $style_id, $dealer_price, $price, $origin_price, $bonus, $dividend)
    {

        return DB::transaction(function () use ($sale_id, $style_id, $dealer_price, $price, $origin_price, $bonus, $dividend) {
            $tableName = 'prd_salechannel_style_price';
            $updateData = ['dealer_price' => $dealer_price,
                'price' => $price,
                'origin_price' => $origin_price,
                'bonus' => $bonus,
                'dividend' => $dividend];

            if (!DB::table($tableName)
                ->where('style_id', $style_id)
                ->where('sale_channel_id', $sale_id)
                ->get()
                ->first()) {

                DB::table($tableName)
                    ->insert(array_merge(['style_id' => $style_id,
                        'sale_channel_id' => $sale_id,
                    ], $updateData));
            } else {

                DB::table($tableName)
                    ->where('style_id', $style_id)
                    ->where('sale_channel_id', $sale_id)
                    ->update($updateData);
            }

        });
    }

    public static function batchPrice($sale_id)
    {
        DB::transaction(function () use ($sale_id) {
            $masterSale = self::where('is_master', 1)->select('id')->get()->first();
            if (!$masterSale) {
                return;
            }

            $currentSale = self::where('is_master', '<>', 1)
                ->where('id', $sale_id)
                ->select('id', 'discount')
                ->get()
                ->first();

            $originProduct = DB::table('prd_salechannel_style_price')
                ->where('sale_channel_id', $masterSale->id)
                ->get();

            foreach ($originProduct as $product) {
                // dd($product);
                $newPrice = DB::table('prd_salechannel_style_price')
                    ->where('sale_channel_id', $currentSale->id)
                    ->where('style_id', $product->style_id)
                    ->get()->first();

                if (!$newPrice) {
                    $price = round($product->price * $currentSale->discount);
                    $bonus = round(($price - $product->dealer_price) * Bonus::bonus()->value);
                    DB::table('prd_salechannel_style_price')
                        ->insert([
                            'style_id' => $product->style_id,
                            'sale_channel_id' => $currentSale->id,
                            'dealer_price' => $product->dealer_price,
                            'origin_price' => $product->origin_price,
                            'price' => $price,
                            'bonus' => $bonus,
                        ]);
                }
            }
        });
    }
}
