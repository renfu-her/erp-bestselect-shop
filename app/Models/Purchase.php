<?php

namespace App\Models;

use App\Enums\Consignment\AuditStatus;
use App\Enums\Delivery\Event;
use App\Enums\Purchase\LogEventFeature;
use App\Enums\StockEvent;
use App\Helpers\IttmsDBB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
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

    public static function createPurchase($sn = null, $supplier_id, $supplier_name, $supplier_nickname, $supplier_sn = null, $purchase_user_id, $purchase_user_name
        , $scheduled_date, $estimated_depot_id = null, $estimated_depot_name = null
        , $logistics_price = 0, $logistics_memo = null, $invoice_num = null, $invoice_date = null,$note=null
    )
    {
        return IttmsDBB::transaction(function () use (
            $sn,
            $supplier_id,
            $supplier_name,
            $supplier_nickname,
            $supplier_sn,
            $purchase_user_id,
            $purchase_user_name,
            $scheduled_date,
            $estimated_depot_id,
            $estimated_depot_name,
            $logistics_price,
            $logistics_memo,
            $invoice_num,
            $invoice_date,
            $note
            ) {

            //判斷若無sn 則產生新的
            if(false == isset($sn)) {
                $sn = Sn::createSn('purchase', 'B', 'ymd', 4);
            }


            $id = self::create([
                "sn" => $sn,
                'supplier_id' => $supplier_id,
                'supplier_name' => $supplier_name,
                'supplier_nickname' => $supplier_nickname,
                'supplier_sn' => $supplier_sn ?? null,
                'purchase_user_id' => $purchase_user_id,
                'purchase_user_name' => $purchase_user_name,
                'scheduled_date' => $scheduled_date,
                'estimated_depot_id' => $estimated_depot_id ?? null,
                'estimated_depot_name' => $estimated_depot_name ?? null,
                'logistics_price' => $logistics_price ?? 0,
                'logistics_memo' => $logistics_memo ?? null,
                'invoice_num' => $invoice_num ?? null,
                'invoice_date' => $invoice_date ?? null,
                'note' => $note
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
                , 'estimated_depot_id'
            )
            ->get()->first();

        //判斷在尚未審核時才能編輯的資料是否被編輯過
        $purchase = Purchase::verifyPcsNormalCanEditData($purchase, $tax, $purchaseReq, $purchasePayReq);
        return $purchase;
    }

    public static function checkToUpdatePurchaseData($id, array $purchaseReq, $operator_user_id, $operator_user_name, $tax, array $purchasePayReq,$note = null
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

        return IttmsDBB::transaction(function () use ($purchase, $id, $purchaseReq, $operator_user_id, $operator_user_name, $tax, $purchasePayReq, $orign_audit_status,$note
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
                    } else if($key == 'estimated_depot_id') {
                        $event = '修改預計入庫倉';
                    }

                    $rePcsLSC = PurchaseLog::stockChange($id, null, Event::purchase()->value, $id, LogEventFeature::change_data()->value, null, null, $event, null, null, $operator_user_id, $operator_user_name);
                    if ($rePcsLSC['success'] == 0) {
                        DB::rollBack();
                        return $rePcsLSC;
                    }
                }

                $estimated_depot_id = null;
                $estimated_depot_name = null;
                if (isset($purchaseReq['estimated_depot_id'])) {
                    $depot = Depot::where('id', '=', $purchaseReq['estimated_depot_id'])->first();
                    if (isset($depot)) {
                        $estimated_depot_id = $depot->id;
                        $estimated_depot_name = $depot->name;
                    }
                }

                $updArr = [];
                if (AuditStatus::unreviewed()->value == $orign_audit_status) {
                    $updArr = [
//                        "supplier_id" => $purchaseReq['supplier'],
                        "supplier_sn" => $purchaseReq['supplier_sn'],
                        "scheduled_date" => $purchaseReq['scheduled_date'],
                        "estimated_depot_id" => $estimated_depot_id,
                        "estimated_depot_name" => $estimated_depot_name,
                        "has_tax" => $tax,
                        'invoice_num' => $purchasePayReq['invoice_num'] ?? null,
                        'invoice_date' => $purchasePayReq['invoice_date'] ?? null,

                        'logistics_price' => $purchasePayReq['logistics_price'] ?? 0,
                        'logistics_memo' => $purchasePayReq['logistics_memo'] ?? null,
                    ];

                    $curr_date = date('Y-m-d H:i:s');
                    //判斷變更審核狀態 才寫入審核人員
                    if ($purchase->getRawOriginal('audit_status') != $purchaseReq['audit_status']) {
                        $updArr['audit_date'] = $curr_date;
                        $updArr['audit_user_id'] = $operator_user_id;
                        $updArr['audit_user_name'] = $operator_user_name;
                        $updArr['audit_status'] = $purchaseReq['audit_status'] ?? App\Enums\Consignment\AuditStatus::unreviewed()->value;
                    }
                } else {
                    $updArr = [
                        "supplier_sn" => $purchaseReq['supplier_sn'] ?? null,
                        "scheduled_date" => $purchaseReq['scheduled_date'],
                        "has_tax" => $tax,
                        'invoice_num' => $purchasePayReq['invoice_num'] ?? null,
                        'invoice_date' => $purchasePayReq['invoice_date'] ?? null,
                    ];
                }
                $updArr['note'] = $note;
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
            $purchase->audit_status = intval($purchaseReq['audit_status'], (int)AuditStatus::unreviewed()->value);
            $purchase->estimated_depot_id = ($purchaseReq['estimated_depot_id']?? null);
            $purchase->logistics_price = intval($purchasePayReq['logistics_price'] ?? 0, 0);
            $purchase->logistics_memo = $purchasePayReq['logistics_memo'] ?? null;
        }
        return $purchase;
    }

    //刪除
    public static function del($id, $operator_user_id, $operator_user_name) {
        //判斷若有審核、付款單 則不可刪除
        $returnMsg = [];
        $purchase = Purchase::where('id', '=', $id)->get()->first();
        if (AuditStatus::approved()->value == $purchase->audit_status) {
            return ['success' => 0, 'error_msg' => '已審核無法刪除'];
        }
        return self::delAndRelatedData($id, $operator_user_id, $operator_user_name);
    }

    //刪除採購單 並刪除 入庫單
    public static function delAndRelatedData($id, $operator_user_id, $operator_user_name) {
        $purchase = Purchase::where('id', '=', $id)->get()->first();
        if (false == isset($purchase)) {
            return ['success' => 0, 'error_msg' => '查無採購單'];
        }
        $dlv_inbounds = PurchaseInbound::deliveryPcsInboundList($id)->get()->toArray();
        $payingOrderList = PayingOrder::getPayingOrdersWithPurchaseID($id)->get();
        if (null != $dlv_inbounds && 0 < count($dlv_inbounds)) {
            return ['success' => 0, 'error_msg' => '已出貨無法刪除'];
        } else if (null != $payingOrderList && 0 < count($payingOrderList)) {
            return ['success' => 0, 'error_msg' => '已有付款單無法刪除'];
        } else {
            DB::beginTransaction();
            try {
                $rePcsLSC = PurchaseLog::stockChange($id, null, Event::purchase()->value, $id, LogEventFeature::del()->value, null, null, '刪除採購單', null, null, $operator_user_id, $operator_user_name);
                if ($rePcsLSC['success'] == 0) {
                    DB::rollBack();
                    return $rePcsLSC;
                }
                //找出每筆入庫單
                // 退回入庫數量
                // 判斷若為理貨倉 需紀錄可售數量
                $inboundList = PurchaseInbound::getInboundList(['event' => Event::purchase()->value, 'event_id' => $id])
                    ->get()->toArray();
                if (0 < count($inboundList)) {
                    foreach ($inboundList as $key_ib => $val_ib) {
                        //$can_tally = Depot::can_tally($val_ib->depot_id);
                        $can_tally = true;

                        $inbound_num = $val_ib->inbound_num?? 0 - $val_ib->shipped_num?? 0;
                        PcsStatisInbound::updateData($val_ib->product_style_id, $val_ib->depot_id, $inbound_num * -1);
                        $updateLog = PurchaseInbound::addLogAndUpdateStock(LogEventFeature::purchase_del()->value, $val_ib->inbound_id
                            , $val_ib->event, $val_ib->event_id, $val_ib->event_item_id
                            , $val_ib->product_style_id
                            , $val_ib->inbound_prd_type, $val_ib->product_title, $val_ib->inbound_num * -1, $can_tally, '刪除採購單', StockEvent::purchase_del()->value, '刪除採購單', $operator_user_id, $operator_user_name);
                        if ($updateLog['success'] == 0) {
                            DB::rollBack();
                            return $updateLog;
                        }
                    }
                }

                Purchase::where('id', '=', $id)->delete();
                PurchaseItem::where('purchase_id', '=', $id)->delete();
                PurchaseInbound::where('event_id', '=', $id)->where('event', '=', Event::purchase()->value)->delete();

                DB::commit();
                return ['success' => 1, 'error_msg' => ""];
            } catch (\Exception $e) {
                DB::rollback();
                return ['success' => 0, 'error_msg' => $e->getMessage()];
            }
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
                , 'purchase.estimated_depot_id as estimated_depot_id'
                , 'purchase.estimated_depot_name as estimated_depot_name'
                , 'purchase.note as note'
                , 'purchase.has_tax as has_tax'
                , 'purchase.logistics_price as logistics_price'
                , 'purchase.logistics_memo as logistics_memo'
                , 'purchase.logistics_ro_note as logistics_ro_note'
                , 'purchase.logistics_po_note as logistics_po_note'
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


    public static function purchase_item($purchase_id = null)
    {
        $query = DB::table('pcs_purchase as purchase')
            ->leftJoin(DB::raw('(
                SELECT
                    pcs_purchase_items.purchase_id,
                    SUM(pcs_purchase_items.price) AS total_price,
                    CONCAT(\'[\', GROUP_CONCAT(\'{
                        "id":"\', pcs_purchase_items.id, \'",
                        "sku":"\', pcs_purchase_items.sku, \'",
                        "product_title":"\', pcs_purchase_items.title, \'",
                        "price":"\', pcs_purchase_items.price / pcs_purchase_items.num, \'",
                        "qty":"\', pcs_purchase_items.num, \'",
                        "total_price":"\', pcs_purchase_items.price, \'",
                        "memo":"\', COALESCE(replace(pcs_purchase_items.memo, \'"\', \'\\\"\'), ""), \'",
                        "ro_note":"\', COALESCE(pcs_purchase_items.ro_note, ""), \'",
                        "po_note":"\', COALESCE(pcs_purchase_items.po_note, ""), \'",
                        "taxation":"\', product.has_tax, \'"
                    }\' ORDER BY pcs_purchase_items.id), \']\') AS items
                FROM pcs_purchase_items
                LEFT JOIN prd_product_styles ON prd_product_styles.id = pcs_purchase_items.product_style_id
                LEFT JOIN prd_products AS product ON product.id = prd_product_styles.product_id
                WHERE (product.deleted_at IS NULL AND pcs_purchase_items.deleted_at IS NULL)
                GROUP BY pcs_purchase_items.purchase_id
                ) AS purchase_table'), function ($join){
                    $join->on('purchase_table.purchase_id', '=', 'purchase.id');
            })
            ->leftJoin('prd_suppliers as supplier', 'supplier.id', '=', 'purchase.supplier_id')
            ->leftJoin('pcs_paying_orders AS po_deposit', function ($join) {
                $join->on('po_deposit.source_id', '=', 'purchase.id');
                $join->where([
                    'po_deposit.source_type'=>app(self::class)->getTable(),
                    'po_deposit.source_sub_id'=>null,
                    'po_deposit.type'=>0,
                    'po_deposit.deleted_at'=>null,
                ]);
            })
            ->leftJoin('pcs_paying_orders AS po_balance', function ($join) {
                $join->on('po_balance.source_id', '=', 'purchase.id');
                $join->where([
                    'po_balance.source_type'=>app(self::class)->getTable(),
                    'po_balance.source_sub_id'=>null,
                    'po_balance.type'=>1,
                    'po_balance.deleted_at'=>null,
                ]);
            })

            ->where(function ($q) use ($purchase_id) {
                if($purchase_id){
                    if(gettype($purchase_id) == 'array') {
                        $q->whereIn('purchase.id', $purchase_id);
                    } else {
                        $q->where('purchase.id', $purchase_id);
                    }
                }

                $q->where('purchase.deleted_at', null);
            })

            ->select(
                'purchase.id AS purchase_id',
                'purchase.sn AS purchase_sn',
                'purchase.audit_status AS audit_status',
                'purchase.purchase_user_name AS purchase_user_name',
                'purchase.logistics_price AS purchase_logistics_price',
                'purchase.logistics_memo AS purchase_logistics_memo',
                'purchase.logistics_ro_note AS purchase_logistics_ro_note',
                'purchase.logistics_po_note AS purchase_logistics_po_note',
                'purchase.audit_user_name AS purchase_audit_user_name',

                'purchase_table.items AS purchase_table_items',
                'purchase_table.total_price AS purchase_table_total_price',

                'supplier.id AS supplier_id',
                'supplier.name AS supplier_name',
                'supplier.nickname AS supplier_nickname',
                'supplier.contact_tel AS supplier_phone',
                'supplier.email AS supplier_email',
                'supplier.contact_address AS supplier_address',

                'po_deposit.id AS po_deposit_id',
                'po_deposit.sn AS po_deposit_sn',
                'po_deposit.price AS po_deposit_price',
                'po_deposit.balance_date AS po_deposit_balance_date',
                'po_deposit.summary AS po_deposit_summary',
                'po_deposit.memo AS po_deposit_memo',
                'po_deposit.payee_id AS po_deposit_payee_id',
                'po_deposit.payee_name AS po_deposit_payee_name',
                'po_deposit.payee_phone AS po_deposit_payee_phone',
                'po_deposit.payee_address AS po_deposit_payee_address',
                'po_deposit.created_at AS po_deposit_created_at',

                'po_balance.id AS po_balance_id',
                'po_balance.sn AS po_balance_sn',
                'po_balance.price AS po_balance_price',
                'po_balance.balance_date AS po_balance_balance_date',
                'po_balance.summary AS po_balance_summary',
                'po_balance.memo AS po_balance_memo',
                'po_balance.payee_id AS po_balance_payee_id',
                'po_balance.payee_name AS po_balance_payee_name',
                'po_balance.payee_phone AS po_balance_payee_phone',
                'po_balance.payee_address AS po_balance_payee_address',
                'po_balance.created_at AS po_balance_created_at'
            )
            ->orderBy('purchase.id', 'desc');

        return $query;
    }


    public static function update_logistic($parm)
    {
        $update = [];
        if(Arr::exists($parm, 'note')){
            $update['logistics_memo'] = $parm['note'];
        }
        if(Arr::exists($parm, 'ro_note')){
            $update['logistics_ro_note'] = $parm['ro_note'];
        }
        if(Arr::exists($parm, 'po_note')){
            $update['logistics_po_note'] = $parm['po_note'];
        }
        self::where('id', $parm['logistic_id'])->update($update);
    }
}
