<?php

namespace Database\Seeders;

use App\Models\Depot;
use Illuminate\Database\Seeder;

class DepotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $depot1 = Depot::create([
                'name'     => '集運本倉',
                'sender'      => '倉管理者',
                'can_tally' => '1',
                'addr'      => '松江路148號8樓之1',
                'city_id'   => '1',
                'region_id' => '4',
                'tel'       => '0225716101',
                'address' => '104 台北市中山區松江路148號8樓之1',
            ])->id;

    }
}
