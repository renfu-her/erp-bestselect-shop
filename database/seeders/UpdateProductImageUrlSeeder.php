<?php

namespace Database\Seeders;

use App\Enums\Globals\ImageDomain;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use simplehtmldom\HtmlDocument;


class UpdateProductImageUrlSeeder extends Seeder
{
    /**
     * 把產品圖片連結更新成CDN連結
     * @return void
     */
    public function run()
    {
        $productData = DB::table('prd_products')
            ->select([
                'id',
                'title',
                'desc',
            ])
            ->get();

        foreach ($productData as $prdKey => $productDatum) {
            if ($productDatum->desc){
                print_r('id:' . ($prdKey + 1));
                $descHTML = (new HtmlDocument())->load($productDatum->desc);
                $descImages = $descHTML->find('img[src^="https://img.bestselection.com.tw/"]');
                foreach ($descImages as $key => $descImage) {
                    preg_match('~^https://img.bestselection.com.tw/(.*)~', $descImage->src, $matchSubUrl);
                    $descImage->src = ImageDomain::CDN . $matchSubUrl[1];
                }
                Product::where('id', $productDatum->id)
                        ->update([
                            'desc' => $descHTML,
                        ]);
            }
        }
    }
}
