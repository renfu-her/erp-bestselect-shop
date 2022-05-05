<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSalechannel extends Model
{
    use HasFactory;
    protected $table = 'usr_user_salechannel';
    protected $guarded = [];
    public $timestamps = false;

    public static function updateSalechannel($user_id, $channel_ids)
    {
        self::where('user_id')->delete();
        if (!$channel_ids || count($channel_ids) == 0) {
            return;
        }
        self::insert(array_map(function ($n) use ($user_id) {
            return [
                'user_id' => $user_id,
                'salechannel_id' => $n,
            ];
        }, $channel_ids));

    }
}
