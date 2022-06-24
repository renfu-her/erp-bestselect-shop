<?php

namespace Database\Seeders;

use App\Enums\Globals\AppEnvClass;
use App\Models\SaleChannel;
use Illuminate\Database\Seeder;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpec;
use App\Models\ProductSpecItem;
use App\Models\ProductStyle;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class LiquorImportSeeder extends Seeder
{
    /**
     *
     * 酒類商品匯入
     * @return void
     */
    public function run()
    {
        if (App::environment(AppEnvClass::Release)) {
            $userId = 1;
            $categoryId = 2;
            $supplierId = 1;
            $devStyleSku = '';

        } elseif (App::environment(AppEnvClass::Development)) {
            $userId = 9;
            $categoryId = 4;
            $supplierId = 2;
            $devStyleSku = '-test-'.strval(time());
        } else {
            $userId = 9;
            $categoryId = 3;
            $supplierId = 2;
            $devStyleSku = '';
//            $devStyleSku = '-test-'.strval(time());
        }

        $allJsonFile = preg_grep("~\.(json)$~",
            scandir(database_path('seeders/Json/liquor')));
        $allJsonFile = array_unique($allJsonFile);

        $totalFileCount = count($allJsonFile);
        foreach ($allJsonFile as $key => $jsonFile) {
            $strJsonFileContents
                = file_get_contents(database_path('seeders/Json/liquor/')
                .$jsonFile);
            $productArray = json_decode($strJsonFileContents, true);

            print_r('('.($key - 1).'/'.$totalFileCount.')執行：'
                .$productArray['prd_title'].'-'.$jsonFile);

            //款式是否含有「一般商品」的sku，
            $containProductSku = false;
            if ($productArray['sku'][0] === 'P') {
                $containProductSku = true;
            }

            if (Category::where('category', $productArray['category'])->get()->first()) {
                $categoryId = Category::where('category', $productArray['category'])
                    ->get()
                    ->first()
                    ->id;
            }
            //開始建立商品
            if ($containProductSku) {
                $re = Product::createProduct(
                    $productArray['prd_title'],
                    $userId,
                    $categoryId,
                    'p',
                    null,
                    $productArray['url'],
                    null,
                    null,
                    null,
                    [$supplierId],
                    1,
                    0,
                    1,
                    0,
                    1,
                );
                $productId = $re['id'];

                Product::where('id', $productId)
                    ->update([
                        'spec_locked' => 1,
                        'desc'        => $productArray['prd_html_editor'],
                    ]);

                //建立主圖圖片路徑
                foreach ($productArray['image_url_list'] as $photoUrl) {
                    preg_match('/.*\\/(product_imgs\\/.*)/', $photoUrl,
                        $fullMaxUrl);
                    DB::table('prd_product_images')
                        ->insert([
                            'product_id' => $productId,
                            'url'        => $fullMaxUrl[1],
                        ]);
                }

                //開始建立「款式」商品
                //只有單一規格款式，塞入'規格'、'單一款式'
                $optionArray = [$productArray['spec']];
                $specId = ProductSpec::where('title', '品項')
                    ->get()
                    ->first()
                    ->id;
                Product::setProductSpec($productId, $specId);
                ProductSpecItem::createItems($productId, $specId,
                    $productArray['spec']);

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
                    'sku' => $productArray['sku'].$devStyleSku
                ]);

                //更新產品負責人
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

                $personInChargeFileContent = file_get_contents(database_path('seeders/') . 'person_in_charge.json');
                $jsonData = json_decode($personInChargeFileContent, true);
                $data = $jsonData['data'];
                //紅利
                $bonus = 0;
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
                        // TODO 獎金先不用匯入
//                        $bonus = round($data[$skuKey]['bonus']);
                    }
                }

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
                    0,
                    $productArray['price'] ?? 0,
                    0,
                    $bonus,
                    0,
                );
                SaleChannel::addPriceForStyle($styleId);
            }
        }
    }
}
