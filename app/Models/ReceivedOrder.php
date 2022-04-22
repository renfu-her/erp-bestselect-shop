<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\DB;

use App\Enums\Received\ReceivedMethod;

class ReceivedOrder extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'pcs_received_orders';
    protected $guarded = [];


    public static function store_received($request)
    {
        $id = null;

        switch ($request['acc_transact_type_fk']) {
            // case ReceivedMethod::Cash:

            case ReceivedMethod::Cheque:
                $id = DB::table('acc_received_cheque')->insertGetId([
                    'ticket_number'=>$request[$request['acc_transact_type_fk']]['ticket_number'],
                    'due_date'=>$request[$request['acc_transact_type_fk']]['due_date'],
                    'created_at'=>date("Y-m-d H:i:s"),
                ]);
                break;

            case ReceivedMethod::CreditCard:
                $id = DB::table('acc_received_credit')->insertGetId([
                    'created_at'=>date("Y-m-d H:i:s"),
                ]);
                break;

            case ReceivedMethod::Remittance:
                $id = DB::table('acc_received_remit')->insertGetId([
                    'remittance'=>$request[$request['acc_transact_type_fk']]['remittance'],
                    'memo'=>$request[$request['acc_transact_type_fk']]['bank_slip_name'],
                    'created_at'=>date("Y-m-d H:i:s"),
                ]);
                break;

            case ReceivedMethod::ForeignCurrency:
                $id = DB::table('acc_received_currency')->insertGetId([
                    'currency'=>$request[$request['acc_transact_type_fk']]['rate'],
                    'foreign_currency'=>$request[$request['acc_transact_type_fk']]['foreign_price'],
                    'created_at'=>date("Y-m-d H:i:s"),
                ]);
                break;

            // case ReceivedMethod::AccountsReceivable:

            // case ReceivedMethod::Other:

            // case ReceivedMethod::Refund:
        }

        return $id;
    }
}
