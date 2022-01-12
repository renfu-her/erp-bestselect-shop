<?php

namespace Database\Seeders;

use App\Models\SaleChannel;
use Illuminate\Database\Seeder;

class SaleChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SaleChannel::create([
            'title' => '喜鴻官網2.0',
            'contact_person' => '喜鴻窗口',
            'contact_tel' => '0225118885',
            'chargeman' => '喜鴻員工',
            'sales_type' => 0,
            'use_coupon' => 0,
            'is_realtime' => 1,
        ]);

        SaleChannel::create([
            'title' => '員工優惠銷售',
            'contact_person' => '員工窗口',
            'contact_tel' => '0918777777',
            'chargeman' => '銷售負責人',
            'sales_type' => 1,
            'use_coupon' => 1,
        ]);

    }
}
