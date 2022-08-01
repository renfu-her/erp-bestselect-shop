<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;


class OrganizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $re = Http::get("https://www.besttour.com.tw/api/empdep.asp?type=6")->json();

        dd($re);
    }
}
