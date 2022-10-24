<?php

namespace App\Models;

use App\Helpers\IttmsDBB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class SupplierPayment extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'prd_supplier_payments';
    protected $guarded = [];

    public static function createData($supplier_id, $paytype, $array = []) {
        $result = IttmsDBB::transaction(function () use ($supplier_id, $paytype, $array
        ) {
            $insert_arr = [];
            $insert_arr['supplier_id'] = $supplier_id;
            $insert_arr['type'] = $paytype;
            $insert_arr = self::createInsertKeyVal($insert_arr, $array, 'bank_cname');
            $insert_arr = self::createInsertKeyVal($insert_arr, $array, 'bank_code');
            $insert_arr = self::createInsertKeyVal($insert_arr, $array, 'bank_acount');
            $insert_arr = self::createInsertKeyVal($insert_arr, $array, 'bank_numer');
            $insert_arr = self::createInsertKeyVal($insert_arr, $array, 'cheque_payable');
            $insert_arr = self::createInsertKeyVal($insert_arr, $array, 'def_paytype');
            $insert_arr = self::createInsertKeyVal($insert_arr, $array, 'other');
            $id = self::create($insert_arr)->id;
            return ['success' => 1, 'id' => $id];
        });
        return $result['id'] ?? null;
    }

    private static function createInsertKeyVal($insert_arr, $orignal_arr, $key) {
        if(isset($orignal_arr[$key])) {
            $insert_arr[$key] = $orignal_arr[$key];
        }
        return $insert_arr;
    }

    public static function checkToUpdateData($supplier_id, $paytype, $array = [])
    {
        $payment = null;
        if (null != $supplier_id && null != $paytype) {
            $payment = SupplierPayment::where('supplier_id', '=', $supplier_id)
                ->where('type', '=', $paytype)
                ->get()->first();
        }

        $result = IttmsDBB::transaction(function () use ($supplier_id, $paytype, $array, $payment
        ) {
            $id = null;
            if (null == $payment) {
                $id = self::createData($supplier_id, $paytype, $array);
            } else {
                $id = SupplierPayment::where('id', $payment->id)
                    ->where('supplier_id', '=', $supplier_id)
                    ->where('type', '=', $paytype)
                    ->update([
                        'bank_cname' => $array['bank_cname'] ?? null,
                        'bank_code' => $array['bank_code'] ?? null,
                        'bank_acount' => $array['bank_acount'] ?? null,
                        'bank_numer' => $array['bank_numer'] ?? null,
                        'other' => $array['other'] ?? null,
                        'cheque_payable' => $array['cheque_payable'] ?? null
                ]);
            }
            return ['success' => 1, 'id' => $id];
        });
        return $result['id'] ?? null;
    }
}
