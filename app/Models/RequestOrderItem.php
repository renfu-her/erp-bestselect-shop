<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Arr;

class RequestOrderItem extends Model
{
    use HasFactory;

    protected $table = 'acc_request_order_items';
    protected $guarded = [];


    public static function update_request_order_item($parm)
    {
        $update = [];
        if(Arr::exists($parm, 'grade_id')){
            $update['grade_id'] = $parm['grade_id'];
        }
        if(Arr::exists($parm, 'summary')){
            $update['summary'] = $parm['summary'];
        }
        if(Arr::exists($parm, 'taxation')){
            $update['taxation'] = $parm['taxation'];
        }
        if(Arr::exists($parm, 'memo')){
            $update['memo'] = $parm['memo'];
        }
        if(Arr::exists($parm, 'ro_note')){
            $update['ro_note'] = $parm['ro_note'];
        }

        self::where('id', $parm['request_order_item_id'])->update($update);
    }
}
