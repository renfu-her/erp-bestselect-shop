<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Sn extends Model
{
    use HasFactory;
    protected $table = 'sn';
    protected $guarded = [];

    public static function createSn($type, $prefix)
    {
        DB::beginTransaction();
        $sn = $prefix . date("Ymd") . str_pad((self::whereDate('created_at', '=', date('Y-m-d'))->where('type', $type)
                ->lockForUpdate()->get()
                ->count()) + 1, 4, '0', STR_PAD_LEFT);


        self::create([
            'sn' => $sn,
            'type' => $type,
        ]);

        DB::commit();
        return $sn;
    }

}
