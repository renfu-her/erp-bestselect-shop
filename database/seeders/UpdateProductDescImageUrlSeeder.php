<?php

namespace Database\Seeders;

use App\Enums\Globals\ImageDomain;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use simplehtmldom\HtmlDocument;

class UpdateProductDescImageUrlSeeder extends Seeder
{
    /**
     * 把產品描述的圖片連結從CDN連結更新成使用FTP（202.168.206.100)img.bestselection.com.tw
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
                $descImages = $descHTML->find('img[src^="https://besttour-img.ittms.com.tw/"]');
                foreach ($descImages as $key => $descImage) {
                    preg_match('~^https://besttour-img.ittms.com.tw/(.*)~', $descImage->src, $matchSubUrl);
                    $descImage->src = ImageDomain::FTP . $matchSubUrl[1];
                }
                Product::where('id', $productDatum->id)
                        ->update([
                            'desc' => $descHTML,
                        ]);
            }
        }
    }
}
