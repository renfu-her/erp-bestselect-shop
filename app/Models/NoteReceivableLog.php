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
        $result = self::create([
            'cheque_id'=>$cheque_id,
            'status_code'=>$status_code,
            'status'=>ChequeStatus::getDescription($status_code),
        ]);

        return $result;
    }


    public static function reverse_cheque_status($cheque_id)
    {
        // orderBy('id', 'desc')->skip(1)->first()
        $target = self::where('cheque_id', $cheque_id)->where('status_code', '!=', 'cashed')->orderBy('id', 'desc')->first();
        $cheque = DB::table('acc_received_cheque')->where('id', $cheque_id)->first();

        if($cheque){
            $status_code = $target ? $target->status_code : 'received';
            $status = $target ? $target->status : '收票';

            DB::table('acc_received_cheque')->where('id', $cheque_id)->update([
                'status_code'=>$status_code,
                'status'=>$status,

                'amt_net'=>0,
                'note_receivable_order_id'=>null,
                'sn'=>null,
                'updated_at'=>date('Y-m-d H:i:s'),
            ]);

            $new_log = self::create_cheque_log($cheque_id, $status_code);

            NoteReceivableOrder::store_note_receivable_order($cheque->cashing_date);
        }

        return $new_log;
    }
}
