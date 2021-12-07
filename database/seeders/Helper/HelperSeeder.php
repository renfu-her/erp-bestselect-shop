<?php

namespace Database\Seeders\Helper;

use Illuminate\Database\Seeder;

class HelperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        $this->call([
            AddrSeeder::class
        ]);
    }
}
