<?php

namespace Database\Seeders\Helper;

use App\Models\Addr;
use Illuminate\Database\Seeder;

// php use Illuminate\Support\Facades\DB;

class AddrSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // echo getcwd();
        //
        $strJsonFileContents = file_get_contents(getcwd() . "/database/seeders/Helper/taiwan_districts.json");
        $array = json_decode($strJsonFileContents, true);

        foreach ($array as $row) {

            $c = Addr::create(['title' => $row['name']]);

            $id = $c->id;
            Addr::insert(array_map(function ($d) use ($id) {
                return [
                    'title' => $d['name'],
                    'zipcode' => $d['zip'],
                    'parent_id' => $id,
                ];
            }, $row['districts']));
        }

    }
}
