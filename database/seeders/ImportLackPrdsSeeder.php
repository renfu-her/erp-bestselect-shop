<?php

namespace Database\Seeders;

use App\Enums\Globals\AppEnvClass;
use App\Models\Product;
use App\Models\ProductSpec;
use App\Models\ProductSpecItem;
use App\Models\ProductStyle;
use App\Models\SaleChannel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class ImportLackPrdsSeeder extends Seeder
{
    /**
     * 從小姜資料，來建立缺少的單品
     * @return void
     */
    public function run()
    {
        if (App::environment([
            AppEnvClass::Local,
            AppEnvClass::Development,
            AppEnvClass::Release,
        ])) {
            $userId = 1;
            $categoryId = 1;
            $supplierId = 1;
            $devStyleSku = '';
        }

        $lackSkuData = [
            "P201005002-42","P201005003-42","P201015001-42","P201016002-42","P201208004-42","P201203014-42","P201208006-42","P201208009-42","P201208010-42","P201208008-42","P201127001-209","P201203007-42","P201203008-42","P201203009-42","P201215008-209","P210225009-42","P210302016-42","P210302017-42","P210302018-42","P210308008-42","P210610023-42","P210315016-42","P210325007-42","P210105003-42","P210106001-42","P210329038-42","P210114001-50","P210401005-42","P210414007-42","P210414008-42","P210414009-42","P210414010-42","P210426003-42","P210426004-42","P210426002-42","P210426017-42","P210426018-42","P200929064-103","P201118004-50","P210519032-42","P210531002-42","P210531003-42","P210531004-42","P210531005-42","P210531006-42","P210531007-42","P210531011-42","P210531012-42","P210531013-42","P210531014-42","P210531015-42","P200922010-42","P200922008-42","P200922007-42","P201006001-42","P201006002-42","P201019011-42","P210223003-42","P210308012-42","P210308013-42","P210308014-42","P210308015-42","P210606006-42","P210316004-42","P210421006-42","P210304041-42","P210809044-42","P210809045-42","P210722001-612","P210722001-614","P210722001-613","P210819007-286","P210819006-286","P210819008-286","P210819010-286","P211014001-42","P211025001-42","P211025004-42","P211025005-42","P211025009-42","P211101012-42","P211102003-42","P211102005-42","P210330034-42","P210330033-42","P211019007-42","P220211003-42","P220208018-42","P220208017-42","P220214028-42","P220311020-42","P210507011-42","P220317032-42","P210618003-42","P220324032-42","P220324034-42","P210618004-42","P220324031-42","P210618005-42","P220310016-42","P220511011-42","P220511013-42","P220511015-42","P220511017-42","P220720001-42","P220720004-42","P220728001-42","P220311022-42","P211013007-42","P220729006-42","P210225021-42","P200929059-91","P210518025-42","P210330024-320","P210330032-144","P201216012-42","P210506026-42","P210531001-42","P200929017-50","P210422017-42","P210506010-42","P210426020-42","P210304022-50","P210317042-42","P210506013-42","P210326006-42","P210325015-42","P210426014-42","P210706014-42","P210225028-42","P210419009-369","P210419013-369","P210104024-42","P201017003-50","P210609014-42","P201113003-42","P201029011-83","P210609010-42","P210609012-42"
            ,
            // Cyberbiz產品設定公開，但是有單品+組合包款式
            "P210226001-42","P210308004-42","P210317045-42","P210518032-42","P200929026-50","P210506015-42","P210415043-50","P210415040-50","P210503001-42","P210630011-42","P210611004-42","P210503014-42","P210709002-42","P210709001-42","P210803007-42"
            ,
        ];

        $jsonFileContents = file_get_contents(database_path('seeders/') . 'person_in_charge.json');
        $jsonData = json_decode($jsonFileContents, true);
        /*
         *
        {
            "ID": "03183",
            "sku": "P200924012-42",
            "cost": 200.00,
            "name": "【台南東山】土窯烘焙龍眼乾500g",
            "bonus": 29.10,
            "price": 280,
            "price2": 250
        }
         */
        foreach ($lackSkuData as $lackSkuDatum) {
            $key = array_search($lackSkuDatum, array_column($jsonData['data'], 'sku'), true);
            if ($key) {
                $productStyleSkuDoesntExist = DB::table('prd_product_styles')->where('sku', $lackSkuDatum)->doesntExist();
                if ($productStyleSkuDoesntExist) {
                    if (DB::table('usr_users')
                        ->whereNotNull('account')
                        ->where('account', '=', $jsonData['data'][$key]['ID'])
                        ->exists()) {
                        $userId = DB::table('usr_users')
                            ->whereNotNull('account')
                            ->where('account', '=', $jsonData['data'][$key]['ID'])
                            ->get()
                            ->first()
                            ->id;
                    }

                    print_r('匯入:' . $jsonData['data'][$key]['name'] . ':' . $lackSkuDatum . PHP_EOL);
                    $re = Product::createProduct(
                        $jsonData['data'][$key]['name'],
                        $userId,
                        $categoryId,
                        'p',
                        null,
                        null,
                        null,
                        null,
                        null,
                        [$supplierId],
                        1,
                        0,
                        0,
                        0,
                        0,
                    );
                    $productId = $re['id'];

                    Product::where('id', $productId)
                            ->update([
                                'spec_locked' => 1,
                            ]);
                    $optionArray = ['單一款式'];
                    $specId = ProductSpec::where('title', '規格')->get()->first()->id;
                    Product::setProductSpec($productId, $specId);
                    ProductSpecItem::createItems($productId, $specId, '單一款式');

                    $item_ids = DB::table('prd_spec_items')
                        ->where(['product_id' => $productId,])
                        ->whereIn('title', $optionArray)
                        ->orderBy('spec_id', 'ASC')
                        ->select('id')
                        ->get()
                        ->toArray();

                    $itemArray = array();
                    foreach ($item_ids as $data) {
                        $itemArray[] = $data->id;
                    }
                    ProductStyle::createStyle($productId, $itemArray);

                    // 更新「款式SKU」成「喜多方SKU」
                    ProductStyle::where([
                        'product_id'    => $productId,
                        'spec_item1_id' => $itemArray[0] ?? null,
                        'spec_item2_id' => $itemArray[1] ?? null,
                        'spec_item3_id' => $itemArray[2] ?? null,
                    ])->update([
                        'sku' => $lackSkuDatum,
                    ]);

                    // 銷售通路價格
                    $styleId = ProductStyle::where([
                        'product_id'    => $productId,
                        'spec_item1_id' => $itemArray[0] ?? null,
                        'spec_item2_id' => $itemArray[1] ?? null,
                        'spec_item3_id' => $itemArray[2] ?? null,
                    ])->get()
                        ->first()
                        ->id;
                    SaleChannel::changePrice(
                        1,
                        $styleId,
                        100000,
                        100000,
                        100000,
                        0,
                        0,
                    );
                    SaleChannel::addPriceForStyle($styleId);
                }
            }
        }
    }
}
