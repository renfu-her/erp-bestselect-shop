<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DlvBack extends Model
{
    use HasFactory;
    protected $table = 'dlv_back';
    protected $guarded = [];
    public $timestamps = true;

    public static function getDataWithDeliveryID($delivery_id) {
        $result = DB::table(app(DlvBack::class)->getTable(). ' as dlv_back')
            ->where('dlv_back.delivery_id', $delivery_id)
            ->select(
                'dlv_back.id'
                , 'dlv_back.event_item_id'
                , 'dlv_back.product_style_id'
                , 'dlv_back.sku'
                , 'dlv_back.product_title'
                , 'dlv_back.price'
                , 'dlv_back.origin_qty'
                , 'dlv_back.qty as back_qty'
                , DB::raw('ifnull(dlv_back.bonus, "") as bonus')
                , 'dlv_back.memo'
                , 'dlv_back.show'
            );
        return $result;
    }

}
