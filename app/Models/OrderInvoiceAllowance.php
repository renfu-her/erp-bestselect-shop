<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\DB;

class OrderInvoiceAllowance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ord_order_invoice_allowance';
    protected $guarded = [];


    public static function create_allowance($invoice_id, $parm = [])
    {
        $item_name_arr = [];
        $item_count_arr = [];
        $item_unit_arr = [];
        $item_price_arr = [];
        $item_amt_arr = [];
        $item_tax_type_arr = [];
        $item_tax_amt_arr = [];

        foreach($parm['o_title'] as $key => $value){
            $item_name_arr[] = trim(mb_substr(str_replace('|', '｜', preg_replace('/(\t|\r|\n|\r\n)+/', ' ', $parm['o_title'][$key])), 0, 30));
            $item_count_arr[] = $parm['o_qty'][$key];
            $item_unit_arr[] = '-';
            $item_price_arr[] = $parm['o_price'][$key];
            $item_amt_arr[] = $parm['o_total_price'][$key];
            $item_tax_type_arr[] = $parm['o_taxation'][$key] == 1 ? 1 : 3;
            $item_tax_amt_arr[] = $parm['o_tax_price'][$key];
        }

        $user_id = auth('user')->user() ? auth('user')->user()->id : null;
        $invoice_number = OrderInvoice::find($invoice_id)->invoice_number ;
        $merchant_order_no = OrderInvoice::find($invoice_id)->merchant_order_no ;
        $buyer_email = isset($parm['buyer_email']) ? $parm['buyer_email'] : null;

        if(count(array_unique($item_tax_type_arr)) == 1) {
            if(array_unique($item_tax_type_arr)[0] == 1) {
                $tax_type = 1;
            } else if(array_unique($item_tax_type_arr)[0] == 3) {
                $tax_type = 3;
            } else {
                $tax_type = 9;
            }

        } else {
            $tax_type = 9;
        }

        $total_amt = array_sum($item_amt_arr) + array_sum($item_tax_amt_arr);

        $item_name = implode('|', $item_name_arr);
        $item_count = implode('|', $item_count_arr);
        $item_unit = implode('|', $item_unit_arr);
        $item_price = implode('|', $item_price_arr);
        $item_amt = implode('|', $item_amt_arr);
        $item_tax_type = implode('|', $item_tax_type_arr);
        $item_tax_amt = implode('|', $item_tax_amt_arr);

        $merchant_id = null;
        $allowance_no = null;
        $remain_amt = null;
        $check_code = null;

        DB::beginTransaction();

        try {
            $inv_result = self::create([
                'invoice_id' => $invoice_id,
                'user_id' => $user_id,
                'invoice_number' => $invoice_number,
                'merchant_order_no' => $merchant_order_no,
                'buyer_email' => $buyer_email,
                'tax_type' => $tax_type,

                'item_name' => $item_name,
                'item_count' => $item_count,
                'item_unit' => $item_unit,
                'item_price' => $item_price,
                'item_amt' => $item_amt,
                'item_tax_type' => $item_tax_type,
                'item_tax_amt' => $item_tax_amt,
                'total_amt' => $total_amt,

                'r_status' => null,
                'r_msg' => null,
                'r_json' => null,
                'merchant_id' => $merchant_id,
                'allowance_no' => $allowance_no,
                'remain_amt' => $remain_amt,
                'check_code' => $check_code,
            ]);

            wToast(__('發票折讓新增成功'));

            DB::commit();

        } catch (\Exception $e) {
            $inv_result = null;
            // $e->getMessage();
            wToast(__('發票折讓新增失敗'), ['type'=>'danger']);
            DB::rollback();
        }

        return $inv_result;
    }
}
