<?php

namespace Database\Seeders;

use App\Enums\Globals\AppEnvClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class CyberbizUpdateDataSeeder extends Seeder
{
//    const CYBERBIZ_SKU = 0;
//    const TITLE = 1;
//    const CYBERBIZ_ID = 2;

    /**
     *
     *
     * @return void
     */
    public function run()
    {
        //匯入缺少的Cyberbiz_id
//        $jsonFileContents = file_get_contents(database_path('seeders/') . 'imported_data.json');
//        $jsonData = json_decode($jsonFileContents, true);
//
//        foreach ($jsonData['data'] as $productData) {
//            DB::table('prd_products')
//                ->where('sku', '=', $productData[self::CYBERBIZ_SKU])
//                ->update(['cyberbiz_id' => $productData[self::CYBERBIZ_ID]]);
//        }

        //更新產品負責人
        $jsonFileContents = file_get_contents(database_path('seeders/') . 'person_in_charge.json');
        $jsonData = json_decode($jsonFileContents, true);
        $data = $jsonData['data'];

        if (App::environment(AppEnvClass::Release)) {
            $userId = 1;
        } else {
            $userId = 9;
        }
        $skuData = DB::table('prd_products')
            ->where('user_id', '=', $userId)
            ->leftJoin('prd_product_styles', 'prd_products.id', '=', 'prd_product_styles.product_id')
            ->whereNotNull('prd_product_styles.sku')
            ->select(
                'prd_product_styles.sku as styleSku',
                'prd_products.id as productId',
                'prd_products.sku as productSku',
                'prd_products.user_id',
                'prd_products.title',
            )
            ->get();

        $skuDataSet = [];
        foreach ($skuData as $skuDatum) {
            $skuDataSet[] = [
                'styleSku' => $skuDatum->styleSku,
                'productId' => $skuDatum->productId,
                'sku' => $skuDatum->productSku,
                'user_id' => $skuDatum->user_id,
                'title' => $skuDatum->title,
            ];
        }

        foreach ($skuDataSet as $skuDatabase) {
            $skuKey = array_search($skuDatabase['styleSku'] , array_column($data, 'sku'), true);
            if ($skuKey) {
                $personInChargeId = $data[$skuKey]['ID'];
                $userAccount = DB::table('usr_users')
                    ->where('account', '=', $personInChargeId)
                    ->whereNotNull('account')
                    ->get()
                    ->first();

                if ($userAccount) {
                    print_r($skuDatabase['productId']. ':' . $skuDatabase['title'] . PHP_EOL);
                    DB::table('prd_products')
                        ->where('id', '=', $skuDatabase['productId'])
                        ->update(['user_id' => $userAccount->id]);
                }
            }
        }
    }
}
