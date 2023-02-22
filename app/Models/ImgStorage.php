<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ImgStorage extends Model
{
    use HasFactory;

    protected $table = 'img_storage';
    protected $guarded = [];

    public static function dataList($user_name = null, $sDate = null, $eDate = null)
    {
        $re = DB::table('img_storage as img')
            ->leftJoin('usr_users as user', 'img.user_id', '=', 'user.id')
            ->select(['img.*', 'user.name as user_name']);

        if ($user_name) {
            $re->where('user.name', 'like', "%$user_name%");
        }

        if ($sDate) {
            $re->where('img.created_at', ">=", date("Y-m-d 00:00:00", strtotime($sDate)));
        }

        if ($eDate) {
            $re->where('img.created_at', "<=", date("Y-m-d 23:59:59", strtotime($eDate)));
        }

        return $re;

    }
}
