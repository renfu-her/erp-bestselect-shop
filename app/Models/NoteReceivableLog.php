<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

use App\Enums\Received\ChequeStatus;

class NoteReceivableLog extends Model
{
    use HasFactory;

    protected $table = 'acc_received_cheque_log';
    protected $guarded = [];


    public static function create_cheque_log($cheque_id, $status_code)
    {
        self::create([
            'cheque_id'=>$cheque_id,
            'status_code'=>$status_code,
            'status'=>ChequeStatus::getDescription($status_code),
        ]);
    }


    public static function reverse_cheque_status($cheque_id)
    {
        // skip(1)->first()
        $target = NoteReceivableLog::where('cheque_id', $cheque_id)->where('status_code', '!=', 'cashed')->orderBy('created_at', 'desc')->first();
        $cheque = DB::table('acc_received_cheque')->where('id', $cheque_id)->first();
        dd($cheque);
        if($target && $cheque){
            $cheque->update([
                'status_code'=>$target->status_code,
                'status'=>$target->status,

                'amt_net'=>0,
                'note_receivable_order_id'=>null,
                'sn'=>null,
                'updated_at'=>date("Y-m-d H:i:s"),
            ]);

            self::create_cheque_log($cheque_id, $target->status_code);

            NoteReceivableOrder::store_note_receivable_order($cheque->cashing_date);
        }

        return $target;
    }
}
