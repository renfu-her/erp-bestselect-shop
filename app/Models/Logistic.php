<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Logistic extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dlv_logistic';
    protected $guarded = [];

    public static function getData($id) {
        $data = Logistic::where('id', $id);
        return $data;
    }

    //新增資料
    public static function createData($delivery_id)
    {
        $data = Logistic::where('delivery_id', $delivery_id);
        $dataGet = null;
        if (null != $data) {
            $dataGet = $data->first();
        }

        $result = null;
        if (null == $dataGet) {
            $delivery = Delivery::where('id', $delivery_id)->withTrashed()->get()->first();
            $sn = "LG" . date("ymd") . str_pad((Delivery::whereDate('created_at', '=', date('Y-m-d'))
                        ->withTrashed()
                        ->get()
                        ->count()) + 1, 3, '0', STR_PAD_LEFT);

            $result = Logistic::create([
                'sn' => $sn,
                'delivery_id' => $delivery->id,
                'ship_group_id' => $delivery->ship_group_id,
            ])->id;
            return ['success' => 1, 'error_msg' => "", 'id' => $result];
        } else {
            $result = $dataGet->id;
            return ['success' => 1, 'error_msg' => "", 'id' => $result];
        }
    }

    //更新物流單資料
    public static function updateData($id, $package_sn, $ship_group_id, $cost, $memo) {
        $data = Logistic::where('id', $id);
        $dataGet = null;
        if (null != $data) {
            $dataGet = $data->first();
        }

        if (null != $dataGet) {
            $updateData = [
                'package_sn' => $package_sn
                , 'ship_group_id' => $ship_group_id
                , 'cost' => $cost
                , 'memo' => $memo
            ];

            $data->update($updateData);

            return ['success' => 1, 'error_msg' => "", 'id' => $id];
        } else {
            return ['success' => 0, 'error_msg' => "查無資料"];
        }
    }

    public static function deleteById($id)
    {
        return DB::transaction(function () use ($id
        ) {
            $logistic = Logistic::where('id', $id)->withTrashed();
            $logistic_get = $logistic->first();
            if (null == $logistic_get) {
                return ['success' => 0, 'error_msg' => "無此物流單"];
            } else if ($logistic_get->audit_date != null) {
                //若已送出審核 則代表已扣除相應入庫單數量 則不給刪除
                return ['success' => 0, 'error_msg' => "已送出審核，無法刪除"];
            } else {
                $logistic->delete();
                return ['success' => 1, 'error_msg' => ""];
            }
        });
    }
}
