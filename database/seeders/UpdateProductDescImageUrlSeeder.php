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
     * 把產品描述的圖片連結從images-besttour.cdn.hinet.net改成edgio-cdn-pix.besttour.com.tw
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
                $descImages = $descHTML->find('img[src^="https://images-besttour.cdn.hinet.net/"]');
                foreach ($descImages as $key => $descImage) {
                    preg_match('~^https://images-besttour.cdn.hinet.net/(.*)~', $descImage->src, $matchSubUrl);
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
