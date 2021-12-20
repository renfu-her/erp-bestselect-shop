<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpec;
use App\Models\ProductSpecItem;
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
        //

        ProductSpec::insert([['title' => '尺寸'], ['title' => '容量']]);
        // Category::insert([])

        Category::create((['category' => '食品', 'rank' => 100]));
        Category::create((['category' => '清潔用品', 'rank' => 100]));

        $re = Product::createProduct('測試商品', 1, 1, '測試', null, '好吃商品', null, null, [1, 2], 1);

        Product::setProductSpec($re['id'], 1);
        Product::setProductSpec($re['id'], 1);
        Product::setProductSpec($re['id'], 2);

        ProductSpecItem::createItems($re['id'], 1, 'X');

    }
}
