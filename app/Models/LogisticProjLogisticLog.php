<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogisticProjLogisticLog extends Model
{
    use HasFactory;
    protected $table = 'dlv_logistic_proj_logistic_log';
    protected $guarded = [];
    public $timestamps = ["created_at"];

    public static function createData($logistic_id, $order_sn = null, $status, $text_request, $text_response, $user) {
        LogisticProjLogisticLog::create([
            'logistic_fk' => $logistic_id
            , 'order_sn' => $order_sn
            , 'status' => $status
            , 'text_request' => $text_request
            , 'text_response' => $text_response
            , 'create_user_fk' => $user()->id
            , 'create_user_name' => $user()->name
        ]);
    }
}
