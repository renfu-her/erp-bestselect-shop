<?php

namespace App\Models;

use App\Enums\Consignment\AuditStatus;
use App\Enums\Delivery\Event;
use App\Enums\Purchase\LogEventFeature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class CsnOrder extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'csn_orders';
    protected $guarded = [];
    protected $casts = [
        'scheduled_date'  => 'datetime:Y-m-d',
        'audit_date'  => 'datetime:Y-m-d',
    ];

    public static function createData($depot_id, $depot_name
        , $create_user_id = null, $create_user_name = null
        , $scheduled_date = null
    )
    {
        return DB::transaction(function () use (
            $depot_id, $depot_name
            , $create_user_id, $create_user_name
            , $scheduled_date
            ) {

            $sn = "CSO" . date("ymd") . str_pad((self::whereDate('created_at', '=', date('Y-m-d'))
                        ->withTrashed()
                        ->get()
                        ->count()) + 1, 4, '0', STR_PAD_LEFT);

            $id = self::create([
                "sn" => $sn,
                'depot_id' => $depot_id,
                'depot_name' => $depot_name,
                'create_user_id' => $create_user_id ?? null,
                'create_user_name' => $create_user_name ?? null,
                'scheduled_date' => $scheduled_date ?? null,
            ])->id;

            $rePcsLSC = PurchaseLog::stockChange($id, null, Event::csn_order()->value, $id, LogEventFeature::csn_order_add()->value, null,null, null, $create_user_id, $create_user_name);
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
            ->select('depot_id'
                , 'depot_name'
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

        $orign_audit_status = $purchase->audit_status;
        $purchase->scheduled_date = $purchaseReq['scheduled_date'] ?? $purchase->scheduled_date;
        $purchase->memo = $purchaseReq['memo'] ?? $purchase->memo;
        $purchase->audit_status = $purchaseReq['audit_status'] ?? $purchase->audit_status;

        return DB::transaction(function () use ($purchase, $id, $purchaseReq, $operator_user_id, $operator_user_name, $orign_audit_status
        ) {
            if ($purchase->isDirty()) {
                foreach ($purchase->getDirty() as $key => $val) {
                    $event = '';
                    if($key == 'scheduled_date') {
                        $event = '訂購日期';
                    } else if($key == 'audit_status') {
                        $event = '修改審核狀態';
                    }
                    $rePcsLSC = PurchaseLog::stockChange($id, null, Event::csn_order()->value, $id, LogEventFeature::csn_order_change_data()->value, null,null, $event, $operator_user_id, $operator_user_name);
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
                    "audit_status" => $purchaseReq['audit_status'] ?? AuditStatus::unreviewed()->value,
                ]);

                //從尚未審核 變成 核可後物態自動變檢貨中
                if (AuditStatus::unreviewed()->value == $orign_audit_status
                    && AuditStatus::approved()->value == $purchase->audit_status
                ) {
                    $delivery = Delivery::where('event', Event::csn_order()->value)
                        ->where('event_id', $id)->update([
                        'logistic_status' => '寄倉售出',
                        'logistic_status_code' => 'D9000',
                    ]);
                }
            }
            return ['success' => 1, 'error_msg' => ''];
        });
    }

    //刪除
    public static function del($id, $operator_user_id, $operator_user_name) {
        //判斷若有審核、否決 則不可刪除
        $consignment = CsnOrder::where('id', $id)->get()->first();
        if (null != $consignment->audit_date) {
            return ['success' => 0, 'error_msg' => '已審核無法刪除'];
        } else {
            return DB::transaction(function () use ($id, $operator_user_id, $operator_user_name
            ) {
                $rePcsLSC = PurchaseLog::stockChange($id, null, Event::csn_order()->value, $id, LogEventFeature::csn_order_del()->value, null,null, null, $operator_user_id, $operator_user_name);
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

        PurchaseLog::stockChange($id, null, Event::csn_order()->value, $id, LogEventFeature::csn_order_close()->value, null,null, null, $operator_user_id, $operator_user_name);
    }

    public static function getData($id) {
        $re = DB::table('csn_orders as csn_orders')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->select(
                'csn_orders.id'
                , 'csn_orders.sn'
                , 'csn_orders.depot_id'
                , 'csn_orders.depot_name'
                , 'csn_orders.create_user_id'
                , 'csn_orders.create_user_name'
                , DB::raw('DATE_FORMAT(csn_orders.scheduled_date,"%Y-%m-%d") as scheduled_date')
                , 'csn_orders.audit_date'
                , 'csn_orders.close_date'
                , 'csn_orders.audit_user_id'
                , 'csn_orders.audit_user_name'
                , 'csn_orders.audit_status'
                , 'csn_orders.memo'
                , 'csn_orders.created_at'
                , 'csn_orders.updated_at'
                , 'csn_orders.deleted_at'
            )
            ;
        return $re;
    }
}
