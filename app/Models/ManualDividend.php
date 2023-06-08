<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ManualDividend extends Model
{
    use HasFactory;
    protected $table = 'dis_manual_dividend';
    protected $guarded = [];

    public static function dataList()
    {

        return DB::table('dis_manual_dividend as md')
            ->leftJoin('usr_users as user', 'md.user_id', '=', 'user.id')
            ->select(['md.*', 'user.name as user_name']);
    }


}
