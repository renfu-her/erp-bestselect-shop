<?php

namespace Database\Seeders;

use App\Models\SaleChannel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaleChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $id = SaleChannel::create([
            'title' => '喜鴻購物2.0官網',
            'contact_person' => '喜鴻窗口',
            'contact_tel' => '0225118885',
            'chargeman' => '喜鴻員工',
            'sales_type' => 1,
            'use_coupon' => 1,
            'is_realtime' => 1,
            'code' => '01',
            'is_master' => 1,
            'dividend_rate' => 90,
        ])->id;

        $identity = DB::table('usr_identity')->where('code', 'customer')->get()->first();
        DB::table('usr_identity_salechannel')->insert(['identity_id' => $identity->id, 'sale_channel_id' => $id]);

        $id = SaleChannel::create([
            'title' => '喜鴻購物2.0ERP',
            'contact_person' => '員工窗口',
            'contact_tel' => '0918777777',
            'chargeman' => '銷售負責人',
            'sales_type' => 0,
            'use_coupon' => 1,
            'discount' => 1,
            'is_realtime' => 1,
            'code' => '02',
        ])->id;

        $identity = DB::table('usr_identity')->where('code', 'employee')->get()->first();
        DB::table('usr_identity_salechannel')->insert(['identity_id' => $identity->id, 'sale_channel_id' => $id]);

        $id = SaleChannel::create([
            'title' => '喜鴻購物企業網',
            'contact_person' => '員工窗口',
            'contact_tel' => '0918777777',
            'chargeman' => '銷售負責人',
            'sales_type' => 1,
            'use_coupon' => 1,
            'discount' => 0.95,
            'is_realtime' => 1,
            'code' => '03',
        ])->id;

        $identity = DB::table('usr_identity')->where('code', 'company')->get()->first();
        DB::table('usr_identity_salechannel')->insert(['identity_id' => $identity->id, 'sale_channel_id' => $id]);

        SaleChannel::create([
            'title' => '蝦皮',
            'contact_person' => '員工窗口',
            'contact_tel' => '0918777777',
            'chargeman' => '銷售負責人',
            'sales_type' => 1,
            'use_coupon' => 0,
            'discount' => 1,
            'is_realtime' => 0,
            'code' => '04',
        ]);

        SaleChannel::create([
            'title' => '郵政平台',
            'contact_person' => '員工窗口',
            'contact_tel' => '0918777777',
            'chargeman' => '銷售負責人',
            'sales_type' => 1,
            'use_coupon' => 0,
            'discount' => 1,
            'is_realtime' => 0,
            'code' => '05',
        ]);

        SaleChannel::changePrice(1, 1, 90, 120, 130, 5, 0);
        SaleChannel::changePrice(1, 4, 180, 230, 250, 5, 0);
        SaleChannel::changePrice(1, 5, 185, 235, 255, 5, 0);

        User::customerBinding(6, 'hsihung08079@gmail.com');
        User::customerBinding(1, 'hayashi0126@gmail.com');

    }
}
