<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PayingOrder extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'pcs_paying_orders';
    protected $guarded = [];

    /**
     * 取得「採購」付款單的「應付帳款」資訊
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function accountPayable()
    {
        return $this->morphOne(AccountPayable::class, 'payingOrder', 'pay_order_type', 'pay_order_id');
    }

    /**
     * 付款單商品的會計科目資料
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function productGrade()
    {
        return $this->morphTo(__FUNCTION__, 'product_grade_type', 'product_grade_id');
    }

    /**
     * 物流費用的會計科目資料
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function logisticsGrade()
    {
        return $this->morphTo(__FUNCTION__, 'logistics_grade_type', 'logistics_grade_id');
    }

    public static function createPayingOrder(
        $purchase_id,
        $usr_users_id,
        $type,
        $product_grade_id,
        $logistics_grade_id,
        $price = null,
        $pay_date = null,
        $summary = null,
        $memo = null
    ) {
        return DB::transaction(function () use (
            $purchase_id,
            $usr_users_id,
            $type,
            $product_grade_id,
            $logistics_grade_id,
            $price,
            $pay_date,
            $summary,
            $memo
        ) {
            $sn = "PSG" . date("ymd") . str_pad((self::whereDate('created_at', '=', date('Y-m-d'))
                        ->withTrashed()
                        ->get()
                        ->count()) + 1, 3, '0', STR_PAD_LEFT);

            $id = self::create([
                "purchase_id" => $purchase_id,
                "usr_users_id" => $usr_users_id,
                "type" => $type,
                "sn" => $sn,
                "product_grade_id" => $product_grade_id,
                "logistics_grade_id" => $logistics_grade_id,
                "price" => $price,
                "pay_date" => $pay_date,
                'summary' => $summary,
                "memo" => $memo
            ])->id;

            return ['success' => 1, 'error_msg' => "", 'id' => $id];
        });
    }

    /**
     * @param $purchase_id
     * @param  int|null  $payType   0:訂金, 1:尾款 null:訂金跟尾款
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function getPayingOrdersWithPurchaseID($purchase_id, $payType = null)
    {
        $result = DB::table('pcs_paying_orders as paying_order')
            ->select(
                'paying_order.id as id',
                'paying_order.type as type',
                'paying_order.usr_users_id as usr_users_id',
                'paying_order.sn as sn',
                'paying_order.summary as summary',
                'paying_order.memo as memo',
                'paying_order.price as price',
            )
            ->selectRaw('DATE_FORMAT(paying_order.pay_date,"%Y-%m-%d") as pay_date')
            ->selectRaw('DATE_FORMAT(paying_order.created_at,"%Y-%m-%d") as created_at')
            ->where('paying_order.purchase_id', '=', $purchase_id)
            ->whereNull('paying_order.deleted_at');

        if (!is_null($payType)) {
            $result = $result->where('paying_order.type', '=', $payType);
        }

        return $result;
    }
}
