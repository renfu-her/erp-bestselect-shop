<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductSpecItem;
use App\Models\ProductSpec;
use App\Models\Category;
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

        $re = Product::createProduct('測試商品', 1, 1, '測試', null, '好吃商品', null, null, [1, 2], 1);

        Product::setProductSpec($re['id'], [1, 1, 2]);


        ProductSpecItem::createItems($re['id'],1,'X');


    }
}
