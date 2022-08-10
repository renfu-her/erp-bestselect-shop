<?php

namespace Database\Seeders;

use App\Models\ConsignmentItem;
use App\Models\Consum;
use App\Models\CsnOrderItem;
use App\Models\DlvBack;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductStyle;
use App\Models\PurchaseImportLog;
use App\Models\PurchaseInbound;
use App\Models\PurchaseItem;
use App\Models\PurchaseLog;
use App\Models\ReceiveDepot;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportPurchaseChangeTitleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pcs_items_calc_leng = DB::table(app(PurchaseItem::class)->getTable() . ' as pcs_item')
            ->select(
                'pcs_item.product_style_id'
                , 'pcs_item.sku'
                , DB::raw('CHAR_LENGTH( pcs_item.title ) AS leng')
            );
        $pcs_items = DB::query()->fromSub($pcs_items_calc_leng, 'tb')
            ->where('tb.leng', '=', 40)
            ->groupBy('tb.product_style_id')
            ->having(DB::raw('count(tb.product_style_id)'), '>', 1)
            ->orderBy('tb.product_style_id')
        ;

        $product = DB::table(app(Product::class)->getTable() . ' as product')
            ->leftJoin(app(ProductStyle::class)->getTable() . ' as style', 'style.product_id', '=', 'product.id')
            ->leftJoinSub($pcs_items, 'item', function($join) {
                $join->on('item.product_style_id', '=', 'style.id')
                    ->whereNotNull('style.id');
            })
            ->select(
                'item.product_style_id as product_style_id'
                , DB::raw('Concat(product.title, "-", style.title) AS product_title')
                , 'style.sku as sku'
            )
            ->whereNotNull('product.id')
            ->whereNotNull('style.id')
            ->whereNotNull('item.product_style_id')
            ;

        $product = $product->get();
        if (isset($product) && 0 < count($product)) {
            foreach ($product as $item) {
                $update_title = ['title' => $item->product_title];
                $update_product_title = ['product_title' => $item->product_title];

                PurchaseItem::where('product_style_id', '=', $item->product_style_id)->update($update_title);
                ConsignmentItem::where('product_style_id', '=', $item->product_style_id)->update($update_title);
                CsnOrderItem::where('product_style_id', '=', $item->product_style_id)->update($update_title);
                PurchaseInbound::where('product_style_id', '=', $item->product_style_id)->update($update_title);
                PurchaseImportLog::where('sku', '=', $item->sku)->update($update_title);

                OrderItem::where('product_style_id', '=', $item->product_style_id)->update($update_product_title);
                ReceiveDepot::where('product_style_id', '=', $item->product_style_id)->update($update_product_title);
                Consum::where('product_style_id', '=', $item->product_style_id)->update($update_product_title);
                PurchaseLog::where('product_style_id', '=', $item->product_style_id)->update($update_product_title);
                DlvBack::where('product_style_id', '=', $item->product_style_id)->update($update_product_title);
            }
        }
        echo "匯入完成";
    }
}
