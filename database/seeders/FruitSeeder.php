<?php

namespace Database\Seeders;

use App\Models\Fruit;
use App\Models\FruitCollection;
use Illuminate\Database\Seeder;

class FruitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $collections = [
            ['春季水果', '(3至5月)'],
            ['夏季水果', '(6至8月)'],
            ['秋季水果', '(9至11月)'],
            ['東季水果', '(12至2月)'],
            ['進口水果', '(1至12月)'],
        ];

        FruitCollection::insert(array_map(function ($n) {
            return [
                'title' => $n[0],
                'sub_title' => $n[1],
            ];
        }, $collections));

        $fruits = [
            [
                'title' => '水果',
                'sub_title' => '副標',
                'place' => '產地',
                'season' => '季節',
                'pic' => '圖片',
                'link' => '連結',
                'text' => '內文',
                'status' => '狀態',
            ],
        ];

        Fruit::insert($fruits);
    }
}
