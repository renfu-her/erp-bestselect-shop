<?php

namespace App\Models;

use App\Enums\Consignment\AuditStatus;
use App\Enums\Delivery\Event;
use App\Enums\Purchase\LogEventFeature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Purchase extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'pcs_purchase';
    protected $guarded = [];
    protected $casts = [
        'scheduled_date'  => 'datetime:Y-m-d',
        'audit_date'  => 'datetime:Y-m-d',
    ];

    public static function createPurchase($supplier_id, $supplier_name, $supplier_nickname, $supplier_sn = null, $purchase_user_id, $purchase_user_name
        , $scheduled_date
        , $logistics_price = 0, $logistics_memo = null, $invoice_num = null, $invoice_date = null
    )
    {
        return DB::transaction(function () use (
            $supplier_id,
            $supplier_name,
            $supplier_nickname,
            $supplier_sn,
            $purchase_user_id,
            $purchase_user_name,
            $scheduled_date,
            $logistics_price,
            $logistics_memo,
            $invoice_num,
            $invoice_date
            ) {

            $sn = "B" . date("ymd") . str_pad((self::whereDate('created_at', '=', date('Y-m-d'))
                        ->withTrashed()
                        ->get()
                        ->count()) + 1, 4, '0', STR_PAD_LEFT);

            $id = self::create([
                "sn" => $sn,
                'supplier_id' => $supplier_id,
                'supplier_name' => $supplier_name,
                'supplier_nickname' => $supplier_nickname,
                'supplier_sn' => $supplier_sn ?? null,
                'purchase_user_id' => $purchase_user_id,
                'purchase_user_name' => $purchase_user_name,
                'scheduled_date' => $scheduled_date,
                'logistics_price' => $logistics_price ?? 0,
                'logistics_memo' => $logistics_memo ?? null,
                'invoice_num' => $invoice_num ?? null,
                'invoice_date' => $invoice_date ?? null,
            ])->id;

            $rePcsLSC = PurchaseLog::stockChange($id, null, Event::purchase()->value, $id, LogEventFeature::add()->value, null, null, null, null, null, $purchase_user_id, $purchase_user_name);
            if ($rePcsLSC['success'] == 0) {
                DB::rollBack();
                return $rePcsLSC;
            }
            return ['success' => 1, 'error_msg' => "", 'id' => $id];
        });
    }

    //判斷已核可的採購單有被編輯審核狀態、物流資訊
    public static function checkInputApprovedDataDirty($id, $tax, array $purchaseReq, array $purchasePayReq) {
        $purchase = Purchase::where('id', '=', $id)
            ->select('supplier_id'
                , 'logistics_price'
                , 'logistics_memo'
                , 'audit_status'
            )
            ->get()->first();

        //判斷在尚未審核時才能編輯的資料是否被編輯過
        $purchase = Purchase::verifyPcsNormalCanEditData($purchase, $tax, $purchaseReq, $purchasePayReq);
        return $purchase;
    }

    public static function checkToUpdatePurchaseData($id, array $purchaseReq, $operator_user_id, $operator_user_name, $tax, array $purchasePayReq
    )
    {
        $purchase = Purchase::where('id', '=', $id)
            ->select('supplier_id'
                , 'supplier_sn'
                , 'has_tax'
                , 'logistics_price'
                , 'logistics_memo'
                , 'invoice_num'
                , 'invoice_date'
                , 'audit_status'
            )
            ->selectRaw('DATE_FORMAT(scheduled_date,"%Y-%m-%d") as scheduled_date')
            ->selectRaw('DATE_FORMAT(invoice_date,"%Y-%m-%d") as invoice_date')
            ->selectRaw('DATE_FORMAT(audit_date,"%Y-%m-%d") as audit_date')
            ->get()->first();

        //尚未審核可修改任一選項
        //核可、否決後 不可修改採購商品清單 物流、需可修改付款發票資訊
        $orign_audit_status = $purchase->audit_status;
        if (AuditStatus::unreviewed()->value == $orign_audit_status) {
            $purchase = Purchase::verifyPcsNormalCanEditData($purchase, $tax, $purchaseReq, $purchasePayReq);
            $purchase = Purchase::verifyPcsApprovedCanEditData($purchase, $tax, $purchaseReq, $purchasePayReq);
        } else {
            $purchase = Purchase::verifyPcsApprovedCanEditData($purchase, $tax, $purchaseReq, $purchasePayReq);
        }

        return DB::transaction(function () use ($purchase, $id, $purchaseReq, $operator_user_id, $operator_user_name, $tax, $purchasePayReq, $orign_audit_status
        ) {
//            dd($purchase->isDirty(), $purchase);
            if ($purchase->isDirty()) {
                foreach ($purchase->getDirty() as $key => $val) {
                    $event = '';
                    $logEventFeature = LogEventFeature::change_data()->value;
                    if ($key == 'supplier_id') {
                        $event = '修改廠商';
                    } else if($key == 'supplier_sn') {
                        $event = '修改廠商訂單號';
                    } else if($key == 'scheduled_date') {
                        $event = '修改廠商預計進貨日期';
                    } else if($key == 'has_tax') {
                        $event = '修改課稅別';
                        if (0 == $val) {
                            $val = '應稅';
                        } else if (1 == $val) {
                            $val = '免稅';
                        }
                    } else if($key == 'audit_status') {
                        $event = '修改審核狀態';
                    } else if($key == 'logistics_price') {
                        $event = '修改物流費用';
                    } else if($key == 'logistics_memo') {
                        $event = '修改物流備註';
                    } else if($key == 'invoice_num') {
                        $event = '修改發票號碼';
                    } else if($key == 'invoice_date') {
                        $event = '修改發票日期';
                    }

                    $rePcsLSC = PurchaseLog::stockChange($id, null, Event::purchase()->value, $id, LogEventFeature::change_data()->value, null, null, $event, null, null, $operator_user_id, $operator_user_name);
                    if ($rePcsLSC['success'] == 0) {
                        DB::rollBack();
                        return $rePcsLSC;
                    }
                }
                $updArr = [];
                if (AuditStatus::unreviewed()->value == $orign_audit_status) {
                    $updArr = [
//                        "supplier_id" => $purchaseReq['supplier'],
                        "supplier_sn" => $purchaseReq['supplier_sn'],
                        "scheduled_date" => $purchaseReq['scheduled_date'],
                        "has_tax" => $tax,
                        'invoice_num' => $purchasePayReq['invoice_num'] ?? null,
                        'invoice_date' => $purchasePayReq['invoice_date'] ?? null,

                        'logistics_price' => $purchasePayReq['logistics_price'] ?? 0,
                        'logistics_memo' => $purchasePayReq['logistics_memo'] ?? null,
                    ];

                    $curr_date = date('Y-m-d H:i:s');
                    $updArr['audit_date'] = $curr_date;
                    $updArr['audit_user_id'] = $operator_user_id;
                    $updArr['audit_user_name'] = $operator_user_name;
                    $updArr['audit_status'] = $purchaseReq['audit_status'] ?? App\Enums\Consignment\AuditStatus::unreviewed()->value;
                } else {
                    $updArr = [
                        "supplier_sn" => $purchaseReq['supplier_sn'],
                        "scheduled_date" => $purchaseReq['scheduled_date'],
                        "has_tax" => $tax,
                        'invoice_num' => $purchasePayReq['invoice_num'] ?? null,
                        'invoice_date' => $purchasePayReq['invoice_date'] ?? null,
                    ];
                }
                Purchase::where('id', $id)->update($updArr);
            }
            return ['success' => 1, 'error_msg' => ''];
        });
    }

    /**
     * 核可否決後 可修改的欄位
     */
    public static function verifyPcsApprovedCanEditData($purchase, $tax, array $purchaseReq, array $purchasePayReq)
    {
        if (null != $purchase && null != $purchasePayReq) {
            $purchase->supplier_sn = $purchaseReq['supplier_sn'] ?? null;
            $purchase->scheduled_date = $purchaseReq['scheduled_date'];
            $purchase->has_tax = $tax;
            $purchase->invoice_num = $purchasePayReq['invoice_num'] ?? null;
            $purchase->invoice_date = $purchasePayReq['invoice_date'] ?? null;
        }
        return $purchase;
    }

    /**
     * 尚未審核時 可修改的欄位
     **** 尚未審核時 可修改任一欄位，所以檢查時，要一併同時檢查另一個method verifyPcsApprovedCanEditData 核可否決後 可修改的欄位 物件才會知道有改那些東西 ***
     */
    public static function verifyPcsNormalCanEditData($purchase, $tax, array $purchaseReq, array $purchasePayReq)
    {
        if (null != $purchase && null != $purchaseReq && null != $purchasePayReq) {
            $purchase->audit_status = $purchaseReq['audit_status'];
            $purchase->logistics_price = $purchasePayReq['logistics_price'] ?? 0;
            $purchase->logistics_memo = $purchasePayReq['logistics_memo'] ?? null;
        }
        return $purchase;
    }

    //刪除
    public static function del($id, $operator_user_id, $operator_user_name) {
        //判斷若有入庫、付款單 則不可刪除
        $returnMsg = [];
        $purchase = Purchase::where('id', '=', $id)->get()->first();
        if (AuditStatus::approved()->value == $purchase->audit_status) {
            return ['success' => 0, 'error_msg' => '已審核無法刪除'];
        }
        $inbounds = PurchaseInbound::purchaseInboundList($id)->get()->toArray();
        $payingOrderList = PayingOrder::getPayingOrdersWithPurchaseID($id)->get();
        if (null != $inbounds && 0 < count($inbounds)) {
            return ['success' => 0, 'error_msg' => '已入庫無法刪除'];
        } else if (null != $payingOrderList && 0 < count($payingOrderList)) {
            return ['success' => 0, 'error_msg' => '已有付款單無法刪除'];
        } else {
            return DB::transaction(function () use ($id, $operator_user_id, $operator_user_name
            ) {
                $rePcsLSC = PurchaseLog::stockChange($id, null, Event::purchase()->value, $id, LogEventFeature::del()->value, null, null, null, null, null, $operator_user_id, $operator_user_name);
                if ($rePcsLSC['success'] == 0) {
                    DB::rollBack();
                    return $rePcsLSC;
                }
                Purchase::where('id', '=', $id)->delete();
                return ['success' => 1, 'error_msg' => ""];
            });
        }
    }

    //結案
    public static function close($id, $operator_user_id, $operator_user_name) {
        $currDate = date('Y-m-d H:i:s');
        Purchase::where('id', $id)->update(['close_date' => $currDate]);
        PurchaseInbound::where('event', Event::purchase()->value)
            ->where('event_id', '=', $id)
            ->whereNull('deleted_at')
            ->update([ 'close_date' => $currDate ]);

        PurchaseLog::stockChange($id, null, Event::purchase()->value, $id, LogEventFeature::close()->value, null, null, null, null, null, $operator_user_id, $operator_user_name);
    }

    //起日 訖日 是否含已結單 發票號碼
    public static function getPurchaseList($sDate = null, $eDate = null, $hasClose = false, $invoiceNum = null)
    {
        $result = DB::table('pcs_purchase as purchase')
            ->select('purchase.id'
                , 'purchase.invoice_num as invoice_num'
                , 'purchase.pay_type as pay_type'
                , 'purchase_user_name as user_name'
                , 'supplier_name as supplier_name'
                , 'supplier_nickname as supplier_nickname'
            )
            ->selectRaw('DATE_FORMAT(purchase.close_date,"%Y-%m-%d") as close_date')
            ->whereNull('purchase.deleted_at');

        if ($sDate && $eDate) {
            $result->whereBetween('purchase.created_at', [date((string) $sDate), date((string) $eDate)]);
        }
        if ($invoiceNum) {
            $result->Where('invoice_num', 'like', "%{$invoiceNum}%");
        }
        //是否含有結單資料
        if (false == $hasClose) {
            $result->whereNull('purchase.close_date');
        }

        return $result;
    }

    public static function getPurchase($id)
    {
        $result = DB::table('pcs_purchase as purchase')
            ->select('purchase.id'
                , 'purchase.sn as purchase_sn'
                , 'purchase.invoice_num as invoice_num'
                , 'purchase.pay_type as pay_type'
                , 'purchase.purchase_user_id as user_id'
                , 'purchase.purchase_user_name as user_name'
                , 'purchase.supplier_id as supplier_id'
                , 'purchase.supplier_name as supplier_name'
                , 'purchase.supplier_nickname as supplier_nickname'
                , 'purchase.supplier_sn as supplier_sn'

                , 'purchase.has_tax as has_tax'
                , 'purchase.logistics_price as logistics_price'
                , 'purchase.logistics_memo as logistics_memo'
                , 'purchase.audit_status as audit_status'
                , 'purchase.audit_user_id as audit_user_id'
                , 'purchase.audit_user_name as audit_user_name'
            )
            ->selectRaw('DATE_FORMAT(purchase.close_date,"%Y-%m-%d") as close_date')
            ->selectRaw('DATE_FORMAT(purchase.audit_date,"%Y-%m-%d") as audit_date')
            ->selectRaw('DATE_FORMAT(purchase.scheduled_date,"%Y-%m-%d") as scheduled_date')
            ->selectRaw('DATE_FORMAT(purchase.invoice_date,"%Y-%m-%d") as invoice_date')
            ->whereNull('purchase.deleted_at')
            ->where('purchase.id', '=', $id);

        return $result;
    }

}
