<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class testSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::get();
        foreach ($users as $user) {
            $user->assignRole('Super Admin');
        }
      
    }
}
