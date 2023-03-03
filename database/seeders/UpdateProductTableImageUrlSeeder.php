<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class UpdateProductTableImageUrlSeeder extends Seeder
{
    /**
     * 把產品圖片連結jpg,png,jpeg更新成webp格式
     * @return void
     */
    public function run()
    {
        $productImageData = DB::table('prd_product_images')
            ->select([
                'id',
                'url',
            ])
            ->get();

        foreach ($productImageData as $productImageDatum) {
            $match = preg_match('~^(.*).(jpg|jpeg|png)~', $productImageDatum->url, $matchSubUrl);
            if ($match === 1) {
                print_r('id:' . $productImageDatum->id . '-' . $productImageDatum->url);
                DB::table('prd_product_images')
                    ->where('id', $productImageDatum->id)
                    ->update([
                        'url' => $matchSubUrl[1].'.webp',
                    ]);
            }
        }
    }
}
