<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpec;
use App\Models\ProductSpecItem;
use App\Models\ProductStyle;
use App\Models\ProductStyleCombo;
use App\Models\ProductStock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ProductSpec::insert([
            ['title' => '尺寸'],
            ['title' => '容量'],
            ['title' => '顏色'],
            ['title' => '品項'],
            ['title' => '規格'],
        ]);

        Category::create((['category' => '喜鴻', 'rank' => 100]));
        if (App::environment(['dev'])) {
            Category::create((['category' => '清潔用品', 'rank' => 100]));
        } else {
            Category::create((['category' => '無類別', 'rank' => 100]));
        }

        $re = Product::createProduct('測試商品', 1, 1, 'p', '測試', null, '好吃商品', null, null, [1, 2], 1);

        Product::setProductSpec($re['id'], 1);

        ProductSpecItem::createItems($re['id'], 1, 'X');
        ProductSpecItem::createItems($re['id'], 1, 'M');
        ProductSpecItem::createItems($re['id'], 1, 'L');
        ProductSpecItem::createItems($re['id'], 2, '10ml');
        ProductSpecItem::createItems($re['id'], 2, '15ml');
        ProductSpecItem::createItems($re['id'], 2, '20ml');
        ProductSpecItem::createItems($re['id'], 3, '黃');
        ProductSpecItem::createItems($re['id'], 3, '綠');
        ProductSpecItem::createItems($re['id'], 3, '紅');

        $style_id1 = ProductStyle::createStyle($re['id'], [4, 1]);
        $style_id2 = ProductStyle::createStyle($re['id'], [2, 5]);
        $style_id3 = ProductStyle::createStyle($re['id'], [3, 6]);

        ProductStyle::createSku($re['id'], $style_id1);
        ProductStyle::createSku($re['id'], $style_id2);
        ProductStyle::createSku($re['id'], $style_id3);

        // ProductStock::stockChange(1, 10, 'purchase');

        $re = Product::createProduct('組合包商品', 1, 1, 'c', '組合', null, '組合商品', null, null, null, 1);
        $id = ProductStyle::createComboStyle($re['id'], '三包組', 1);
        ProductStyleCombo::createCombo($id, $style_id1, 2);
        ProductStyleCombo::createCombo($id, $style_id2, 1);

        $id_2 = ProductStyle::createComboStyle($re['id'], '六包組', 1);
        ProductStyleCombo::createCombo($id_2, $style_id1, 3);
        ProductStyleCombo::createCombo($id_2, $style_id3, 3);
        ProductStyle::createSku($re['id'], $id);
        ProductStyle::createSku($re['id'], $id_2);


      // dd(ProductStock::comboProcess(3,-130));

        //新增:茶葉金禮盒
        $re1 = Product::createProduct('茶葉金禮盒', 6, 1, 'p', '茶湯色澤迷人濃郁，入口清香、後韻回甘', null, '純正台灣茶葉', null, null, [2], 1);
        Product::setProductSpec($re1['id'], 3);
        ProductSpecItem::createItems($re1['id'], 3, '藍');
        ProductSpecItem::createItems($re1['id'], 3, '橙');
        ProductSpecItem::createItems($re1['id'], 3, '黑');
        $styleId_1 = ProductStyle::createStyle($re1['id'], [10]);
        $styleId_2 = ProductStyle::createStyle($re1['id'], [11]);
        $styleId_3 = ProductStyle::createStyle($re1['id'], [12]);
        ProductStyle::createSku($re1['id'], $styleId_1);
        ProductStyle::createSku($re1['id'], $styleId_2);
        ProductStyle::createSku($re1['id'], $styleId_3);

    }
}
