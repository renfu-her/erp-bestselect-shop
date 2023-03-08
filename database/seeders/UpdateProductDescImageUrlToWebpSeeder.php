<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use simplehtmldom\HtmlDocument;

class UpdateProductDescImageUrlToWebpSeeder extends Seeder
{
    /**
     * 把產品描述圖片格式變成webp
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
                $descHTML = (new HtmlDocument())->load($productDatum->desc);
                $descImages = $descHTML->find('img[src^="https://images-besttour.cdn.hinet.net/"]');
                $shouldConvertToWebp = false;
                foreach ($descImages as $key => $descImage) {
                    $match = preg_match('~^(https://images-besttour.cdn.hinet.net.*/.*.)(jpg|jpeg|png)$~', $descImage->src, $matchSubUrl);
//                    dd($matchSubUrl);
                    if ($match === 1){
                        $shouldConvertToWebp = true;
                        $descImage->src = $matchSubUrl[1] . 'webp';
                    }else {
                        print_r('id:' . ($prdKey + 1) . $productDatum->title);
                    }
                }
                if ($shouldConvertToWebp) {
                    Product::where('id', $productDatum->id)
                            ->update([
                                'desc' => $descHTML,
                            ]);
                }
            }
        }
    }
}
