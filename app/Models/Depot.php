<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Depot extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'depot';

    protected $fillable = [
        'name',
        'sender',
        'can_tally',
        'address',
        'tel',
        'city_id',
        'region_id',
        'addr',
    ];

    //判斷倉庫是否需理貨
    public static function can_tally($id) {
        $depotData = Depot::where('id', '=', $id);
        $depotDataGet = $depotData->get()->first();
        $can_tally = false;
        if (null != $depotDataGet) {
            if (1 == $depotDataGet->can_tally) {
                $can_tally = true;
            }
        }
        return $can_tally;
    }
}
