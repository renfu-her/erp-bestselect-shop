<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DlvBackItem extends Model
{
    use HasFactory;
    protected $table = 'dlv_back_items';
    protected $guarded = [];
    public $timestamps = true;

    public static function getDataWithDeliveryID($delivery_id) {
        $result = DB::table(app(DlvBackItem::class)->getTable(). ' as dlv_backitem')
            ->where('dlv_backitem.delivery_id', '=', $delivery_id)
            ->select('dlv_backitem.id'
                , 'dlv_backitem.type'
                , 'dlv_backitem.title'
                , 'dlv_backitem.price'
                , 'dlv_backitem.qty'
                , 'dlv_backitem.memo'
            );
        return $result;
    }

}
