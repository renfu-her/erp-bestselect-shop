<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Purchase extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'pcs_purchase';
    protected $guarded = [];

    public static function createPurchase($supplier_id, $purchase_id, $bank_cname, $bank_code, $bank_acount, $bank_numer, $invoice_num = '', $pay_type, $logistic_price = 0, $memo = null, $scheduled_date = null, $close_date = null)
    {
        return DB::transaction(function () use ($supplier_id,
            $purchase_id,
            $bank_cname,
            $bank_code,
            $bank_acount,
            $bank_numer,
            $invoice_num,
            $pay_type,
            $logistic_price,
            $memo,
            $scheduled_date,
            $close_date
            ) {

            $sn = "B" . date("ymd") . str_pad((self::whereDate('created_at', '=', date('Y-m-d'))
                        ->withTrashed()
                        ->get()
                        ->count()) + 1, 3, '0', STR_PAD_LEFT);

            $id = self::create([
                "sn" => $sn,
                'supplier_id' => $supplier_id,
                'purchase_id' => $purchase_id,
                'bank_cname' => $bank_cname,
                'bank_code' => $bank_code,
                'bank_acount' => $bank_acount,
                'bank_numer' => $bank_numer,
                'invoice_num' => $invoice_num,
                'pay_type' =>$pay_type,
                'logistic_price' => $logistic_price,
                'memo' => $memo,
                'scheduled_date' => $scheduled_date,
                'close_date' => $close_date,
            ])->id;

            return $id;
        });
    }

    //起日 訖日 是否含已結單 發票號碼
    public static function getPurchaseList($sDate = null, $eDate = null, $hasClose = false, $invoiceNum = null)
    {
        $result = DB::table('pcs_purchase as purchase')
            ->leftJoin('usr_users as users', 'users.id', '=', 'purchase.purchase_id')
            ->leftJoin('prd_suppliers as suppliers', 'suppliers.id', '=', 'purchase.supplier_id')

            ->select('purchase.id'
                , 'purchase.bank_cname as bank_cname'
                , 'purchase.bank_code as bank_code'
                , 'purchase.bank_acount as bank_acount'
                , 'purchase.bank_numer as bank_numer'
                , 'purchase.invoice_num as invoice_num'
                , 'purchase.pay_type as pay_type'
                , 'purchase.logistic_price as logistic_price'
                , 'users.name as user_name'
                , 'suppliers.name as supplier_name'
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
            ->leftJoin('usr_users as users', 'users.id', '=', 'purchase.purchase_id')
            ->leftJoin('prd_suppliers as suppliers', 'suppliers.id', '=', 'purchase.supplier_id')

            ->select('purchase.id'
                , 'purchase.bank_cname as bank_cname'
                , 'purchase.bank_code as bank_code'
                , 'purchase.bank_acount as bank_acount'
                , 'purchase.bank_numer as bank_numer'
                , 'purchase.invoice_num as invoice_num'
                , 'purchase.pay_type as pay_type'
                , 'purchase.logistic_price as logistic_price'
                , 'purchase.close_date as close_date'
                , 'users.id as user_id'
                , 'users.name as user_name'
                , 'suppliers.id as supplier_id'
                , 'suppliers.name as supplier_name'
            )
            ->selectRaw('DATE_FORMAT(purchase.scheduled_date,"%Y-%m-%d") as scheduled_date')
            ->whereNull('purchase.deleted_at')
            ->where('purchase.id', '=', $id);

        return $result;
    }

}
