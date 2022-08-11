<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\DB;

use App\Enums\Received\ChequeStatus;

class NoteReceivableLog extends Model
{
    use HasFactory,SoftDeletes;

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

    // NoteReceivableLog::orderBy('id', 'desc')->skip(1)->first();
}
