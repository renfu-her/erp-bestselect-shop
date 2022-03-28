<?php

namespace App\Models;

use App\Enums\Purchase\LogEvent;
use App\Enums\Purchase\LogEventFeature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Consignment extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'csn_consignment';
    protected $guarded = [];
    protected $casts = [
        'send_date'  => 'datetime:Y-m-d',
        'audit_date'  => 'datetime:Y-m-d',
    ];

    public static function createData($send_depot_id, $send_depot_name, $receive_depot_id, $receive_depot_name
        , $create_user_id = null, $create_user_name = null
        , $send_date = null
    )
    {
        return DB::transaction(function () use (
            $send_depot_id, $send_depot_name, $receive_depot_id, $receive_depot_name
//            , $ship_temp_id, $ship_temp_name, $ship_event_id, $ship_event
//            , $dlv_fee, $logistic_status_code, $logistic_status
            , $create_user_id, $create_user_name
            , $send_date
//            , $audit_date, $audit_user_id, $audit_user_name, $audit_status
            ) {

            $sn = "CSN" . date("ymd") . str_pad((self::whereDate('created_at', '=', date('Y-m-d'))
                        ->withTrashed()
                        ->get()
                        ->count()) + 1, 4, '0', STR_PAD_LEFT);

            $id = self::create([
                "sn" => $sn,
                'send_depot_id' => $send_depot_id,
                'send_depot_name' => $send_depot_name,
                'receive_depot_id' => $receive_depot_id,
                'receive_depot_name' => $receive_depot_name,
                'create_user_id' => $create_user_id ?? null,
                'create_user_name' => $create_user_name ?? null,
                'send_date' => $send_date ?? null,
            ])->id;

            $rePcsLSC = PurchaseLog::stockChange($id, null, LogEvent::consignment()->value, $id, LogEventFeature::csn_add()->value, null, null, $create_user_id, $create_user_name);
            if ($rePcsLSC['success'] == 0) {
                DB::rollBack();
                return $rePcsLSC;
            }

            return ['success' => 1, 'error_msg' => "", 'id' => $id];
        });
    }

    public static function checkToUpdateConsignmentData($id, array $purchaseReq, string $changeStr,
                                                        $operator_user_id, $operator_user_name
    )
    {
        $purchase = self::where('id', '=', $id)
            ->select('send_depot_id'
                , 'send_depot_name'
                , 'receive_depot_id'
                , 'receive_depot_name'
                , 'ship_temp_id'
                , 'ship_temp_name'
                , 'ship_event_id'
                , 'ship_event'
                , 'dlv_fee'
                , 'logistic_status_code'
                , 'logistic_status'
                , 'create_user_id'
                , 'create_user_name'
                , 'send_date'
                , 'memo'
            )
            ->selectRaw('DATE_FORMAT(send_date,"%Y-%m-%d") as send_date')
            ->selectRaw('DATE_FORMAT(audit_date,"%Y-%m-%d") as audit_date')
            ->get()->first();

        $purchase->send_depot_id = $purchaseReq['send_depot_id'];
        $purchase->send_depot_name = $purchaseReq['send_depot_name'];
        $purchase->receive_depot_id = $purchaseReq['receive_depot_id'];
        $purchase->receive_depot_name = $purchaseReq['receive_depot_name'] ?? null;
        $purchase->ship_temp_id = $purchaseReq['ship_temp_id'] ?? null;
        $purchase->ship_temp_name = $purchaseReq['ship_temp_name'] ?? null;
        $purchase->ship_event_id = $purchaseReq['ship_event_id'] ?? null;
        $purchase->ship_event = $purchaseReq['ship_event'] ?? null;
        $purchase->dlv_fee = $purchaseReq['dlv_fee'] ?? 0;
        $purchase->logistic_status_code = $purchaseReq['logistic_status_code'] ?? null;
        $purchase->logistic_status = $purchaseReq['logistic_status'] ?? null;
        $purchase->create_user_id = $purchaseReq['create_user_id'] ?? null;
        $purchase->create_user_name = $purchaseReq['create_user_name'] ?? null;
        $purchase->send_date = $purchaseReq['send_date'] ?? null;
        $purchase->memo = $purchaseReq['memo'] ?? null;

        return DB::transaction(function () use ($purchase, $id, $purchaseReq, $changeStr, $operator_user_id, $operator_user_name
        ) {
            if ($purchase->isDirty()) {
                foreach ($purchase->getDirty() as $key => $val) {
                    $event = '';
//                    if ($key == 'supplier_id') {
//                        $event = '修改廠商';
//                    } else if($key == 'supplier_sn') {
//                        $event = '修改廠商訂單號';
//                    } else if($key == 'scheduled_date') {
//                        $event = '修改廠商預計進貨日期';
//                    } else if($key == 'has_tax') {
//                        $event = '修改課稅別';
//                        if (0 == $val) {
//                            $val = '應稅';
//                        } else if (1 == $val) {
//                            $val = '免稅';
//                        }
//                    } else if($key == 'logistics_price') {
//                        $event = '修改物流費用';
//                    } else if($key == 'logistics_memo') {
//                        $event = '修改物流備註';
//                    } else if($key == 'invoice_num') {
//                        $event = '修改發票號碼';
//                    } else if($key == 'invoice_date') {
//                        $event = '修改發票日期';
//                    }
                    $changeStr .= ' ' . $key . ' change to ' . $val;
                    $rePcsLSC = PurchaseLog::stockChange($id, null, LogEvent::consignment()->value, $id, LogEventFeature::csn_change_data()->value, null, $event, $operator_user_id, $operator_user_name);
                    if ($rePcsLSC['success'] == 0) {
                        DB::rollBack();
                        return $rePcsLSC;
                    }
                }
                self::where('id', $id)->update([
                    "send_depot_id" => $purchaseReq['send_depot_id'],
                    "send_depot_name" => $purchaseReq['send_depot_name'],
                    "receive_depot_id" => $purchaseReq['receive_depot_id'],
                    "receive_depot_name" => $purchaseReq['receive_depot_name'] ?? null,
                    "ship_temp_id" => $purchaseReq['ship_temp_id'] ?? null,
                    "ship_temp_name" => $purchaseReq['ship_temp_name'] ?? null,
                    "ship_event_id" => $purchaseReq['ship_event_id'] ?? null,
                    "ship_event" => $purchaseReq['ship_event'] ?? null,
                    "dlv_fee" => $purchaseReq['dlv_fee'] ?? 0,
                    "logistic_status_code" => $purchaseReq['logistic_status_code'] ?? null,
                    "logistic_status" => $purchaseReq['logistic_status'] ?? null,
                    "create_user_id" => $purchaseReq['create_user_id'] ?? null,
                    "create_user_name" => $purchaseReq['create_user_name'] ?? null,
                    "send_date" => $purchaseReq['send_date'] ?? null,
                    "audit_status" => $purchaseReq['audit_status'] ?? 0,
                    "memo" => $purchaseReq['memo'] ?? null,
                ]);
            }
            return ['success' => 1, 'error_msg' => $changeStr];
        });
    }

    //刪除
    public static function del($id, $operator_user_id, $operator_user_name) {
        //判斷若有入庫、付款單 則不可刪除
        return DB::transaction(function () use ($id, $operator_user_id, $operator_user_name
        ) {
            $rePcsLSC = PurchaseLog::stockChange($id, null, LogEvent::consignment()->value, $id, LogEventFeature::csn_del()->value, null, null, $operator_user_id, $operator_user_name);
            if ($rePcsLSC['success'] == 0) {
                DB::rollBack();
                return $rePcsLSC;
            }
            self::where('id', '=', $id)->delete();
            return ['success' => 1, 'error_msg' => ""];
        });
    }

    //結案
    public static function close($id, $operator_user_id, $operator_user_name) {
        self::where('id', $id)->update(['audit_date' => date('Y-m-d H:i:s')]);
        PurchaseLog::stockChange($id, null, LogEvent::consignment()->value, $id, LogEventFeature::csn_close()->value, null, null, $operator_user_id, $operator_user_name);
    }

    //起日 訖日 是否含已結單 發票號碼
    public static function getDataList($sDate = null, $eDate = null, $hasClose = false)
    {
        $result = DB::table('csn_consignment as consignment')
            ->select(
                'id'
                , 'consignment.sn as consignment_sn'
                , 'consignment.send_depot_id as send_depot_id'
                , 'consignment.send_depot_name as send_depot_name'
                , 'consignment.receive_depot_id as receive_depot_id'
                , 'consignment.receive_depot_name as receive_depot_name'
                , 'consignment.ship_temp_id as ship_temp_id'
                , 'consignment.ship_temp_name as ship_temp_name'
                , 'consignment.ship_event_id as ship_event_id'
                , 'consignment.ship_event as ship_event'
                , 'consignment.dlv_fee as dlv_fee'
                , 'consignment.logistic_status_code as logistic_status_code'
                , 'consignment.logistic_status as logistic_status'
                , 'consignment.create_user_id as create_user_id'
                , 'consignment.create_user_name as create_user_name'
                , 'consignment.audit_user_id as audit_user_id'
                , 'consignment.audit_user_name as audit_user_name'
                , 'consignment.audit_status as audit_status'
                , 'consignment.memo as memo'
            )
            ->selectRaw('DATE_FORMAT(consignment.send_date,"%Y-%m-%d") as send_date')
            ->selectRaw('DATE_FORMAT(consignment.audit_date,"%Y-%m-%d") as audit_date')
            ->whereNull('consignment.deleted_at');

        if ($sDate && $eDate) {
            $sDate = date('Y-m-d 00:00:00', strtotime($sDate));
            $eDate = date('Y-m-d 23:59:59', strtotime($eDate));
            $result->whereBetween('consignment.created_at', [$sDate, $eDate]);
        }
        //是否含有結單資料
        if (false == $hasClose) {
            $result->whereNull('consignment.audit_date');
        }

        return $result;
    }

    public static function getData($id)
    {
        $result = DB::table('csn_consignment as consignment')
            ->select(
                'id'
                , 'consignment.sn as consignment_sn'
                , 'consignment.send_depot_id as send_depot_id'
                , 'consignment.send_depot_name as send_depot_name'
                , 'consignment.receive_depot_id as receive_depot_id'
                , 'consignment.receive_depot_name as receive_depot_name'
                , 'consignment.ship_temp_id as ship_temp_id'
                , 'consignment.ship_temp_name as ship_temp_name'
                , 'consignment.ship_event_id as ship_event_id'
                , 'consignment.ship_event as ship_event'
                , 'consignment.dlv_fee as dlv_fee'
                , 'consignment.logistic_status_code as logistic_status_code'
                , 'consignment.logistic_status as logistic_status'
                , 'consignment.create_user_id as create_user_id'
                , 'consignment.create_user_name as create_user_name'
                , 'consignment.audit_user_id as audit_user_id'
                , 'consignment.audit_user_name as audit_user_name'
                , 'consignment.audit_status as audit_status'
                , 'consignment.memo as memo'

            )
            ->selectRaw('DATE_FORMAT(consignment.send_date,"%Y-%m-%d") as send_date')
            ->selectRaw('DATE_FORMAT(consignment.audit_date,"%Y-%m-%d") as audit_date')
            ->whereNull('consignment.deleted_at')
            ->where('consignment.id', '=', $id);

        return $result;
    }

}
