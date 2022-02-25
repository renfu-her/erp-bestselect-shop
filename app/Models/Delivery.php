<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Delivery extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dlv_delivery';
    protected $guarded = [];

    public static function setData($id = null, $event, $event_id, $event_sn, $temp_id, $temp_name, $logistic_method, $logistic_status, $ship_depot_id, $ship_depot_name, $memo = null)
    {
        $data = null;
        $dataGet = null;
        if (null != $id) {
            $data = Delivery::where('id', $id);
            $dataGet = $data->get()->first();
        }
        $result = null;
        if (null == $dataGet) {
            $sn = date("ymd") . str_pad((Delivery::whereDate('created_at', '=', date('Y-m-d'))
                        ->withTrashed()
                        ->get()
                        ->count()) + 1, 3, '0', STR_PAD_LEFT);

            $result = Delivery::create([
                'sn' => $sn,
                'event' => $event,
                'event_id' => $event_id,
                'event_sn' => $event_sn,
                'temp_id' => $temp_id,
                'temp_name' => $temp_name,
                'logistic_method' => $logistic_method,
                'logistic_status' => $logistic_status,
                'ship_depot_id' => $ship_depot_id,
                'ship_depot_name' => $ship_depot_name,
                'memo' => $memo,
            ])->id;
//        } else {
//            $result = DB::transaction(function () use ($data, $dataGet, $event, $event_id, $event_sn, $temp_id, $temp_name, $logistic_method, $logistic_status, $ship_depot_id, $ship_depot_name, $$memo
//            ) {
//                $data->update([
//                    'event' => $event,
//                    'event_id' => $event_id,
//                    'event_sn' => $event_sn,
//                    'temp_id' => $temp_id,
//                    'temp_name' => $temp_name,
//                    'logistic_method' => $logistic_method,
//                    'logistic_status' => $logistic_status,
//                    'ship_depot_id' => $ship_depot_id,
//                    'ship_depot_name' => $ship_depot_name,
//                    'memo' => $memo,
//                ]);
//                return $dataGet->id;
//            });
        }
        return $result;
    }
}
