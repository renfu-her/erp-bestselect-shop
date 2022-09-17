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

        self::copyPriceToDealerPrice();
    }

    /**
     * 如果經銷價為0，複製售價到經銷價
     * @return void
     */
    public static function copyPriceToDealerPrice()
    {
        $productPriceData = DB::table('prd_products')
            ->leftJoin('prd_product_styles', 'prd_product_styles.product_id', '=', 'prd_products.id')
            ->join('prd_salechannel_style_price', function ($join) {
                $join->on('prd_product_styles.id', '=', 'prd_salechannel_style_price.style_id')
                    ->where('prd_salechannel_style_price.dealer_price', '=', 0)
                    ->where('prd_salechannel_style_price.price', '<>', 0);
            })
            ->select([
                'prd_product_styles.product_id',
                'prd_product_styles.sku as style_sku',
                'prd_salechannel_style_price.price',
                'prd_salechannel_style_price.style_id',
                'prd_salechannel_style_price.sale_channel_id',
            ])
            ->get();

        $productPriceDataArray = [];
        foreach ($productPriceData as $productPriceDatum) {
            $productPriceDataArray[] = [
                'product_id' => $productPriceDatum->product_id,
                'style_sku' => $productPriceDatum->style_sku,
                'price' => $productPriceDatum->price,
                'style_id' => $productPriceDatum->style_id,
                'sale_channel_id' => $productPriceDatum->sale_channel_id,
            ];
        }

        foreach ($productPriceDataArray as $item) {
            if (DB::table('prd_salechannel_style_price')
                ->where([
                    ['style_id', '=', $item['style_id']],
                    ['sale_channel_id', '=', $item['sale_channel_id']],
                ])->exists()
            ) {
                DB::table('prd_salechannel_style_price')
                    ->where([
                        ['style_id', '=', $item['style_id']],
                        ['sale_channel_id', '=', $item['sale_channel_id']],
                    ])
                    ->update(['dealer_price' => $item['price']]);
            }
        }
    }
}
