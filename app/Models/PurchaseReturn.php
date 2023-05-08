<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\DB;

use App\Enums\Purchase\ReturnStatus;

class PurchaseReturn extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'pcs_purchase_return';
    protected $guarded = [];


    public static function return_list($purchase_id = null)
    {
        $re = DB::table('pcs_purchase_return as return')
            ->leftJoin(DB::raw('(
                SELECT items.return_id,
                    COUNT(items.return_id) AS return_main_item_num
                FROM pcs_purchase_return_items AS items
                WHERE items.qty > 0 AND
                    items.show = 1 AND
                    items.type = 0
                GROUP BY items.return_id
                ) AS r_items'), function ($join){
                    $join->on('r_items.return_id', '=', 'return.id');
            })
            ->whereNull('return.deleted_at')
            ->select(
                'return.*',
                DB::raw('IFNULL(r_items.return_main_item_num, 0) AS return_main_item_num')
            );

        if ($purchase_id) {
            $re->where('return.purchase_id', '=', $purchase_id);
        }

        return $re;
    }

    public static function change_status($id, ReturnStatus $status = null)
    {
        $target = self::where('id', $id);

        if ($status) {
            $target->update([
                'status' => $status->value,
                // 'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public static function delete_return($id)
    {
        $target = self::findOrFail($id);
        self::change_status($target->id, ReturnStatus::del_return());

        $target->delete();

        return $target;
    }
}
