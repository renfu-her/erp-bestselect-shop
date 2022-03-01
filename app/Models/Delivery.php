<?php

namespace App\Models;

use App\Enums\Delivery\Event;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Delivery extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dlv_delivery';
    protected $guarded = [];

    private static function getData($event, $event_id) {
        $data = null;
        if (null != $event_id) {
            if (Event::order()->value == $event) {
                $data = Delivery::where('event_id', $event_id);
            }
        }
        return $data;
    }

    //新增資料
    //創建時，將上層資料複製進來
    public static function createData($event, $event_id, $event_sn, $temp_id, $temp_name, $ship_category, $ship_category_name, $memo = null)
    {
        $data = Delivery::getData($event, $event_id);
        $dataGet = null;
        if (null != $data) {
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
                'ship_category' => $ship_category,
                'ship_category_name' => $ship_category_name,
                'memo' => $memo,
            ])->id;
        } else {
            $result = $dataGet->id;
        }
        return $result;
    }

    //更新物流狀態
    public static function updateLogisticStatus($event, $event_id, $logistic_status, $logistic_status_code)
    {
        $data = Delivery::getData($event, $event_id);
        $dataGet = null;
        if (null != $data) {
            $dataGet = $data->get()->first();
        }
        $result = null;
        if (null != $dataGet) {
            $result = DB::transaction(function () use ($data, $dataGet, $logistic_status, $logistic_status_code
            ) {
                $data->update([
                    'logistic_status' => $logistic_status,
                    'logistic_status_code' => $logistic_status_code,
                ]);
                return $dataGet->id;
            });
        }
        return $result;
    }

    //更新出貨倉庫
    public static function updateShipDepot($event, $event_id, $ship_depot_id, $ship_depot_name)
    {
        $data = Delivery::getData($event, $event_id);
        $dataGet = null;
        if (null != $data) {
            $dataGet = $data->get()->first();
        }
        $result = null;
        if (null != $dataGet) {
            $result = DB::transaction(function () use ($data, $dataGet, $ship_depot_id, $ship_depot_name
            ) {
                $data->update([
                    'ship_depot_id' => $ship_depot_id,
                    'ship_depot_name' => $ship_depot_name,
                ]);
                return $dataGet->id;
            });
        }
        return $result;
    }

    public static function deleteByEventId($event, $event_id)
    {
        if (null != $event_id) {
            if (Event::order()->value == $event) {
                Delivery::where('event_id', $event_id)->delete();
            }
        }
    }
}
