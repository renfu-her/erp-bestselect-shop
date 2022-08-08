<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductSpec;
use App\Models\ProductSpecItem;
use App\Models\ProductStyle;
use App\Models\SaleChannel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportComboSingleProduct extends Seeder
{
    /**
     * 匯入「單品+組合包」產品中的單品
     * @return void
     */
    public function run()
    {
        $userId = 1;
        $categoryId = 1;
        $supplierId = 1;
        $devStyleSku = '';

        $allJsonFile = preg_grep("~\.(json)$~", scandir(database_path('seeders/Json/')));
        $allJsonFile = array_unique($allJsonFile);

        $totalFileCount = count($allJsonFile);

        $personInChargeJson = file_get_contents(database_path('seeders/') . 'person_in_charge.json');
        $personInChareJsonData = json_decode($personInChargeJson, true);

        //-匯入產品(檢查否曾經上傳過？？ 上傳過就不重新匯入）
        $cyberbizIds = DB::table('prd_products')
                        ->whereNotNull('cyberbiz_id')
                        ->select('cyberbiz_id')
                        ->get();
        $cyberbizIdsArray = [];
        foreach ($cyberbizIds as $cyberbizId) {
            $cyberbizIdsArray[] = $cyberbizId->cyberbiz_id;
        }
        foreach ($allJsonFile as $key => $jsonFile) {
            $strJsonFileContents = file_get_contents(database_path('seeders/Json/').$jsonFile);
            $productArray = json_decode($strJsonFileContents, true);

            //員工編號匯入Cyberbiz產品Excel裡面
            if (
                DB::table('usr_users')
                    ->whereNotNull('account')
                    ->where('account', '=', $productArray['worker_id'])
                    ->exists()
            ) {
                $userAccountId = DB::table('usr_users')
                    ->whereNotNull('account')
                    ->where('account', '=', $productArray['worker_id'])
                    ->get()
                    ->first();
                $userId = $userAccountId->id;
            }

            $styleSkuData = DB::table('prd_product_styles')
                ->whereNull('deleted_at')
                ->select('sku')
                ->get();
            $styleSkuArray = [];
            foreach ($styleSkuData as $styleSkuDatum) {
                $styleSkuArray[] = $styleSkuDatum->sku;
            }

            if (in_array(strval($productArray['id']), $cyberbizIdsArray, true)) {
                print_r('Cyberbiz ID已經重複匯入過：' . $productArray['id'] . PHP_EOL);
            }
            $containStyleSku = false;
            foreach ($productArray['variants'] as $variant) {
                if (in_array($variant['sku'], $styleSkuArray, true)) {
                    $containStyleSku = true;
                    print_r('款式SKU已經重複匯入過：' . $variant['sku'] . PHP_EOL);
                }
            }

            //開始建立商品
            if (!in_array($productArray['id'], $cyberbizIdsArray, true) &&
                !$containStyleSku
            ) {
                $re = Product::createProduct(
                    $productArray['title'],
                    $userId,
                    $categoryId,
                    'p',
                    $productArray['brief'],
                    explode('/', $productArray['url'])[2],
                    $productArray['slogan'],
                    $productArray['sell_from'],
                    explode(' ', $productArray['sell_to'])[0],
                    [$supplierId],
                    1,
                    0,
                    1,
                    1,
                    1,
                );
                $productId = $re['id'];

                Product::where('id', $productId)
                    ->update([
                        'spec_locked' => 1,
                        'desc' => $productArray['body_html'],
                        'cyberbiz_id' => $productArray['id'],
                    ]);

                $specCount = count($productArray['options']);

                //建立主圖圖片路徑
                foreach ($productArray['photo_urls'] as $photoUrl) {
                    preg_match('/.*\\/(product_imgs\\/.*)/', $photoUrl["maximum"], $fullMaxUrl);
                    DB::table('prd_product_images')
                        ->insert([
                            'product_id' => $productId,
                            'url' => $fullMaxUrl[1],
                        ]);
                }

                //開始建立「款式」商品
                foreach ($productArray['variants'] as $variant) {
                    //只建立「一般商品」，不建立「組合包」
                    if ($variant['sku'][0] === 'P') {
                        print_r('(' . ($key-1) . '/'. $totalFileCount . ')執行：' . $variant['sku'] . '-' . $jsonFile . PHP_EOL);

                        $optionArray = array();
                        //只有單一規格款式，塞入'規格'、'單一款式'
                        if (count($productArray['options']) === 0) {
                            $optionArray = ['單一款式'];
                            $specId = ProductSpec::where('title', '規格')->get()
                                ->first()->id;
                            Product::setProductSpec($productId, $specId);
                            ProductSpecItem::createItems($productId, $specId, '單一款式');
                        } elseif (count($productArray['options']) === 1) {
                            $optionArray = [$variant['option1']];
                            foreach ($productArray['options'] as $specIndex => $specName) {
                                $specId = ProductSpec::where('title', $specName)
                                    ->get()
                                    ->first()
                                    ->id;
                                Product::setProductSpec($productId, $specId);
                                ProductSpecItem::createItems($productId, $specId, $optionArray);
                            }
                        } else {
                            for ($index = 1; $index <= $specCount; $index++) {
                                $optionArray[] = $variant['option' . $index];
                            }
                            foreach ($productArray['options'] as $specIndex => $specName) {
                                $specId = ProductSpec::where('title', $specName)
                                    ->get()
                                    ->first()->id;
                                Product::setProductSpec($productId, $specId);
                                if (!ProductSpecItem::where([
                                    'product_id' => $productId,
                                    'spec_id' => $specId,
                                    'title' => $optionArray[$specIndex]
                                ])->get()->first()) {
                                    ProductSpecItem::createItems($productId, $specId, $optionArray[$specIndex]);
                                }
                            }
                        }

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
                            'sku' => $variant['sku'] . $devStyleSku
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
                        $key = array_search($variant['sku'], array_column($personInChareJsonData['data'], 'sku'), true);
                        if ($key) {
                            $variant['dealer_price'] = $personInChareJsonData['data'][$key]['price2'];
                        }
                        SaleChannel::changePrice(
                            1,
                            $styleId,
                            ($variant['dealer_price'] === 0) ? $variant['price'] : $variant['dealer_price'],
                            $variant['price'] ?? 0,
                            $variant['compare_at_price'] ?? 0,
                            0,
                            $variant['max_usable_bonus'] ?? 0
                        );
                        SaleChannel::addPriceForStyle($styleId);
                    }
                }
            }
        }
    }
}
