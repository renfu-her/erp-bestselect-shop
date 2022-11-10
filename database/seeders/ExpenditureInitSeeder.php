<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExpenditureInitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $items = "電腦用品 交際費 郵電費 文具用品 印刷費 差旅費 廣告費 電話費 辦公室租金 團費退款 水電費 教育訓練 雜項支出 預付款項 租金支出 交通費 進貨耗材 樣品物流費用 銷貨退回 稅捐";

        $payment = "原支票抽退 開立新支票 匯款 退刷卡 現金 街口支付 LINEPAY 台灣PAY";

        DB::table('exp_items')->insert(array_map(function ($n) {
            return ['title' => $n];
        }, explode(" ", $items)));

        DB::table('exp_payment')->insert(array_map(function ($n) {
            return ['title' => $n];
        }, explode(" ", $payment)));

    }
}
