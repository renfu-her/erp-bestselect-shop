<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $request_newBanner = Request()->instance();
        $request_newBanner->merge([
            'title' => 'title'
            , 'event_type' => 'url'
            //, 'event_id' => ''
            , 'event_url' => 'asdf'
            , 'img_pc' => 'pc'
            , 'img_phone' => 'phone'
            , 'is_public' => '0'
        ]);

       $bannerID1 = Banner::storeNewBanner($request_newBanner);


        $request_updateBanner = Request()->instance();
        $request_updateBanner->merge([
            'title' => 'title_update'
            , 'event_type' => 'url_update'
            //, 'event_id' => ''
            , 'event_url' => 'asdf_update'
            , 'img_pc' => 'pc_update'
            , 'img_phone' => 'phone_update'
            , 'is_public' => '1'
        ]);

        Banner::updateBanner($request_updateBanner, $bannerID1);
    }
}
