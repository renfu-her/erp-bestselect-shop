<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CyberbizUpdateDataSeeder extends Seeder
{
    const CYBERBIZ_SKU = 0;
    const TITLE = 1;
    const CYBERBIZ_ID = 2;

    /**
     * 匯入缺少的Cyberbiz_id
     *
     * @return void
     */
    public function run()
    {
        $jsonFileContents = file_get_contents(database_path('seeders/') . 'imported_data.json');
        $jsonData = json_decode($jsonFileContents, true);

        foreach ($jsonData['data'] as $productData) {
            DB::table('prd_products')
                ->where('sku', '=', $productData[self::CYBERBIZ_SKU])
                ->update(['cyberbiz_id' => $productData[self::CYBERBIZ_ID]]);
        }
    }
}
