<?php

namespace Database\Seeders;

use App\Enums\Globals\Status;
use App\Models\ProductStyle;
use App\Models\ProductSupplier;
use App\Models\Purchase;
use App\Models\PurchaseImportLog;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InboundImportToAddPrdSupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //找到Log內的款式對應商品 當初跟哪家供應商採購
        $productSupplier = DB::table(app(PurchaseImportLog::class)->getTable(). ' as pcs_import_log')
            ->leftJoin(app(Purchase::class)->getTable(). ' as pcs', function ($join) {
                $join->on('pcs.sn', '=', 'pcs_import_log.purchase_sn');
                $join->where('pcs_import_log.status', '=', Status::success()->value);
                $join->whereNotNull('pcs_import_log.sku');
            })
            ->leftJoin(app(ProductStyle::class)->getTable(). ' as style', function ($join) {
                $join->on('style.sku', '=', 'pcs_import_log.sku');
                $join->whereNotNull('pcs_import_log.sku');
            })
            ->select(
                'style.product_id'
                , 'pcs.supplier_id'
                , 'pcs.supplier_name'
            )
            ->where('pcs_import_log.status', '=', Status::success()->value)
            ->groupBy('style.product_id')
            ->groupBy('pcs.supplier_id')
            ->groupBy('pcs.supplier_id')
            ->get()
        ;
        //找到廠商 = 無廠商的資料
        if (isset($productSupplier) && 0 < count($productSupplier)) {
            $supplier_wuchangsang = Supplier::where('name', '=', '無廠商')->first();
            //找該商品是否有包含採購匯入所記錄的供應商 若沒有則新增
            foreach ($productSupplier as $key_ps => $val_ps) {
                $prd_product_supplier = DB::table('prd_product_supplier as pcs_supp')
                    ->where('pcs_supp.product_id', '=', $val_ps->product_id)
                    ->where('pcs_supp.supplier_id', '=', $val_ps->supplier_id)
                    ->first()
                ;
                if (false == isset($prd_product_supplier))
                {
                    //判斷沒有就建立
                    ProductSupplier::firstOrCreate([
                        'product_id' => $val_ps->product_id
                        , 'supplier_id' => $val_ps->supplier_id
                    ]);
                    //移除商品對應的無廠商資料
                    if (true == isset($supplier_wuchangsang)) {
                        ProductSupplier::where('product_id', '=', $val_ps->product_id)->where('supplier_id', '=', $supplier_wuchangsang->id)->delete();
                    }
                }
            }
        }
        echo '採購庫存更新廠商完成';
    }
}
