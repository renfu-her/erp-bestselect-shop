<?php

namespace App\Models;

use App\Enums\Delivery\Event;
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
        'scheduled_date'  => 'datetime:Y-m-d',
        'audit_date'  => 'datetime:Y-m-d',
    ];

    public static function createData($send_depot_id, $send_depot_name, $receive_depot_id, $receive_depot_name
        , $create_user_id = null, $create_user_name = null
        , $scheduled_date = null
    )
    {
        return DB::transaction(function () use (
            $send_depot_id, $send_depot_name, $receive_depot_id, $receive_depot_name
//            , $ship_temp_id, $ship_temp_name, $ship_event_id, $ship_event
//            , $dlv_fee, $logistic_status_code, $logistic_status
            , $create_user_id, $create_user_name
            , $scheduled_date
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
                'scheduled_date' => $scheduled_date ?? null,
            ])->id;

            $rePcsLSC = PurchaseLog::stockChange($id, null, Event::consignment()->value, $id, LogEventFeature::csn_add()->value, null, null, $create_user_id, $create_user_name);
            if ($rePcsLSC['success'] == 0) {
                DB::rollBack();
                return $rePcsLSC;
            }

            return ['success' => 1, 'error_msg' => "", 'id' => $id];
        });
    }

    //更新現有資料
    public static function checkToUpdateConsignmentData($id, array $purchaseReq,
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
                , 'scheduled_date'
                , 'audit_date'
                , 'audit_user_id'
                , 'audit_user_name'
                , 'audit_status'
                , 'memo'
            )
            ->selectRaw('DATE_FORMAT(scheduled_date,"%Y-%m-%d") as scheduled_date')
            ->selectRaw('DATE_FORMAT(audit_date,"%Y-%m-%d") as audit_date')
            ->get()->first();

//        $purchase->scheduled_date = $purchaseReq['scheduled_date'] ?? null;
        $purchase->memo = $purchaseReq['memo'] ?? null;
        $purchase->audit_status = $purchaseReq['audit_status'] ?? null;

        return DB::transaction(function () use ($purchase, $id, $purchaseReq, $operator_user_id, $operator_user_name
        ) {
            if ($purchase->isDirty()) {
                foreach ($purchase->getDirty() as $key => $val) {
                    $event = '';
                    if($key == 'scheduled_date') {
                        $event = '預計入庫日期';
                    }
                    $rePcsLSC = PurchaseLog::stockChange($id, null, Event::consignment()->value, $id, LogEventFeature::csn_change_data()->value, null, $event, $operator_user_id, $operator_user_name);
                    if ($rePcsLSC['success'] == 0) {
                        DB::rollBack();
                        return $rePcsLSC;
                    }
                }
                $curr_date = date('Y-m-d H:i:s');
                self::where('id', $id)->update([
                    "scheduled_date" => $purchaseReq['scheduled_date'] ?? null,
                    "memo" => $purchaseReq['memo'] ?? null,
                    "audit_date" => $curr_date,
                    "audit_user_id" => $operator_user_id,
                    "audit_user_name" => $operator_user_name,
                    "audit_status" => $purchaseReq['audit_status'] ?? App\Enums\Consignment\AuditStatus::unreviewed()->value,
                ]);
            }
            return ['success' => 1, 'error_msg' => ''];
        });
    }

    //刪除
    public static function del($id, $operator_user_id, $operator_user_name) {
        //判斷若有審核、否決 則不可刪除
        $consignment = Consignment::where('id', $id)->get()->first();
        if (null != $consignment->audit_date) {
            return ['success' => 0, 'error_msg' => '已審核無法刪除'];
        } else {
            return DB::transaction(function () use ($id, $operator_user_id, $operator_user_name
            ) {
                $rePcsLSC = PurchaseLog::stockChange($id, null, Event::consignment()->value, $id, LogEventFeature::csn_del()->value, null, null, $operator_user_id, $operator_user_name);
                if ($rePcsLSC['success'] == 0) {
                    DB::rollBack();
                    return $rePcsLSC;
                }
                self::where('id', '=', $id)->delete();
                return ['success' => 1, 'error_msg' => ""];
            });
        }
    }

    //結案
    public static function close($id, $operator_user_id, $operator_user_name) {
        $currDate = date('Y-m-d H:i:s');
        self::where('id', $id)->update(['close_date' => $currDate]);
        PurchaseInbound::where('event', Event::consignment()->value)
            ->where('event_id', '=', $id)
            ->whereNull('deleted_at')
            ->update([ 'close_date' => $currDate ]);

        PurchaseLog::stockChange($id, null, Event::consignment()->value, $id, LogEventFeature::csn_close()->value, null, null, $operator_user_id, $operator_user_name);
    }

    //起日 訖日 是否含已結單 發票號碼
    public static function getDataList($sDate = null, $eDate = null, $hasClose = false)
    {
        $result = DB::table('csn_consignment as consignment')
            ->select(
                'consignment.id'
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
            ->selectRaw('DATE_FORMAT(consignment.scheduled_date,"%Y-%m-%d") as scheduled_date')
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
            ->leftJoin('depot as send', 'send.id', '=', 'consignment.send_depot_id')
            ->leftJoin('depot as rcv', 'rcv.id', '=', 'consignment.receive_depot_id')
            ->select(
                'consignment.id'
                , 'consignment.sn as consignment_sn'
                , 'consignment.send_depot_id as send_depot_id'
                , 'consignment.send_depot_name as send_depot_name'
                , 'send.tel as send_depot_tel'
                , 'send.address as send_depot_address'
                , 'consignment.receive_depot_id as receive_depot_id'
                , 'consignment.receive_depot_name as receive_depot_name'
                , 'rcv.tel as receive_depot_tel'
                , 'rcv.address as receive_depot_address'
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
            ->selectRaw('DATE_FORMAT(consignment.created_at,"%Y-%m-%d") as created_at')
            ->selectRaw('DATE_FORMAT(consignment.scheduled_date,"%Y-%m-%d") as scheduled_date')
            ->selectRaw('DATE_FORMAT(consignment.audit_date,"%Y-%m-%d") as audit_date')
            ->selectRaw('DATE_FORMAT(consignment.close_date,"%Y-%m-%d") as close_date')
            ->whereNull('consignment.deleted_at')
            ->where('consignment.id', '=', $id);

        return $result;
    }

    public static function getDeliveryData($consignment_id = null)
    {
        $query = DB::table('csn_consignment as consignment')
            ->leftJoin('depot as send', 'send.id', '=', 'consignment.send_depot_id')
            ->leftJoin('depot as rcv', 'rcv.id', '=', 'consignment.receive_depot_id')
            ->leftJoin('dlv_delivery', function ($join) {
                $join->on('dlv_delivery.event_id', '=', 'consignment.id')
                    ->where('dlv_delivery.event', '=', Event::consignment()->value);
            })
            ->leftJoin('dlv_logistic', 'dlv_logistic.delivery_id', '=', 'dlv_delivery.id')
            ->leftJoin('shi_group', 'shi_group.id', '=', 'dlv_logistic.ship_group_id')
            ->leftJoin('shi_temps', 'shi_temps.id', '=', 'shi_group.temps_fk')
            ->select(
                'consignment.id as consignment_id'
                , 'consignment.sn as consignment_sn'
                , 'consignment.send_depot_id as send_depot_id'
                , 'consignment.send_depot_name as send_depot_name'
                , 'send.tel as send_depot_tel'
                , 'send.address as send_depot_address'
                , 'send.can_tally as send_can_tally'
                , 'consignment.receive_depot_id as receive_depot_id'
                , 'consignment.receive_depot_name as receive_depot_name'
                , 'rcv.tel as receive_depot_tel'
                , 'rcv.address as receive_depot_address'
                , 'rcv.can_tally as rcv_can_tally'
                , 'consignment.logistic_status_code as logistic_status_code'
                , 'consignment.logistic_status as logistic_status'
                , 'consignment.create_user_id as create_user_id'
                , 'consignment.create_user_name as create_user_name'
                , 'consignment.audit_user_id as audit_user_id'
                , 'consignment.audit_user_name as audit_user_name'
                , 'consignment.audit_status as audit_status'
                , 'consignment.memo as memo'
                , 'consignment.created_at as created_at_withHIS'
                , DB::raw('DATE_FORMAT(consignment.created_at,"%Y-%m-%d") as created_at')
                , DB::raw('DATE_FORMAT(consignment.scheduled_date,"%Y-%m-%d") as scheduled_date')
                , DB::raw('DATE_FORMAT(consignment.audit_date,"%Y-%m-%d") as audit_date')
                , DB::raw('DATE_FORMAT(consignment.close_date,"%Y-%m-%d") as close_date')

                , 'dlv_delivery.sn as dlv_sn'
                , 'dlv_delivery.logistic_status as dlv_logistic_status'
                , 'dlv_delivery.logistic_status_code as dlv_logistic_status_code'
                , 'dlv_delivery.audit_date as dlv_audit_date'
                , 'dlv_delivery.audit_user_id as dlv_audit_user_id'
                , 'dlv_delivery.audit_user_name as dlv_audit_user_name'

                , 'dlv_logistic.sn as lgt_sn'
                , 'dlv_logistic.package_sn'
                , 'dlv_logistic.cost as lgt_cost'
                , 'dlv_logistic.memo as lgt_memo'
                , 'shi_group.name as group_name'
                , 'shi_group.note as group_note'
                , 'shi_temps.temps'
            );
        if ($consignment_id) {
            $query->where('consignment.id', $consignment_id);
        }

        return $query;
    }
}
