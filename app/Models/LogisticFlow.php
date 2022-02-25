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

    public static function changeDeliveryStatus($delivery_id, $code)
    {
        $code = DB::table('dlv_logistic_status')->where('code', $code)->get()->first();

        Delivery::where('id', $delivery_id)->update([
            'logistic_status' => $code->title,
            'logistic_status_code' => $code->code,
        ]);

        self::create([
            'delivery_id' => $delivery_id,
            'status' => $code->title,
            'status_code' => $code->code,
        ]);
    }
}
