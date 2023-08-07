<?php

namespace Database\Seeders;

use App\Enums\Discount\DisMethod;
use App\Models\Discount;
use Illuminate\Database\Seeder;

class addDiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        for ($i = 0; $i < 120;) {
            $sn = self::createCode(12);

            if (!Discount::where('sn', $sn)->first()) {
                $i++;
                echo $sn . "\n";
                Discount::createCode($sn,
                    '歡慶旅展 優惠獎不完',
                    0,
                    DisMethod::cash(),
                    100,
                    '2023/07/01',
                    '2023/12/31',
                    1,
                    [],
                    1);
            }
        }

    }

    private function createCode($long = 10)
    {
        $characters = '_ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $random_char = '';
        for ($i = 0; $i < $long; $i++) {
            $random_index = rand(0, strlen($characters) - 1);
            $random_char .= $characters[$random_index];
        }

        return $random_char;
    }
}
