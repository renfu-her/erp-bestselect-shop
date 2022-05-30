<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DividendSetting extends Model
{
    use HasFactory;
    protected $table = 'dis_dividend_setting';
    protected $guarded = [];
    public $timestamps = false;

    public static function updateSetting($limit_day, $auto_active_day)
    {

        $re = self::get()->toArray();
        if (count($re) == 0) {
            self::create([
                'limit_day' => $limit_day,
                'auto_active_day' => $auto_active_day,
            ]);
        } else {
            self::where('id', 1)->update([
                'limit_day' => $limit_day,
                'auto_active_day' => $auto_active_day,
            ]);
        }
    }

    public static function getData()
    {
        return self::get()->first();
    }
}
