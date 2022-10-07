<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Arr;

class StituteOrderItem extends Model
{
    use HasFactory;

    protected $table = 'acc_stitute_order_items';
    protected $guarded = [];


    public static function update_stitute_order_item($parm)
    {
        $update = [];
        if(Arr::exists($parm, 'note')){
            $update['memo'] = $parm['note'];
        }
        if(Arr::exists($parm, 'po_note')){
            $update['po_note'] = $parm['po_note'];
        }

        self::where('id', $parm['stitute_order_item_id'])->update($update);
    }
}
