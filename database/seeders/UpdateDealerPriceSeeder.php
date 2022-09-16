<?php

namespace Database\Seeders;

use App\Enums\Globals\AppEnvClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class UpdateDealerPriceSeeder extends Seeder
{
    /**
     *
     * 更新經銷價
     * @return void
     */
    public function run()
    {
        $jsonFileContents = file_get_contents(database_path('seeders/') . 'person_in_charge.json');
        $jsonData = json_decode($jsonFileContents, true);
        $data = $jsonData;//['data'];

        $skuData = DB::table('prd_products')
            ->leftJoin('prd_product_styles', 'prd_products.id', '=', 'prd_product_styles.product_id')
            ->whereNotNull('prd_product_styles.sku')
            ->select(
                'prd_product_styles.sku as styleSku',
                'prd_product_styles.id as style_id',
                'prd_products.id as productId',
                'prd_products.sku as productSku',
//                'prd_products.user_id',
                'prd_products.title',
            )
            ->get();

        $skuDataSet = [];
        foreach ($skuData as $skuDatum) {
            $skuDataSet[] = [
                'styleSku' => $skuDatum->styleSku,
                'styleId' => $skuDatum->style_id,
                'productId' => $skuDatum->productId,
                'sku' => $skuDatum->productSku,
//                'user_id' => $skuDatum->user_id,
                'title' => $skuDatum->title,
            ];
        }

        foreach ($skuDataSet as $skuDatabase) {
            $skuKey = array_search($skuDatabase['styleSku'] , array_column($data, 'sku'), true);
            if ($skuKey) {
                $dealerPrice = $data[$skuKey]['price2'];
                if ($dealerPrice !== 0) {
                    for ($i = 1; $i <= 6; $i++) {
                        if (DB::table('prd_salechannel_style_price')
                            ->where([
                                ['style_id', '=', $skuDatabase['styleId']],
                                ['sale_channel_id', '=', $i],
                            ])->exists()
                        ) {
                            DB::table('prd_salechannel_style_price')
                                ->where([
                                    ['style_id', '=', $skuDatabase['styleId']],
                                    ['sale_channel_id', '=', $i],
                                ])
                                ->update(['dealer_price' => $dealerPrice]);
                        }
                    }
                }
            } else {
                print_r('找不到：' . $skuDatabase['styleSku'] . PHP_EOL);
            }
        }
    }
}
