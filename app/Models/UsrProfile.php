<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UsrProfile extends Model
{
    use HasFactory;
    protected $table = 'usr_profile';
    protected $guarded = [];

    public static function dataList()
    {
        return DB::table('usr_profile as profile')
            ->leftJoin('usr_users as user', 'profile.user_id', '=', 'user.id')
            ->select('profile.*', 'user.name', 'user.account');
    }
}
