<?php

namespace Database\Seeders;

use App\Enums\Globals\AppEnvClass;
use App\Models\Product;
use App\Models\ProductStyle;
use App\Models\ProductStyleCombo;
use App\Models\SaleChannel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class ImportComboProcutSeeder extends Seeder
{
    /**
     * 匯入組合包
     * @return void
     */
    public function run()
    {
        if (App::environment(AppEnvClass::Release)) {
            $userId = 1;
            $categoryId = 1;
            $supplierId = 1;
            $devStyleSku = '';
        } else {
            $userId = 9;
            $categoryId = 3;
            $supplierId = 2;
            $devStyleSku = '-test-' . strval(time());
        }

        $allJsonFile = preg_grep("~\.(json)$~", scandir(database_path('seeders/Json/')));
        $allJsonFile = array_unique($allJsonFile);
        $totalFileCount = count($allJsonFile);

        /**
        組合包的SKU
        組合包的經銷價
        負責人員工號
        Array
        [
        -單品的SKU
        -單品組合數量
        ]
         */
        $jsonFileContents = file_get_contents(database_path('seeders/') . 'comboProduct.json');
        $jsonData = json_decode($jsonFileContents, true);
        $jsonDataFromErp = $jsonData['product'];

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

            //款式是否含有「組合包」的sku，
            $containProductSku = false;
            foreach ($productArray['variants'] as $variant) {
                if ($variant['sku'][0] === 'C') {
                    $containProductSku = true;
                }
            }

            $styleSkuData = DB::table('prd_product_styles')
                ->whereNull('deleted_at')
                ->select('sku')
                ->get();
            $styleSkuArray = [];
            foreach ($styleSkuData as $styleSkuDatum) {
                $styleSkuArray[] = $styleSkuDatum->sku;
            }

            $containStyleSku = false;
            foreach ($productArray['variants'] as $variant) {
                if (in_array($variant['sku'], $styleSkuArray, true)) {
                    print_r('已經匯入過：' . $productArray['title'] . '-' . $jsonFile . PHP_EOL);
                    $containStyleSku = true;
                }
            }

                //開始建立商品
            if ($containProductSku &&
                !in_array($productArray['id'], $cyberbizIdsArray, true) &&
                !$containStyleSku
            ) {
                print_r('匯入：(' . ($key-1) . '/'. $totalFileCount . ')執行：' . $productArray['title'] . '-' . $jsonFile . PHP_EOL);
                $re = Product::createProduct(
                    $productArray['title'],
                    $userId,
                    $categoryId,
                    'c',
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
                    ]);

                if (App::environment([AppEnvClass::Local, AppEnvClass::Release])) {
                    Product::where('id', $productId)
                        ->update(['cyberbiz_id' => $productArray['id']]);
                }

                //建立主圖圖片路徑
                foreach ($productArray['photo_urls'] as $photoUrl) {
                    preg_match('/.*\\/(product_imgs\\/.*)/', $photoUrl["maximum"], $fullMaxUrl);
                    DB::table('prd_product_images')
                        ->insert([
                            'product_id' => $productId,
                            'url' => $fullMaxUrl[1],
                        ]);
                }

                //開始建立「組合包款式」
                foreach ($productArray['variants'] as $variant) {
                    //只建立「組合包」，不建立「一般商品」
                    if ($variant['sku'][0] === 'C') {
                        //只有單一規格款式，塞入'規格'、'單一款式'
                        if (count($productArray['options']) === 0) {
                            //若沒有款式設定，組合包名稱用「標題」命名
                            $comboStyleId = ProductStyle::createComboStyle($productId, $productArray['title']);
                        } else {
                            //組合包名稱用「款式名稱」
                            $comboStyleId = ProductStyle::createComboStyle($productId, $variant['title']);
                        }

                        // check style sku exists in databases
                        // 組合包裡面的產品，可以在資料庫找到才建立組合包產品
                        // TODO 局部匯入、或是全部匯入?
                        $productStylesExist = false;
                        foreach ($jsonDataFromErp as $jsonDatum) {
                            if ($variant['sku'] == $jsonDatum['sku']) {
                                foreach ($jsonDatum['data'] as $productData) {
                                    if (DB::table('prd_product_styles')
                                        ->where('sku', '=', $productData['sku'])
                                        ->exists()
                                    ) {
                                        $productStylesExist = true;
                                    } else {
                                        $productStylesExist = false;
                                    }
                                }
                            }
                        }
                        if ($productStylesExist) {
                            foreach ($jsonDataFromErp as $jsonDatum) {
                                if ($variant['sku'] == $jsonDatum['sku']) {
                                    if (DB::table('usr_users')
                                        ->whereNotNull('account')
                                        ->where('account', '=', $jsonDatum['worker_id'])
                                        ->exists()) {
                                        $userId = DB::table('usr_users')
                                            ->whereNotNull('account')
                                            ->where('account', '=', $jsonDatum['worker_id'])
                                            ->get()
                                            ->first()
                                            ->id;
                                        Product::where('id', $productId)
                                            ->update([
                                                'user_id' => $userId,
                                            ]);
                                    }
                                    foreach ($jsonDatum['data'] as $productData) {
                                        $styleId = DB::table('prd_product_styles')
                                            ->where('sku', '=', $productData['sku'])
                                            ->get()
                                            ->first()
                                            ->id;
                                        ProductStyleCombo::createCombo($comboStyleId, $styleId, $productData['qty']);
                                        ProductStyle::createSku($productId, $comboStyleId);
                                        // 更新「款式SKU」成「喜多方SKU」
                                        ProductStyle::where([
                                            'id' => $comboStyleId,
                                        ])->update([
                                            'sku' => $variant['sku'] . $devStyleSku
                                        ]);

                                        // 銷售通路價格
                                        SaleChannel::changePrice(1,
                                            $comboStyleId,
                                            ($jsonDatum['dealer_price'] === 0) ? $variant['price'] : $jsonDatum['dealer_price'],
                                            $variant['price'] ?? 0,
                                            $variant['compare_at_price'] ?? 0,
                                            0,
                                            $variant['max_usable_bonus'] ?? 0);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
