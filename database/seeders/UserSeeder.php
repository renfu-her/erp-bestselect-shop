<?php

namespace Database\Seeders;

use App\Enums\Globals\AppEnvClass;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        if (App::environment(AppEnvClass::Release)) {
            User::createUser('尚未設定負責人', 'unknown', null, 'qwer6789');
        } else {
            User::createUser('Hans', 'hayashi0126@gmail.com', null, '1234');
            User::createUser('小姜', 'program03@ittms.com.tw', null, '1234');
            User::createUser('理查', 'richardyuan30@gmail.com', null, '1234');
            User::createUser('阿君', 'ccps961032326@gmail.com', null, '1234');
            User::createUser('之谷', 'pntcwz@gmail.com', null, '1234');
            User::createUser('烏梅', 'hsihung08079@gmail.com', null, '1234');
            User::createUser('yoyo', 'yoyo@writingbeing.com', null, '1234');
            User::createUser('andy', 'andy', null, '1111');
            User::createUser('尚未設定負責人', 'unknown', null, 'qwer6789');
        }

        $users = User::get();
        foreach ($users as $user) {
            $user->assignRole('Super Admin');
        }
    }
}
