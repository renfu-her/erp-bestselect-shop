<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpec;
use App\Models\ProductSpecItem;
use App\Models\ProductStock;
use App\Models\ProductStyle;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        ProductSpec::insert([['title' => '尺寸'], ['title' => '容量'], ['title' => '顏色']]);

        Category::create((['category' => '食品', 'rank' => 100]));
        Category::create((['category' => '清潔用品', 'rank' => 100]));

        $re = Product::createProduct('測試商品', 1, 1, '測試', null, '好吃商品', null, null, [1, 2], 1);

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

        ProductStyle::createStyle($re['id'], [4, 1]);
        ProductStyle::createStyle($re['id'], [2, 5]);

        ProductStyle::createSku($re['id'], 2);
        
        ProductStock::stockChange(1, 10, 'in_stock');
        ProductStock::stockChange(1, -5, 'order');

    }
}
