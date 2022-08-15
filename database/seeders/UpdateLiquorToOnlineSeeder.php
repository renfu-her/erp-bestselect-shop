<?php

namespace Database\Seeders;

use App\Enums\Globals\AppEnvClass;
use App\Models\Collection;
use App\Models\SaleChannel;
use Illuminate\Database\Seeder;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpec;
use App\Models\ProductSpecItem;
use App\Models\ProductStyle;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class UpdateLiquorToOnlineSeeder extends Seeder
{
    /**
     *
     * 酒類群組中的商品，通通改為線上販賣
     * @return void
     */
    public function run()
    {
        Collection::
            where([
                'is_public' => 1,
                'is_liquor' => 1,
            ])
            ->join('collection_prd', 'collection.id', '=', 'collection_prd.collection_id_fk')
            ->join('prd_products', 'collection_prd.product_id_fk', '=', 'prd_products.id')
            ->update([
                'prd_products.online' => 1,
            ]);
    }
}
