<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class getEmployeeData extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::getEmployeeData();
    }
}
