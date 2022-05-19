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


    public static function paying_order_list(
        $supplier_id = null,
        $p_order_sn = null,
        $purchase_sn = null,
        $p_order_price = null,
        $p_order_payment_date = null
    ){
        $paying_order = DB::table('pcs_purchase as purchase')
            ->leftJoin('usr_users as user', 'user.id', '=', 'purchase.purchase_user_id')
            ->leftJoin('usr_users as audit', 'audit.id', '=', 'purchase.audit_user_id')
            ->leftJoin('prd_suppliers as supplier', 'supplier.id', '=', 'purchase.supplier_id')
            ->leftJoin(DB::raw('(
                SELECT
                    purchase_id,
                    SUM(price) AS price,
                    CONCAT(\'[\', GROUP_CONCAT(\'{
                        "product_owner":"\', v_u.name, \'",
                        "product_style_id":"\', pcs_purchase_items.product_style_id, \'",
                        "title":"\', pcs_purchase_items.title, \'",
                        "sku":"\', pcs_purchase_items.sku, \'",
                        "price":"\', pcs_purchase_items.price, \'",
                        "num":"\', pcs_purchase_items.num, \'",
                        "arrived_num":"\', pcs_purchase_items.arrived_num, \'",
                        "tally_num":"\', pcs_purchase_items.tally_num, \'",
                        "temp_id":"\', COALESCE(pcs_purchase_items.temp_id, ""), \'"
                    }\' ORDER BY pcs_purchase_items.id), \']\') AS item_list
                FROM pcs_purchase_items
                LEFT JOIN prd_product_styles AS v_ps ON v_ps.id = pcs_purchase_items.product_style_id
                LEFT JOIN prd_products AS v_product ON v_product.id = v_ps.product_id
                LEFT JOIN usr_users AS v_u ON v_u.id = v_product.user_id
                GROUP BY purchase_id
                ) AS v_table_1'), function ($join){
                    $join->on('v_table_1.purchase_id', '=', 'purchase.id');
            })
            ->leftJoin(DB::raw('(
                SELECT
                    GROUP_CONCAT(id) AS id,
                    purchase_id,
                    GROUP_CONCAT(DISTINCT usr_users_id) AS usr_users_id,
                    GROUP_CONCAT(type) AS type,
                    GROUP_CONCAT(sn) AS sn,
                    SUM(price) AS price,
                    GROUP_CONCAT(DISTINCT logistics_grade_id) AS logistics_grade_id,
                    GROUP_CONCAT(DISTINCT product_grade_id) AS product_grade_id
                FROM pcs_paying_orders
                WHERE deleted_at IS NULL
                GROUP BY purchase_id
                ) AS po'), function ($join){
                    $join->on('po.purchase_id', '=', 'purchase.id');
            })
            ->leftJoin(DB::raw('(
                SELECT pay_order_id,
                SUM(tw_price) AS payable_price,
                MAX(payment_date) AS payment_date,
                CONCAT(\'[\', GROUP_CONCAT(\'{
                        "payable_type":"\', v_po.type, \'",
                        "acc_income_type_fk":"\', acc_income_type_fk, \'",
                        "payable_id":"\', payable_id, \'",
                        "all_grades_id":"\', all_grades_id, \'",
                        "tw_price":"\', tw_price, \'",
                        "payment_date":"\', payment_date, \'",
                        "accountant_id_fk":"\', accountant_id_fk, \'",
                        "note":"\', COALESCE(note, ""), \'"
                    }\' ORDER BY acc_payable.id), \']\') AS pay_list
                FROM acc_payable
                LEFT JOIN pcs_paying_orders AS v_po ON v_po.id = acc_payable.pay_order_id WHERE v_po.deleted_at IS NULL
                GROUP BY v_po.purchase_id
                ) AS v_table_2'), function ($join){
                    $join->whereRaw('v_table_2.pay_order_id in (po.id)');
            })

            ->whereColumn([
                ['po.price', '=', 'v_table_2.payable_price'],
            ])

            ->select(
                'po.usr_users_id as po_usr_users_id',
                'po.type as po_type',
                'po.sn as po_sn',
                'po.price as po_price',// 付款單金額(應付)
                'po.logistics_grade_id as po_logistics_grade_id',
                'po.product_grade_id as po_product_grade_id',

                'purchase.id as purchase_id',
                'purchase.sn as purchase_sn',
                'purchase.supplier_name as purchase_supplier_name',
                'purchase.logistics_price as purchase_logistics_price',//運費
                'purchase.logistics_memo as purchase_logistics_memo',
                'purchase.invoice_num as purchase_invoice_num',
                'purchase.invoice_date as purchase_invoice_date',
                'purchase.close_date as purchase_close_date',
                'purchase.audit_date as purchase_audit_date',

                'user.name as purchaser',
                'audit.name as auditor',

                'supplier.id as supplier_id',
                'supplier.name as supplier_name',
                'supplier.contact_person as supplier_contact_person',

                'v_table_1.price as product_price_sum',//採購商品金額總計(未含運費)
                'v_table_1.item_list as product_list',

                'v_table_2.payment_date as payment_date',// 付款單完成付款日期
                'v_table_2.payable_price as payable_price',// 付款單金額(實付)
                'v_table_2.pay_list as payable_list',
            );
            // ->selectRaw('DATE_FORMAT(purchase.created_at, "%Y-%m-%d") as purchase_date');

        if ($supplier_id) {
            if (gettype($supplier_id) == 'array') {
                $paying_order->whereIn('supplier.id', $supplier_id);
            } else {
                $paying_order->where('supplier.id', $supplier_id);
            }
        }

        if ($p_order_sn) {
            $paying_order->where(function ($query) use ($p_order_sn) {
                $query->where('po.sn', 'like', "%{$p_order_sn}%");
            });
        }

        if ($purchase_sn) {
            $paying_order->where(function ($query) use ($purchase_sn) {
                $query->where('purchase.sn', 'like', "%{$purchase_sn}%");
            });
        }

        if ($p_order_price) {
            if (gettype($p_order_price) == 'array' && count($p_order_price) == 2) {
                $min_price = $p_order_price[0] ?? null;
                $max_price = $p_order_price[1] ?? null;
                if($min_price){
                    $paying_order->where('po.price', '>=', $min_price);
                }
                if($max_price){
                    $paying_order->where('po.price', '<=', $max_price);
                }
            }
        }

        if ($p_order_payment_date) {
            $s_payment_date = $p_order_payment_date[0] ? date('Y-m-d', strtotime($p_order_payment_date[0])) : null;
            $e_payment_date = $p_order_payment_date[1] ? date('Y-m-d', strtotime($p_order_payment_date[1] . ' +1 day')) : null;

            if($s_payment_date){
                $paying_order->where('v_table_2.payment_date', '>=', $s_payment_date);
            }
            if($e_payment_date){
                $paying_order->where('v_table_2.payment_date', '<', $e_payment_date);
            }
        }

        return $paying_order;
    }
}
