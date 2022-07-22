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

class ImportNewProcutFromExcelSeeder extends Seeder
{
    /**
     * 匯入產品部「新上架的商品」
     * @return void
     */
    public function run()
    {
        /*
        匯出Cyberbiz的產品Excel(順便加入員工編號）
        使用SKU比對產品部的Excel表格
        整理出需爬蟲的Cyberbiz產品Excel檔案
        爬蟲
        更新HTML的圖檔連結

        -刪除Release、Dev的Json檔案
        -匯入產品(檢查是否曾經上傳過？？ 上傳過就不重新匯入）
         負責人
         若沒有經銷價，就跟售價一致

        上傳圖檔
        */

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

        $allJsonFile = preg_grep("~\.(json)$~",
            scandir(database_path('seeders/Json/')));
        $allJsonFile = array_unique($allJsonFile);

        $totalFileCount = count($allJsonFile);

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
            $strJsonFileContents
                = file_get_contents(database_path('seeders/Json/').$jsonFile);
            $productArray = json_decode($strJsonFileContents, true);

            print_r('(' . ($key-1) . '/'. $totalFileCount . ')執行：' . $productArray['title'] . '-' . $jsonFile);

            //款式是否含有「一般商品」的sku，
            $containProductSku = false;
            foreach ($productArray['variants'] as $variant) {
                if ($variant['sku'][0] === 'P') {
                    $containProductSku = true;
                }
            }
            //員工編號匯入Cyberbiz產品Excel裡面
            $userAccountId = DB::table('usr_users')
                ->whereNotNull('account')
                ->where('account', '=', $productArray['worker_id'])
                ->get()
                ->first();
            if ($userAccountId) {
                $userId = $userAccountId->id;
            }

            //開始建立商品
            if ($containProductSku &&
                !in_array($productArray['id'], $cyberbizIdsArray, true)
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
                    ]);

                if (App::environment([AppEnvClass::Local, AppEnvClass::Release])) {
                    Product::where('id', $productId)
                        ->update(['cyberbiz_id' => $productArray['id']]);
                }

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
