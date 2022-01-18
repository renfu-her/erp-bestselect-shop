<?php

namespace App\Models;

use App\Enums\Purchase\LogFeature;
use App\Enums\Purchase\LogFeatureEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Purchase extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'pcs_purchase';
    protected $guarded = [];

    public static function createPurchase($supplier_id, $supplier_name, $supplier_nickname, $purchase_user_id, $purchase_user_name, $scheduled_date, $memo = null)
    {
        return DB::transaction(function () use ($supplier_id,
            $supplier_name,
            $supplier_nickname,
            $purchase_user_id,
            $purchase_user_name,
            $scheduled_date,
            $memo
            ) {

            $sn = "B" . date("ymd") . str_pad((self::whereDate('created_at', '=', date('Y-m-d'))
                        ->withTrashed()
                        ->get()
                        ->count()) + 1, 3, '0', STR_PAD_LEFT);

            $id = self::create([
                "sn" => $sn,
                'supplier_id' => $supplier_id,
                'supplier_name' => $supplier_name,
                'supplier_nickname' => $supplier_nickname,
                'purchase_user_id' => $purchase_user_id,
                'purchase_user_name' => $purchase_user_name,
                'scheduled_date' => $scheduled_date,
                'memo' => $memo,
            ])->id;

            PurchaseLog::stockChange($id, null, LogFeature::purchase()->value, $id, LogFeatureEvent::pcs_add()->value, null, null, $purchase_user_id, $purchase_user_name);

            return $id;
        });
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
                , 'purchase.close_date as close_date'
                , 'purchase_user_id as user_id'
                , 'purchase_user_name as user_name'
                , 'supplier_id as supplier_id'
                , 'supplier_name as supplier_name'
                , 'supplier_nickname as supplier_nickname'
            )
            ->selectRaw('DATE_FORMAT(purchase.scheduled_date,"%Y-%m-%d") as scheduled_date')
            ->whereNull('purchase.deleted_at')
            ->where('purchase.id', '=', $id);

        return $result;
    }

}
