<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DlvBackOther extends Model
{
    use HasFactory;
    protected $table = 'dlv_back_others';
    protected $guarded = [];
    public $timestamps = true;

    public static function getDataWithDeliveryID($delivery_id) {
        $result = DB::table(app(DlvBackOther::class)->getTable(). ' as dlv_backoth')
            ->where('dlv_backoth.delivery_id', '=', $delivery_id)
            ->select('dlv_backoth.id'
                , 'dlv_backoth.type'
                , 'dlv_backoth.title'
                , 'dlv_backoth.price'
                , 'dlv_backoth.qty'
                , 'dlv_backoth.memo'
            );
        return $result;
    }

}
