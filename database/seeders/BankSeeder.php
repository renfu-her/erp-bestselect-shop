<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $list = [['004', '臺灣銀行'],
            ['005', '台灣土地銀行'],
            ['006', '合作金庫商業銀行'],
            ['007', '第一商業銀行'],
            ['008', '華南商業銀行'],
            ['009', '彰化商業銀行'],
            ['011', '上海商業儲蓄銀行'],
            ['012', '台北富邦商業銀行'],
            ['013', '國泰世華商業銀行'],
            ['017', '兆豐國際商業銀行'],
            ['050', '臺灣中小企業銀行'],
            ['103', '臺灣新光商業銀行'],
            ['807', '永豐商業銀行'],
            ['812', '台新國際商業銀行'],
            ['815', '日盛國際商業銀行'],
            ['822', '中國信託商業銀行',
            ]];

        Bank::insert(array_map(function ($n) {
            return [
                'code' => $n[0],
                'title' => $n[1],
            ];
        }, $list));

    }
}
