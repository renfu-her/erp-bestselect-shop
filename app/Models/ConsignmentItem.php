<?php

namespace App\Models;

use App\Enums\Purchase\LogEvent;
use App\Enums\Purchase\LogEventFeature;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ConsignmentItem extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'csn_consignment_items';
    protected $guarded = [];

    //建立採購單
    public static function createData(array $newData, $operator_user_id, $operator_user_name)
    {
        if (isset($newData['consignment_id'])
            && $newData['product_style_id']
            && $newData['title']
            && $newData['num']
            && $newData['price']
            && $newData['sku']
        ) {
            return DB::transaction(function () use ($newData, $operator_user_id, $operator_user_name
            ) {
                $id = self::create([
                    "consignment_id" => $newData['consignment_id'],
                    "product_style_id" => $newData['product_style_id'],
                    "title" => $newData['title'],
                    "num" => $newData['num'],
                    "price" => $newData['price'],
                    "sku" => $newData['sku'],
                ])->id;

                $rePcsLSC = PurchaseLog::stockChange($newData['consignment_id'], $newData['product_style_id'], LogEvent::consignment()->value, $id, LogEventFeature::style_add()->value, $newData['num'], null, $operator_user_id, $operator_user_name);

                if ($rePcsLSC['success'] == 0) {
                    DB::rollBack();
                    return $rePcsLSC;
                }
                return ['success' => 1, 'error_msg' => "", 'id' => $id];
            });
        } else {
            return ['success' => 0, 'error_msg' => "未建立採購單"];
        }
    }

    //更新到貨數量
    public static function updateArrivedNum($id, $addnum) {
        return DB::transaction(function () use ($id, $addnum
        ) {
            $updateArr = [];
            $updateArr['arrived_num'] = DB::raw("arrived_num + $addnum");
            ConsignmentItem::where('id', $id)
                ->update($updateArr);
            return ['success' => 1, 'error_msg' => ""];
        });
    }

    public static function getData($consignment_id) {
        return self::where('consignment_id', $consignment_id)->whereNull('deleted_at');
    }
}
