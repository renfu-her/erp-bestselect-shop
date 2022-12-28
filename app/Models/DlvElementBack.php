<?php

namespace App\Models;

use App\Helpers\IttmsDBB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DlvElementBack extends Model
{
    use HasFactory;
    protected $table = 'dlv_element_back';
    protected $guarded = [];
    public $timestamps = true;

    public static function createData($delivery_id, $bac_papa_id, $rcv_depot_id, $qty, $memo = null) {
        return IttmsDBB::transaction(function () use ($delivery_id, $bac_papa_id, $rcv_depot_id, $qty, $memo) {
            $id = self::create([
                'delivery_id' => $delivery_id,
                'bac_papa_id' => $bac_papa_id,
                'rcv_depot_id' => $rcv_depot_id,
                'qty' => $qty,
                'memo' => $memo,
            ])->id;
            return ['success' => 1, 'error_msg' => "", 'id' => $id];
        });
    }

}
