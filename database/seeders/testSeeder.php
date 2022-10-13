<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserSalechannel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class testSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $salesId = DB::table('prd_sale_channels')
            ->where('prd_sale_channels.title', '喜鴻購物2.0官網')
            ->select('id')
            ->get()
            ->first()
            ->id;
        $erpSalesId = DB::table('prd_sale_channels')
            ->where('prd_sale_channels.title', '喜鴻購物2.0ERP')
            ->select('id')
            ->get()
            ->first()
            ->id;
        $noBonusSalesId = DB::table('prd_sale_channels')
            ->where('prd_sale_channels.title', '經銷價販售(無獎金)')
            ->select('id')
            ->get()
            ->first()
            ->id;

        $allUsers = DB::table('usr_users')->select('id')->get();
        foreach ($allUsers as $allUser) {
            if (
                DB::table('usr_user_salechannel')->where([
                    'user_id' => $allUser->id,
                    'salechannel_id' => $salesId,
                ])->doesntExist()
            ) {
                UserSalechannel::create([
                    'user_id' => $allUser->id,
                    'salechannel_id' => $salesId,
                ]);
            }

            if (
                DB::table('usr_user_salechannel')->where([
                    'user_id' => $allUser->id,
                    'salechannel_id' => $erpSalesId,
                ])->doesntExist()
            ) {
                UserSalechannel::create([
                    'user_id' => $allUser->id,
                    'salechannel_id' => $erpSalesId,
                ]);
            }

            if (
                DB::table('usr_user_salechannel')->where([
                    'user_id' => $allUser->id,
                    'salechannel_id' => $noBonusSalesId,
                ])->doesntExist()
            ) {
                UserSalechannel::create([
                    'user_id' => $allUser->id,
                    'salechannel_id' => $noBonusSalesId,
                ]);
            }
        }

        $users = User::get();
        foreach ($users as $user) {
            $user->assignRole('Super Admin');
        }

    }
}
