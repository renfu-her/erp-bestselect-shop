<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Depot extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'depot';

    protected $fillable = [
        'name',
        'sender',
        'can_pickup',
        'can_tally',
        'address',
        'tel',
        'city_id',
        'region_id',
        'addr',
    ];

    public static function dataList()
    {

        $sub = DB::table('depot_temp as dt')
            ->leftJoin('shi_temps as temp', 'dt.temp_id', '=', 'temp.id')
            ->select('dt.depot_id')
            ->selectRaw("GROUP_CONCAT(temp.temps) as temp")
            ->groupBy('dt.depot_id');

        $re = DB::table('depot')
            ->select(['depot.*', 'temp.temp'])
            ->leftJoin('depot_temp as dt', 'depot.id', '=', 'dt.depot_id')
            ->leftJoinSub($sub, 'temp', 'depot.id', '=', 'temp.depot_id');

        return $re;

    }

    //判斷倉庫是否需理貨
    public static function can_tally($id)
    {
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

    public static function getAllSelfPickup()
    {
        $re = self::where('can_pickup', '=', 1)
            ->select('id', 'name')
            ->orderBy('sort')
            ->get();
        return $re;
    }

    public static function getTempId($depot_id)
    {
        $re = DB::table('depot_temp')->where('depot_id', $depot_id)->get()->toArray();

        return array_map(function ($n) {
            return $n->temp_id;
        }, $re);

    }

    public static function updateTemp($depot_id, $temp)
    {
        DB::table('depot_temp')->where('depot_id', $depot_id)->delete();
        if (!$temp) {
            return;
        }
        DB::table('depot_temp')->insert(array_map(function ($n) use ($depot_id) {
            return ['depot_id' => $depot_id,
                'temp_id' => $n];
        }, $temp));
    }
}
