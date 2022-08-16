<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

use App\Enums\Supplier\Payment;
use App\Enums\Payable\ChequeStatus;


class PayableCheque extends Model
{
    use HasFactory;

    protected $table = 'acc_payable_cheque';
    protected $guarded = [];


    public static function storePayableCheque($req)
    {
        $payableData = self::create([
            'ticket_number' => $req['cheque']['ticket_number'],
            'due_date' => $req['cheque']['due_date'],
            'cashing_date' => $req['cheque']['cashing_date'],
            'status_code' => $req['cheque']['status_code'],
            'status' => ChequeStatus::getDescription($req['cheque']['status_code']),
            'amt_net'=> ($req['cheque']['cashing_date'] && $req['cheque']['status_code'] == 'cashed') ? $req['tw_price'] : 0,
        ]);

        if($req['cheque']['status_code']){
            NotePayableLog::create_cheque_log($payableData->id, $req['cheque']['status_code']);

            if($req['cheque']['cashing_date'] && $req['cheque']['status_code'] == 'cashed'){
                $note_payable_order = NotePayableOrder::store_note_payable_order($req['cheque']['cashing_date']);

                self::find($payableData->id)->update([
                    'note_payable_order_id'=>$note_payable_order->id,
                    'sn'=>$note_payable_order->sn,
                ]);
            }
        }

        AccountPayable::create([
            'pay_order_type' => 'App\Models\PayingOrder',
            'pay_order_id' => $req['pay_order_id'],
            'acc_income_type_fk' => Payment::Cheque,
            'payable_type' => 'App\Models\PayableCheque',
            'payable_id' => $payableData->id,
            'all_grades_id' => $req['cheque']['grade_id_fk'],
            'tw_price' => $req['tw_price'],
            //            'payable_status' => $req['payable_status'],
            'payment_date' => $req['payment_date'],
            'accountant_id_fk' => Auth::user()->id,
            'summary' => $req['summary'] ?? '',
            'note' => $req['note'] ?? '',
        ]);
    }
}
