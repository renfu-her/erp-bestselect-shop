<?php

namespace Database\Seeders;

use App\Models\SaleChannel;
use Illuminate\Database\Seeder;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpec;
use App\Models\ProductSpecItem;
use App\Models\ProductStyle;
use App\Models\ProductStyleCombo;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use League\Flysystem\Config;

class CyberbizImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $allJsonFile = preg_grep("~\.(json)$~",
            scandir(database_path('seeders/Json/')));

        $totalFileCount = count($allJsonFile);
        foreach ($allJsonFile as $key => $jsonFile) {
            $strJsonFileContents
                = file_get_contents(database_path('seeders/Json/').$jsonFile);
            $productArray = json_decode($strJsonFileContents, true);

            print_r('(' . ($key-1) . '/'. $totalFileCount . ')執行：' . $productArray['title']);

            $re = Product::createProduct(
                $productArray['title'],
                1,
                1,
                'p',
                $productArray['brief'],
                explode('/', $productArray['url'])[2],
                $productArray['slogan'],
                $productArray['sell_from'],
                explode(' ', $productArray['sell_to'])[0],
                [1]);
            $productId = $re['id'];

            //把商品的SKU從購物網2.0預設改成Cyberbiz的product_id
            Product::where('id', $productId)
                ->update(['sku' => $productArray['id']]);

            foreach ($productArray['options'] as $specIndex => $specName) {
                $specId = ProductSpec::where('title', $specName)->get()
                    ->first()->id;

                $specType = $productArray['option_types'][$specIndex];
                ProductSpecItem::createItems($productId, $specId,
                    explode(",", $specType));
            }

            $specCount = count($productArray['options']);
            foreach ($productArray['options'] as $specIndex => $specName) {
                $specId = ProductSpec::where('title', $specName)->get()
                    ->first()->id;
                Product::setProductSpec($productId, $specId);
            }

            foreach ($productArray['variants'] as $variant) {
                $optionArray = array();
                for ($index = 1; $index <= $specCount; $index++) {
                    $optionArray[] = $variant['option'.$index];
                }
                $item_ids = DB::table('prd_spec_items')
                    ->where(['product_id' => $productId,])
                    ->whereIn('title', $optionArray)
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
                ])->update(['sku' => $variant['sku']]);

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
                    $variant['price'] ?? 0,
                    $variant['compare_at_price'] ?? 0,
                    0,
                    $variant['max_usable_bonus'] ?? 0
                );
            }
        }
    }
}
