<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleChannel extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'prd_sale_channels';
    protected $guarded = [];

    public static function saleList()
    {
        return self::select('*')->selectRaw('IF(is_realtime=1,"即時","非即時") as is_realtime_title');

    }

}
