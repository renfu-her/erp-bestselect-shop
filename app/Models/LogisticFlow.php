<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LogisticFlow extends Model
{
    use HasFactory;
    protected $table = 'dlv_logistic_flow';
    protected $guarded = [];

    public static function changeDeliveryStatus($delivery_id, $id)
    {
        $logistic_status = DB::table('dlv_logistic_status')->where('id', $id)->get()->first();

        Delivery::where('id', $delivery_id)->update([
            'logistic_status' => $logistic_status->title,
            'logistic_status_id' => $logistic_status->id,
        ]);

        self::create([
            'delivery_id' => $delivery_id,
            'status_id' => $logistic_status->id,
            'status' => $logistic_status->title,
            'status_code' => $logistic_status->code,
        ]);
    }
}
