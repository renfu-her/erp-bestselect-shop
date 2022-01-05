<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PurchaseInbound extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pcs_purchase_inbound';
    protected $guarded = [];

    public static function createInbound($purchase_id, $expiry_date = null, $status = 0, $inbound_date = null, $inbound_num = 0, $error_num = 0, $depot_id = null, $inbound_user_id = null, $close_date = null, $memo = null)
    {
        $id = self::create([
            "purchase_id" => $purchase_id,
            "expiry_date" => $expiry_date,
            "status" => $status,
            "inbound_date" => $inbound_date,
            "inbound_num" => $inbound_num,
            "error_num" => $error_num,
            "depot_id" => $depot_id,
            "inbound_user_id" => $inbound_user_id,
            "close_date" => $close_date,
            "memo" => $memo
        ])->id;

        return $id;
    }

    //入庫 更新資料
    public static function updateInbound($id, $expiry_date = null, $status = 0, $inbound_date = null, $inbound_num = 0, $error_num = 0, $depot_id = null, $inbound_user_id = null, $close_date = null, $sale_num = 0, $memo = null)
    {
        return DB::transaction(function () use (
            $id,
            $expiry_date,
            $status,
            $inbound_date,
            $inbound_num,
            $error_num,
            $depot_id,
            $inbound_user_id,
            $close_date,
            $sale_num,
            $memo
        ) {
            self::where('id', '=', $id)->update([
                'expiry_date' => $expiry_date,
                'status' => $status,
                'inbound_date' => $inbound_date,
                'inbound_num' => $inbound_num,
                'error_num' => $error_num,
                'depot_id' => $depot_id,
                'inbound_user_id' => $inbound_user_id,
                'close_date' => $close_date,
                'sale_num' => $sale_num,
                'memo' => $memo

            ]);

            return $id;
        });
    }
    //售出 更新資料
    public static function sellInbound($id, $sale_num = 0)
    {
        return DB::transaction(function () use (
            $id,
            $sale_num
        ) {
            self::where('id', '=', $id)->update([
                'sale_num' => DB::raw('sale_num + '. $sale_num ),
            ]);

            return $id;
        });
    }

}
