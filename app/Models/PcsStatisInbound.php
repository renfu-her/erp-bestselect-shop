<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PcsStatisInbound extends Model
{
    use HasFactory;
    protected $table = 'pcs_statis_inbound';
    protected $guarded = [];
    public $timestamps = false;

    public static function updateData($event, $product_style_id, $depot_id, $qty)
    {
        PcsStatisInbound::updateOrCreate([
            'event' => $event
            , 'product_style_id' => $product_style_id
            , 'depot_id' => $depot_id
        ], [
            'qty' => DB::raw("qty + ". $qty)
        ]);
    }
}
