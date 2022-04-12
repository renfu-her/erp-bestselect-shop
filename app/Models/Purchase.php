<?php

namespace App\Models;

use App\Enums\Delivery\Event;
use App\Enums\Purchase\LogEvent;
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

            $rePcsLSC = PurchaseLog::stockChange($id, null, LogEvent::purchase()->value, $id, LogEventFeature::pcs_add()->value, null, null, $purchase_user_id, $purchase_user_name);
            if ($rePcsLSC['success'] == 0) {
                DB::rollBack();
                return $rePcsLSC;
            }
            return ['success' => 1, 'error_msg' => "", 'id' => $id];
        });
    }

    public static function checkToUpdatePurchaseData($id, array $purchaseReq, string $changeStr, $operator_user_id, $operator_user_name, $tax, array $purchasePayReq
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
            )
            ->selectRaw('DATE_FORMAT(scheduled_date,"%Y-%m-%d") as scheduled_date')
            ->get()->first();

        $purchase->supplier_id = $purchaseReq['supplier'];
        $purchase->supplier_sn = $purchaseReq['supplier_sn'] ?? null;
        $purchase->scheduled_date = $purchaseReq['scheduled_date'];
        $purchase->has_tax = $tax;
        $purchase->logistics_price = $purchasePayReq['logistics_price'] ?? 0;
        $purchase->logistics_memo = $purchasePayReq['logistics_memo'] ?? null;
        $purchase->invoice_num = $purchasePayReq['invoice_num'] ?? null;
        $purchase->invoice_date = $purchasePayReq['invoice_date'] ?? null;

        return DB::transaction(function () use ($purchase, $id, $purchaseReq, $changeStr, $operator_user_id, $operator_user_name, $tax, $purchasePayReq
        ) {
            if ($purchase->isDirty()) {
                foreach ($purchase->getDirty() as $key => $val) {
                    $event = '';
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
                    } else if($key == 'logistics_price') {
                        $event = '修改物流費用';
                    } else if($key == 'logistics_memo') {
                        $event = '修改物流備註';
                    } else if($key == 'invoice_num') {
                        $event = '修改發票號碼';
                    } else if($key == 'invoice_date') {
                        $event = '修改發票日期';
                    }
                    $changeStr .= ' ' . $key . ' change to ' . $val;
                    $rePcsLSC = PurchaseLog::stockChange($id, null, LogEvent::purchase()->value, $id, LogEventFeature::pcs_change_data()->value, null, $event, $operator_user_id, $operator_user_name);
                    if ($rePcsLSC['success'] == 0) {
                        DB::rollBack();
                        return $rePcsLSC;
                    }
                }
                Purchase::where('id', $id)->update([
                    "supplier_id" => $purchaseReq['supplier'],
                    "supplier_sn" => $purchaseReq['supplier_sn'],
                    "scheduled_date" => $purchaseReq['scheduled_date'],
                    "has_tax" => $tax,
                    'logistics_price' => $purchasePayReq['logistics_price'] ?? 0,
                    'logistics_memo' => $purchasePayReq['logistics_memo'] ?? null,
                    'invoice_num' => $purchasePayReq['invoice_num'] ?? null,
                    'invoice_date' => $purchasePayReq['invoice_date'] ?? null,
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
            $rePcsLSC = PurchaseLog::stockChange($id, null, LogEvent::purchase()->value, $id, LogEventFeature::pcs_del()->value, null, null, $operator_user_id, $operator_user_name);
            if ($rePcsLSC['success'] == 0) {
                DB::rollBack();
                return $rePcsLSC;
            }
            Purchase::where('id', '=', $id)->delete();
            return ['success' => 1, 'error_msg' => ""];
        });
    }

    //結案
    public static function close($id, $operator_user_id, $operator_user_name) {
        $currDate = date('Y-m-d H:i:s');
        Purchase::where('id', $id)->update(['close_date' => date('Y-m-d H:i:s')]);
        PurchaseInbound::where('event', Event::purchase()->value)
            ->where('event_id', '=', $id)
            ->whereNull('deleted_at')
            ->update([ 'close_date' => $currDate ]);

        PurchaseLog::stockChange($id, null, LogEvent::purchase()->value, $id, LogEventFeature::pcs_close()->value, null, null, $operator_user_id, $operator_user_name);
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
            )
            ->selectRaw('DATE_FORMAT(purchase.close_date,"%Y-%m-%d") as close_date')
            ->selectRaw('DATE_FORMAT(purchase.scheduled_date,"%Y-%m-%d") as scheduled_date')
            ->selectRaw('DATE_FORMAT(purchase.invoice_date,"%Y-%m-%d") as invoice_date')
            ->whereNull('purchase.deleted_at')
            ->where('purchase.id', '=', $id);

        return $result;
    }

}
