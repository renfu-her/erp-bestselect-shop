<?php

namespace Database\Seeders;

use App\Models\Role;
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

        // dd('aa');

       // dd(Role::where('title', "員工")->get()->first());
        User::getEmployeeData([Role::where('title', "員工")->get()->first()->id]);
    }
}
