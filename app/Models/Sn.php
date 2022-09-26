<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sn extends Model
{
    use HasFactory;
    protected $table = 'sn';
    protected $guarded = [];

    public static function createSn($type, $prefix)
    {
        $sn = $prefix . date("Ymd") . str_pad((self::whereDate('created_at', '=', date('Y-m-d'))->where('type', $type)
                ->get()
                ->count()) + 1, 4, '0', STR_PAD_LEFT);

        self::create([
            'sn' => $sn,
            'type' => $type,
        ]);

        return $sn;
    }

}
