<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

use App\Enums\Delivery\Event;
use App\Enums\Supplier\Payment;

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


    public static function createPayingOrder(
        $source_type = null,
        $source_id,
        $source_sub_id = null,
        $usr_users_id,
        $type,
        $product_grade_id,
        $logistics_grade_id,
        $price = null,
        $summary = null,
        $memo = null,
        $payee_id = null,
        $payee_name = null,
        $payee_phone = null,
        $payee_address = null
    ) {
        return DB::transaction(function () use (
            $source_type,
            $source_id,
            $source_sub_id,
            $usr_users_id,
            $type,
            $product_grade_id,
            $logistics_grade_id,
            $price,
            $summary,
            $memo,
            $payee_id,
            $payee_name,
            $payee_phone,
            $payee_address
        ) {
            $sn = 'ISG' . date("ymd") . str_pad((self::whereDate('created_at', '=', date('Y-m-d'))
                        ->withTrashed()
                        ->get()
                        ->count()) + 1, 4, '0', STR_PAD_LEFT);

            $id = self::create([
                'source_type'=>$source_type ? $source_type : app(Purchase::class)->getTable(),
                'source_id'=>$source_id,
                'source_sub_id'=>$source_sub_id,
                "usr_users_id" => $usr_users_id,
                "type" => $type,
                "sn" => $sn,
                "product_grade_id" => $product_grade_id,
                "logistics_grade_id" => $logistics_grade_id,
                "price" => $price,
                'summary' => $summary,
                "memo" => $memo,
                'payee_id' => $payee_id,
                'payee_name' => $payee_name,
                'payee_phone' => $payee_phone,
                'payee_address' => $payee_address
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
                'paying_order.balance_date as balance_date',
                'paying_order.payment_date as payment_date',
                'paying_order.payee_id as payee_id',
                'paying_order.payee_name as payee_name',
                'paying_order.payee_phone as payee_phone',
                'paying_order.payee_address as payee_address',
                'paying_order.append_po_id as append_po_id',
                'paying_order.append_po_sn as append_po_sn',
            )
            ->selectRaw('DATE_FORMAT(paying_order.created_at,"%Y-%m-%d") as created_at')
            ->where([
                'paying_order.source_type'=>app(Purchase::class)->getTable(),
                'paying_order.source_id'=>$purchase_id,
            ])
            ->whereNull('paying_order.deleted_at');

        if (!is_null($payType)) {
            $result = $result->where('paying_order.type', '=', $payType);
        }

        return $result;
    }


    public static function paying_order_list(
        $payee = null,
        $po_sn = null,
        $source_sn = null,
        $po_price = null,
        $po_payment_date = null,
        $check_balance = 'all',
        $po_separate = false
    ){
        $separate = $po_separate ? ', type' : '';
        $payment_separate = $po_separate ? ', v_po.type' : '';

        $sq = '
            SELECT
                acc_all_grades.id,
                CASE
                    WHEN acc_first_grade.code IS NOT NULL THEN acc_first_grade.code
                    WHEN acc_second_grade.code IS NOT NULL THEN acc_second_grade.code
                    WHEN acc_third_grade.code IS NOT NULL THEN acc_third_grade.code
                    WHEN acc_fourth_grade.code IS NOT NULL THEN acc_fourth_grade.code
                    ELSE ""
                END AS code,
                CASE
                    WHEN acc_first_grade.name IS NOT NULL THEN acc_first_grade.name
                    WHEN acc_second_grade.name IS NOT NULL THEN acc_second_grade.name
                    WHEN acc_third_grade.name IS NOT NULL THEN acc_third_grade.name
                    WHEN acc_fourth_grade.name IS NOT NULL THEN acc_fourth_grade.name
                    ELSE ""
                END AS name
            FROM acc_all_grades
            LEFT JOIN acc_first_grade ON acc_all_grades.grade_id = acc_first_grade.id AND acc_all_grades.grade_type = "App\\\Models\\\FirstGrade"
            LEFT JOIN acc_second_grade ON acc_all_grades.grade_id = acc_second_grade.id AND acc_all_grades.grade_type = "App\\\Models\\\SecondGrade"
            LEFT JOIN acc_third_grade ON acc_all_grades.grade_id = acc_third_grade.id AND acc_all_grades.grade_type = "App\\\Models\\\ThirdGrade"
            LEFT JOIN acc_fourth_grade ON acc_all_grades.grade_id = acc_fourth_grade.id AND acc_all_grades.grade_type = "App\\\Models\\\FourthGrade"
        ';

        $query = DB::table(DB::raw('(
                SELECT
                    GROUP_CONCAT(pcs_paying_orders.id) AS id,
                    source_type,
                    source_id,
                    source_sub_id,
                    GROUP_CONCAT(DISTINCT usr_users_id) AS usr_users_id,
                    GROUP_CONCAT(type) AS type,
                    GROUP_CONCAT(sn) AS sn,
                    SUM(price) AS price,
                    GROUP_CONCAT(DISTINCT logistics_grade_id) AS logistics_grade_id,
                    GROUP_CONCAT(DISTINCT l_grade.code) AS logistics_grade_code,
                    GROUP_CONCAT(DISTINCT l_grade.name) AS logistics_grade_name,
                    GROUP_CONCAT(DISTINCT product_grade_id) AS product_grade_id,
                    GROUP_CONCAT(DISTINCT p_grade.code) AS product_grade_code,
                    GROUP_CONCAT(DISTINCT p_grade.name) AS product_grade_name,
                    CASE WHEN COUNT(*) = COUNT(balance_date) THEN MAX(balance_date) END AS balance_date,
                    CASE WHEN COUNT(*) = COUNT(payment_date) THEN MAX(payment_date) END AS payment_date,
                    summary,
                    memo,
                    payee_id,
                    payee_name,
                    payee_phone,
                    payee_address,
                    acc_currency_fk,
                    append_po_id,
                    created_at
                FROM pcs_paying_orders

                LEFT JOIN (' . $sq . ') AS l_grade ON l_grade.id = pcs_paying_orders.logistics_grade_id
                LEFT JOIN (' . $sq . ') AS p_grade ON p_grade.id = pcs_paying_orders.product_grade_id

                WHERE deleted_at IS NULL
                GROUP BY source_type, source_id, source_sub_id' . $separate . '
                ) AS po')
            )
            ->leftJoin(DB::raw('(
                SELECT pay_order_id,
                SUM(tw_price) AS payable_price,
                CONCAT(\'[\', GROUP_CONCAT(\'{
                        "payable_type":"\', v_po.type, \'",
                        "payable_method":"\', COALESCE(i_type.type, ""), \'",
                        "acc_income_type_fk":"\', acc_income_type_fk, \'",
                        "payable_id":"\', payable_id, \'",
                        "all_grades_id":"\', all_grades_id, \'",
                        "grade_code":"\', COALESCE(grade.code, ""), \'",
                        "grade_name":"\', COALESCE(grade.name, ""), \'",
                        "tw_price":"\', tw_price, \'",
                        "payment_date":"\', acc_payable.payment_date, \'",
                        "accountant_id_fk":"\', accountant_id_fk, \'",
                        "summary":"\', COALESCE(acc_payable.summary, ""), \'",
                        "note":"\', COALESCE(note, ""), \'",
                        "cheque_ticket_number":"\', COALESCE(_cheque.ticket_number, ""),\'",
                        "cheque_due_date":"\', COALESCE(_cheque.due_date, ""),\'"
                    }\' ORDER BY acc_payable.id), \']\') AS pay_list
                FROM acc_payable
                LEFT JOIN (' . $sq . ') AS grade ON grade.id = acc_payable.all_grades_id
                LEFT JOIN acc_income_type AS i_type ON i_type.id = acc_payable.acc_income_type_fk
                LEFT JOIN acc_payable_cheque AS _cheque ON acc_payable.payable_id = _cheque.id AND acc_payable.acc_income_type_fk = 2

                LEFT JOIN pcs_paying_orders AS v_po ON v_po.id = acc_payable.pay_order_id WHERE v_po.deleted_at IS NULL
                GROUP BY v_po.source_type, v_po.source_id, v_po.source_sub_id' . $payment_separate . '
                ) AS payable_table'), function ($join){
                    $join->whereRaw('payable_table.pay_order_id in (po.id)');
            })
            ->leftJoin('acc_currency AS currency', function($join){
                $join->on('currency.id', '=', 'po.acc_currency_fk');
            })

            // purchase - deposit
            ->leftJoin(DB::raw('(
                SELECT
                    pcs_paying_orders.id,
                    CONCAT(\'[\', GROUP_CONCAT(\'{
                        "product_owner":"\', "", \'",
                        "title":"\', CONCAT("訂金抵扣（訂金付款單號", sn, "）"), \'",
                        "sku":"\', "", \'",
                        "all_grades_id":"\', pcs_paying_orders.product_grade_id, \'",
                        "grade_code":"\', COALESCE(grade.code, ""), \'",
                        "grade_name":"\', COALESCE(grade.name, ""), \'",
                        "price":"\', price, \'",
                        "num":"\', 1, \'",
                        "summary":"\', COALESCE(pcs_paying_orders.summary, ""), \'",
                        "memo":"\', COALESCE(pcs_paying_orders.memo, ""), \'"
                    }\' ORDER BY pcs_paying_orders.id), \']\') AS items
                FROM pcs_paying_orders
                LEFT JOIN (' . $sq . ') AS grade ON grade.id = pcs_paying_orders.product_grade_id
                WHERE source_type = "pcs_purchase" AND type = 0 AND deleted_at IS NULL
                GROUP BY id
                ) AS deposit'), function ($join){
                    $join->on('deposit.id', '=', 'po.id');
                    $join->where([
                        'po.source_type'=>app(Purchase::class)->getTable(),
                        'po.source_sub_id'=>null,
                        'po.type'=>0,
                    ]);
            })
            // purchase - final
            ->leftJoin('pcs_purchase as purchase', function ($join) use($po_separate) {
                $join->on('po.source_id', '=', 'purchase.id');
                $join->where([
                    'po.source_type'=>app(Purchase::class)->getTable(),
                    'po.source_sub_id'=>null,
                ]);
            })
            ->leftJoin('usr_users as user', 'user.id', '=', 'purchase.purchase_user_id')
            ->leftJoin('usr_users as audit', 'audit.id', '=', 'purchase.audit_user_id')
            ->leftJoin('prd_suppliers as supplier', 'supplier.id', '=', 'purchase.supplier_id')
            ->leftJoin(DB::raw('(
                SELECT
                    purchase_id,
                    SUM(pcs_purchase_items.price) AS total_price,
                    CONCAT(\'[\', GROUP_CONCAT(\'{
                        "product_owner":"\', p_owner.name, \'",
                        "title":"\', pcs_purchase_items.title, \'",
                        "sku":"\', pcs_purchase_items.sku, \'",
                        "all_grades_id":"\', "", \'",
                        "grade_code":"\', "1118", \'",
                        "grade_name":"\', "商品存貨", \'",
                        "price":"\', pcs_purchase_items.price, \'",
                        "num":"\', pcs_purchase_items.num, \'",
                        "summary":"\', COALESCE(pcs_purchase_items.memo, ""), \'",
                        "memo":"\', "", \'"
                    }\' ORDER BY pcs_purchase_items.id), \']\') AS items
                FROM pcs_purchase_items
                LEFT JOIN prd_product_styles AS p_style ON p_style.id = pcs_purchase_items.product_style_id
                LEFT JOIN prd_products AS product ON product.id = p_style.product_id
                LEFT JOIN usr_users AS p_owner ON p_owner.id = product.user_id
                WHERE product.deleted_at IS NULL
                GROUP BY purchase_id
                ) AS purchase_item_table'), function ($join){
                    $join->on('purchase_item_table.purchase_id', '=', 'purchase.id');
            })


            // logistic order
            // ->leftJoin('ord_orders', function ($join) {
            //     $join->on('po.source_id', '=', 'ord_orders.id');
            //     $join->where([
            //         'po.source_type'=>app(Order::class)->getTable(),
            //     ]);
            // })
            ->leftJoin('dlv_delivery', function ($join) {
                $join->on('po.source_sub_id', '=', 'dlv_delivery.event_id');
                $join->where([
                    'dlv_delivery.event'=>Event::order()->value,
                    'po.source_type'=>app(Order::class)->getTable(),
                ]);
            })
            ->leftJoin('dlv_logistic', function ($join) {
                $join->on('dlv_logistic.delivery_id', '=', 'dlv_delivery.id');
            })
            ->leftJoin('shi_group', function ($join) {
                $join->on('shi_group.id', '=', 'dlv_logistic.ship_group_id');
                $join->whereNotNull('dlv_logistic.ship_group_id');
            })
            ->leftJoin('prd_suppliers', function ($join) {
                $join->on('prd_suppliers.id', '=', 'shi_group.supplier_fk');
                $join->whereNotNull('shi_group.supplier_fk');
            })

            // consignment
            ->leftJoin('csn_consignment AS consignment', function ($join) {
                $join->on('po.source_id', '=', 'consignment.id');
                $join->where([
                    'po.source_type'=>app(Consignment::class)->getTable(),
                ]);
            })
            ->leftJoin('dlv_delivery AS co_delivery', function ($join) {
                $join->on('co_delivery.event_id', '=', 'consignment.id')
                    ->where('co_delivery.event', '=', Event::consignment()->value);
            })
            ->leftJoin('dlv_logistic AS co_logistic', function ($join) {
                $join->on('co_logistic.delivery_id', '=', 'co_delivery.id');
            })
            ->leftJoin('shi_group AS co_shi_group', function ($join) {
                $join->on('co_shi_group.id', '=', 'co_logistic.ship_group_id');
                $join->whereNotNull('co_logistic.ship_group_id');
            })
            ->leftJoin('prd_suppliers AS co_supplier', function ($join) {
                $join->on('co_supplier.id', '=', 'co_shi_group.supplier_fk');
                $join->whereNotNull('co_shi_group.supplier_fk');
            })

            // stitute
            ->leftJoin('acc_stitute_orders AS so', function ($join) {
                $join->on('po.id', '=', 'so.pay_order_id');
                // $join->on('po.source_id', '=', 'so.id');
                // $join->where([
                //     'po.source_type'=>app(StituteOrder::class)->getTable(),
                //     'po.source_sub_id'=>null,
                // ]);
            })
            ->leftJoin(DB::raw('(
                SELECT
                    so_item.stitute_order_id,
                    CONCAT(\'[\', GROUP_CONCAT(\'{
                            "product_owner":"\', "", \'",
                            "title":"\', so_item.summary, \'",
                            "sku":"\', "", \'",
                            "all_grades_id":"\', so_item.grade_id, \'",
                            "grade_code":"\', COALESCE(grade.code, ""), \'",
                            "grade_name":"\', COALESCE(grade.name, ""), \'",
                            "price":"\', so_item.total_price, \'",
                            "num":"\', so_item.qty, \'",
                            "summary":"\', COALESCE(so_item.summary, ""), \'",
                            "memo":"\', COALESCE(so_item.memo, ""), \'"
                        }\' ORDER BY so_item.id), \']\') AS items
                FROM acc_stitute_order_items AS so_item
                LEFT JOIN (' . $sq . ') AS grade ON grade.id = so_item.grade_id
                LEFT JOIN acc_currency ON acc_currency.id = so_item.currency_id
                GROUP BY so_item.stitute_order_id
                ) AS stitute_items_table'), function ($join){
                    $join->on('so.id', '=', 'stitute_items_table.stitute_order_id');
            })

            // main order return
            ->leftJoin('ord_orders AS order_return', function ($join) {
                $join->on('po.source_id', '=', 'order_return.id');
                $join->where([
                    'po.source_type'=>app(Order::class)->getTable(),
                    'po.source_sub_id'=>null,
                ]);
            })
            ->leftJoin(DB::raw('(
                SELECT ord_items.order_id,
                CONCAT(\'[\', GROUP_CONCAT(\'{
                        "product_owner":"\', p_owner.name, \'",
                        "title":"\', ord_items.product_title, \'",
                        "sku":"\', ord_items.sku, \'",
                        "all_grades_id":"\', "", \'",
                        "grade_code":"\', "4101", \'",
                        "grade_name":"\', "銷貨收入", \'",
                        "price":"\', ord_items.price * ord_items.qty, \'",
                        "num":"\', ord_items.qty, \'",
                        "summary":"\', COALESCE(ord_items.note, ""), \'",
                        "memo":"\', "", \'"
                    }\' ORDER BY ord_items.id), \']\') AS items
                FROM ord_items
                LEFT JOIN prd_product_styles AS p_style ON p_style.id = ord_items.product_style_id
                LEFT JOIN prd_products AS product ON product.id = p_style.product_id
                LEFT JOIN usr_users AS p_owner ON p_owner.id = product.user_id
                GROUP BY ord_items.order_id
                ) AS order_item_table'), function ($join){
                    $join->on('order_item_table.order_id', '=', 'order_return.id');
            })
            ->leftJoin(DB::raw('(
                SELECT order_id,
                CONCAT(\'[\', GROUP_CONCAT(\'{
                        "sub_order_id":"\', COALESCE(sub_order_id, ""), \'",
                        "order_item_id":"\', COALESCE(order_item_id, ""), \'",
                        "discount_grade_id":"\', COALESCE(discount_grade_id, ""), \'",
                        "grade_code":"\', COALESCE(grade.code, ""), \'",
                        "grade_name":"\', COALESCE(grade.name, ""), \'",
                        "title":"\', COALESCE(title, ""), \'",
                        "sn":"\', COALESCE(sn, ""), \'",
                        "category_title":"\', category_title, \'",
                        "category_code":"\', category_code, \'",
                        "extra_id":"\', COALESCE(extra_id, ""), \'",
                        "extra_title":"\', COALESCE(extra_title, ""), \'",
                        "discount_value":"\', COALESCE(discount_value, ""), \'"
                    }\' ORDER BY ord_discounts.id), \']\') AS items
                FROM ord_discounts
                LEFT JOIN (' . $sq . ') AS grade ON grade.id = ord_discounts.discount_grade_id

                WHERE discount_value IS NOT NULL AND order_type = "main"
                GROUP BY order_id
                ) AS discounts_table'), function ($join){
                    $join->on('discounts_table.order_id', '=', 'order_return.id');
            })

            // sub order return
            ->leftJoin('dlv_delivery AS sub_order_return', function ($join) {
                $join->on('po.source_id', '=', 'sub_order_return.id');
                $join->where([
                    'po.source_type'=>app(Delivery::class)->getTable(),
                    'po.source_sub_id'=>null,
                ]);
            })
            ->leftJoin(DB::raw('(
                SELECT
                    dlv_back.delivery_id,
                    CONCAT(\'[\', GROUP_CONCAT(\'{
                        "product_owner":"\', p_owner.name, \'",
                        "title":"\', dlv_back.product_title, \'",
                        "sku":"\', dlv_back.sku, \'",
                        "all_grades_id":"\', "", \'",
                        "grade_code":"\', "4101", \'",
                        "grade_name":"\', "銷貨收入", \'",
                        "price":"\', dlv_back.price * dlv_back.qty, \'",
                        "num":"\', dlv_back.qty, \'",
                        "summary":"\', COALESCE(dlv_back.memo, ""), \'",
                        "memo":"\', "", \'"
                    }\' ORDER BY dlv_back.id), \']\') AS items
                FROM dlv_back
                LEFT JOIN prd_product_styles ON prd_product_styles.id = dlv_back.product_style_id
                LEFT JOIN prd_products AS product ON product.id = prd_product_styles.product_id
                LEFT JOIN usr_users AS p_owner ON p_owner.id = product.user_id
                WHERE product.deleted_at IS NULL
                GROUP BY dlv_back.delivery_id
                ) AS delivery_back'), function ($join){
                    $join->on('delivery_back.delivery_id', '=', 'sub_order_return.id');
            })

            // pcs_paying_orders - account payable
            ->leftJoin('pcs_paying_orders AS _account_po', function ($join) {
                $join->on('po.source_id', '=', '_account_po.id');
                $join->where([
                    'po.source_type'=>app(self::class)->getTable(),
                    'po.source_sub_id'=>null,
                    'po.type'=>1,
                    '_account_po.deleted_at'=>null,
                ]);
            })
            ->leftJoin(DB::raw('(
                SELECT _account.append_pay_order_id,
                CONCAT(\'[\', GROUP_CONCAT(\'{
                        "product_owner":"\', "", \'",
                        "title":"\', "", \'",
                        "sku":"\', "", \'",
                        "all_grades_id":"\', acc_payable.all_grades_id, \'",
                        "grade_code":"\', COALESCE(grade.code, ""), \'",
                        "grade_name":"\', COALESCE(grade.name, ""), \'",
                        "price":"\', _account.amt_net, \'",
                        "num":"\', 1, \'",
                        "all_grades_id":"\', acc_payable.all_grades_id, \'",
                        "summary":"\', COALESCE(acc_payable.summary, ""), \'",
                        "memo":"\', COALESCE(acc_payable.note, ""), \'"
                    }\' ORDER BY acc_payable.id), \']\') AS items
                FROM acc_payable
                LEFT JOIN (' . $sq . ') AS grade ON grade.id = acc_payable.all_grades_id

                LEFT JOIN acc_payable_account AS _account ON acc_payable.payable_id = _account.id AND acc_payable.acc_income_type_fk = 5
                GROUP BY _account.append_pay_order_id
                ) AS payable_account_table'), function ($join){
                    $join->on('payable_account_table.append_pay_order_id', '=', 'po.id');
            })

            // pcs_paying_orders - multiple
            ->leftJoin('pcs_paying_orders AS multiple_po', function ($join) {
                $join->on('po.source_id', '=', 'multiple_po.id');
                $join->where([
                    'po.source_type'=>app(self::class)->getTable(),
                    'po.source_sub_id'=>null,
                    'po.type'=>2,
                    'multiple_po.deleted_at'=>null,
                ]);
            })
            ->leftJoin(DB::raw('(
                SELECT
                    it.append_po_id,
                    CONCAT(\'[\', GROUP_CONCAT(\'{
                        "product_owner":"\', "", \'",
                        "title":"\', it.sn, \'",
                        "sku":"\', "", \'",
                        "all_grades_id":"\', "", \'",
                        "grade_code":"\', "", \'",
                        "grade_name":"\', "", \'",
                        "price":"\', it.price, \'",
                        "num":"\', 1, \'",
                        "summary":"\', "", \'",
                        "memo":"\', "", \'"
                    }\' ORDER BY id), \']\') AS items
                FROM pcs_paying_orders AS it
                WHERE it.deleted_at IS NULL
                GROUP BY it.append_po_id
                ) AS multiple_po_table'), function ($join){
                    $join->on('multiple_po.id', '=', 'multiple_po_table.append_po_id');
            })

            ->whereColumn([
                // ['po.price', '=', 'payable_table.payable_price'],
            ])
            // ->whereRaw('( (purchase_item_table.price + purchase.logistics_price) = payable_table.payable_price OR dlv_logistic.cost = payable_table.payable_price )')

            ->select(
                'po.id as po_id',
                'po.source_type as po_source_type',
                'po.source_id as po_source_id',
                'po.source_sub_id as po_source_sub_id',
                'po.type as po_type',
                'po.sn as po_sn',
                'po.price as po_price',// 付款單金額(應付)
                'po.logistics_grade_id as po_logistics_grade_id',
                'po.logistics_grade_code as po_logistics_grade_code',
                'po.logistics_grade_name as po_logistics_grade_name',
                'po.product_grade_id as po_product_grade_id',
                'po.product_grade_code as po_product_grade_code',
                'po.product_grade_name as po_product_grade_name',
                'po.balance_date AS po_balance_date',
                'po.payment_date AS payment_date',// 付款單完成付款日期
                'po.payee_id AS po_target_id',
                'po.payee_name AS po_target_name',
                'po.payee_phone AS po_target_phone',
                'po.payee_address AS po_target_address',
                'po.append_po_id AS po_append_po_id',
                'currency.id AS currency_id',
                DB::raw('IF(currency.name IS NOT NULL, currency.name, "NTD") AS currency_name'),
                DB::raw('IF(currency.rate IS NOT NULL, currency.rate, "1") AS currency_rate'),

                'payable_table.payable_price as payable_price',// 付款單金額(實付)
                'payable_table.pay_list as payable_list',

                // 'purchase_item_table.total_price as product_total_price',//採購商品金額總計(未含運費)


                // 'purchase.id as purchase_id',
                // 'purchase.sn as purchase_sn',
                // 'purchase.supplier_name as purchase_supplier_name',
                // 'purchase.logistics_price as purchase_logistics_price',//運費
                // 'purchase.logistics_memo as purchase_logistics_memo',
                // 'purchase.invoice_num as purchase_invoice_num',
                // 'purchase.invoice_date as purchase_invoice_date',
                // 'purchase.close_date as purchase_close_date',
                // 'purchase.audit_date as purchase_audit_date',

                // 'user.name as purchaser',
                // 'audit.name as auditor',

                // 'supplier.id as supplier_id_p',
                // 'supplier.name as supplier_name_p',
                // 'supplier.contact_person as supplier_contact_person_p',

                // 'dlv_delivery.event_sn as order_sub_sn',
                // 'dlv_logistic.cost as order_sub_cost',

                // 'prd_suppliers.id as supplier_id_o',
                // 'prd_suppliers.name as supplier_name_o',
                // 'prd_suppliers.contact_person as supplier_contact_person_o',
            )
            ->selectRaw('
                CASE
                    WHEN po.source_type = "' . app(Purchase::class)->getTable() . '" AND po.source_sub_id IS NULL THEN purchase.sn
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.source_sub_id IS NOT NULL AND po.type = 1 THEN dlv_delivery.event_sn
                    WHEN po.source_type = "' . app(Consignment::class)->getTable() . '" AND po.type = 1 THEN consignment.sn
                    WHEN po.source_type = "' . app(StituteOrder::class)->getTable() . '" AND po.type = 1 THEN so.sn
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.type = 9 THEN order_return.sn
                    WHEN po.source_type = "' . app(Delivery::class)->getTable() . '" AND po.type = 9 THEN sub_order_return.event_sn
                    WHEN po.source_type = "' . app(self::class)->getTable() . '" AND po.type = 1 THEN _account_po.sn
                    WHEN po.source_type = "' . app(self::class)->getTable() . '" AND po.type = 2 THEN multiple_po.sn
                    ELSE NULL
                END AS source_sn
            ')
            // WHEN po.type = "0,1" THEN purchase_item_table.items ELSE CONCAT(SUBSTRING_INDEX(deposit.items, "]", 1), ",", SUBSTRING(purchase_item_table.items, 2))
            // IF(deposit.items IS NULL, purchase_item_table.items, CONCAT(SUBSTRING_INDEX(deposit.items, "]", 1), ",", SUBSTRING(purchase_item_table.items, 2)))
            ->selectRaw('
                CASE
                    WHEN po.source_type = "' . app(Purchase::class)->getTable() . '" AND po.source_sub_id IS NULL THEN CASE WHEN po.type = "0" THEN deposit.items ELSE purchase_item_table.items END
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.source_sub_id IS NOT NULL AND po.type = 1 THEN NULL
                    WHEN po.source_type = "' . app(Consignment::class)->getTable() . '" AND po.type = 1 THEN NULL
                    WHEN po.source_type = "' . app(StituteOrder::class)->getTable() . '" AND po.type = 1 THEN stitute_items_table.items
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.type = 9 THEN order_item_table.items
                    WHEN po.source_type = "' . app(Delivery::class)->getTable() . '" AND po.type = 9 THEN delivery_back.items
                    WHEN po.source_type = "' . app(self::class)->getTable() . '" AND po.type = 1 THEN payable_account_table.items
                    WHEN po.source_type = "' . app(self::class)->getTable() . '" AND po.type = 2 THEN multiple_po_table.items
                    ELSE NULL
                END AS product_items
            ')
            ->selectRaw('
                CASE
                    WHEN po.source_type = "' . app(Purchase::class)->getTable() . '" AND po.source_sub_id IS NULL THEN CASE WHEN po.type = "0" THEN 0 ELSE purchase.logistics_price END
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.source_sub_id IS NOT NULL AND po.type = 1 THEN dlv_logistic.cost
                    WHEN po.source_type = "' . app(Consignment::class)->getTable() . '" AND po.type = 1 THEN co_logistic.cost
                    WHEN po.source_type = "' . app(StituteOrder::class)->getTable() . '" AND po.type = 1 THEN 0
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.type = 9 THEN order_return.dlv_fee
                    WHEN po.source_type = "' . app(Delivery::class)->getTable() . '" AND po.type = 9 THEN 0
                    WHEN po.source_type = "' . app(self::class)->getTable() . '" AND po.type = 1 THEN 0
                    WHEN po.source_type = "' . app(self::class)->getTable() . '" AND po.type = 2 THEN 0
                    ELSE 0
                END AS logistics_price
            ')
            ->selectRaw('
                CASE
                    WHEN po.source_type = "' . app(Purchase::class)->getTable() . '" AND po.source_sub_id IS NULL THEN CASE WHEN po.type = "0" THEN NULL ELSE "物流費用" END
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.source_sub_id IS NOT NULL AND po.type = 1 THEN CONCAT("物流費用 ", shi_group.name, " 物流編號：#", COALESCE(dlv_logistic.projlgt_order_sn, dlv_logistic.package_sn, "") )
                    WHEN po.source_type = "' . app(Consignment::class)->getTable() . '" AND po.type = 1 THEN CONCAT("物流費用 ", co_shi_group.name, " 物流編號：#", COALESCE(co_logistic.projlgt_order_sn, co_logistic.package_sn, "") )
                    WHEN po.source_type = "' . app(StituteOrder::class)->getTable() . '" AND po.type = 1 THEN NULL
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.type = 9 THEN "物流費用"
                    WHEN po.source_type = "' . app(Delivery::class)->getTable() . '" AND po.type = 9 THEN NULL
                    WHEN po.source_type = "' . app(self::class)->getTable() . '" AND po.type = 1 THEN NULL
                    WHEN po.source_type = "' . app(self::class)->getTable() . '" AND po.type = 2 THEN NULL
                    ELSE NULL
                END AS logistics_summary
            ')
            ->selectRaw('
                CASE
                    WHEN po.source_type = "' . app(Purchase::class)->getTable() . '" AND po.source_sub_id IS NULL THEN CASE WHEN po.type = "0" THEN NULL ELSE po.memo END
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.source_sub_id IS NOT NULL AND po.type = 1 THEN dlv_logistic.memo
                    WHEN po.source_type = "' . app(Consignment::class)->getTable() . '" AND po.type = 1 THEN co_logistic.memo
                    WHEN po.source_type = "' . app(StituteOrder::class)->getTable() . '" AND po.type = 1 THEN NULL
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.type = 9 THEN order_return.note
                    WHEN po.source_type = "' . app(Delivery::class)->getTable() . '" AND po.type = 9 THEN NULL
                    WHEN po.source_type = "' . app(self::class)->getTable() . '" AND po.type = 1 THEN NULL
                    WHEN po.source_type = "' . app(self::class)->getTable() . '" AND po.type = 2 THEN NULL
                    ELSE NULL
                END AS logistics_memo
            ')
            ->selectRaw('
                CASE
                    WHEN po.source_type = "' . app(Purchase::class)->getTable() . '" AND po.source_sub_id IS NULL THEN 0
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.source_sub_id IS NOT NULL AND po.type = 1 THEN 0
                    WHEN po.source_type = "' . app(Consignment::class)->getTable() . '" AND po.type = 1 THEN 0
                    WHEN po.source_type = "' . app(StituteOrder::class)->getTable() . '" AND po.type = 1 THEN 0
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.type = 9 THEN order_return.discount_value
                    WHEN po.source_type = "' . app(Delivery::class)->getTable() . '" AND po.type = 9 THEN 0
                    WHEN po.source_type = "' . app(self::class)->getTable() . '" AND po.type = 1 THEN 0
                    WHEN po.source_type = "' . app(self::class)->getTable() . '" AND po.type = 2 THEN 0
                    ELSE 0
                END AS discount_value
            ')
            ->selectRaw('
                CASE
                    WHEN po.source_type = "' . app(Purchase::class)->getTable() . '" AND po.source_sub_id IS NULL THEN NULL
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.source_sub_id IS NOT NULL AND po.type = 1 THEN NULL
                    WHEN po.source_type = "' . app(Consignment::class)->getTable() . '" AND po.type = 1 THEN NULL
                    WHEN po.source_type = "' . app(StituteOrder::class)->getTable() . '" AND po.type = 1 THEN NULL
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.type = 9 THEN discounts_table.items
                    WHEN po.source_type = "' . app(Delivery::class)->getTable() . '" AND po.type = 9 THEN NULL
                    WHEN po.source_type = "' . app(self::class)->getTable() . '" AND po.type = 1 THEN NULL
                    WHEN po.source_type = "' . app(self::class)->getTable() . '" AND po.type = 2 THEN NULL
                    ELSE 0
                END AS order_discount
            ');

        if ($payee) {
            if (gettype($payee) == 'array') {
                $query->where([
                    'po.payee_id'=>$payee['id'],
                ])->where('po.payee_name', 'like', "%{$payee['name']}%");
            }
        }

        if ($po_sn) {
            if(gettype($po_sn) == 'array') {
                $query->whereIn('po.sn', $po_sn);
            } else {
                $query->where('po.sn', 'like', "%{$po_sn}%");
            }
        }

        if ($source_sn) {
            $query->where(function ($query) use ($source_sn) {
                $query->where('purchase.sn', 'like', "%{$source_sn}%")
                    ->orWhere('dlv_delivery.event_sn', 'like', "%{$source_sn}%")
                    ->orWhere('consignment.sn', 'like', "%{$source_sn}%")
                    ->orWhere('so.sn', 'like', "%{$source_sn}%")
                    ->orWhere('order_return.sn', 'like', "%{$source_sn}%")
                    ->orWhere('sub_order_return.event_sn', 'like', "%{$source_sn}%")
                    ->orWhere('_account_po.sn', 'like', "%{$source_sn}%")
                    ->orWhere('multiple_po.sn', 'like', "%{$source_sn}%");
            });
        }

        if ($po_price) {
            if (gettype($po_price) == 'array' && count($po_price) == 2) {
                $min_price = $po_price[0] ?? null;
                $max_price = $po_price[1] ?? null;
                if($min_price){
                    $query->where('po.price', '>=', $min_price);
                }
                if($max_price){
                    $query->where('po.price', '<=', $max_price);
                }
            }
        }

        if ($po_payment_date) {
            $s_payment_date = $po_payment_date[0] ? date('Y-m-d', strtotime($po_payment_date[0])) : null;
            $e_payment_date = $po_payment_date[1] ? date('Y-m-d', strtotime($po_payment_date[1] . ' +1 day')) : null;

            if($s_payment_date){
                $query->where('po.payment_date', '>=', $s_payment_date);
            }
            if($e_payment_date){
                $query->where('po.payment_date', '<', $e_payment_date);
            }
        }

        if ($check_balance == 'all') {
            //
        } else if ($check_balance == 0) {
            $query->whereNull('po.balance_date');
        } else if($check_balance == 1){
            $query->whereNotNull('po.balance_date');
        }

        return $query->orderBy('po.created_at', 'DESC');
    }


    public static function delete_paying_order($id)
    {
        $target = self::findOrFail($id);

        $target->delete();

        return $target;
    }


    public static function get_payable_detail($pay_order_id = null, $method_id = null)
    {
        $query = DB::table('acc_payable AS payable')
            ->leftJoin('pcs_paying_orders AS po', function($join){
                $join->on('po.id', '=', 'payable.pay_order_id');
                $join->where([
                    'po.deleted_at'=>null,
                ]);
            })
            ->leftJoin('acc_payable_cash AS _cash', function($join){
                $join->on('payable.payable_id', '=', '_cash.id');
                $join->where([
                    'payable.acc_income_type_fk'=>1,
                ]);
            })
            ->leftJoin('acc_payable_cheque AS _cheque', function($join){
                $join->on('payable.payable_id', '=', '_cheque.id');
                $join->where([
                    'payable.acc_income_type_fk'=>2,
                ]);
            })
            ->leftJoin('acc_payable_remit AS _remit', function($join){
                $join->on('payable.payable_id', '=', '_remit.id');
                $join->where([
                    'payable.acc_income_type_fk'=>3,
                ]);
            })
            ->leftJoin('acc_payable_currency AS _currency', function($join){
                $join->on('payable.payable_id', '=', '_currency.id');
                $join->where([
                    'payable.acc_income_type_fk'=>4,
                ]);
            })
            ->leftJoin('acc_payable_account AS _account', function($join){
                $join->on('payable.payable_id', '=', '_account.id');
                $join->where([
                    'payable.acc_income_type_fk'=>5,
                ]);
            })
            ->leftJoin('acc_payable_other AS _other', function($join){
                $join->on('payable.payable_id', '=', '_other.id');
                $join->where([
                    'payable.acc_income_type_fk'=>6,
                ]);
            })

            ->where(function ($q) use ($pay_order_id, $method_id) {
                if(gettype($pay_order_id) == 'array') {
                    $q->whereIn('payable.pay_order_id', $pay_order_id);
                } else {
                    $q->where('payable.pay_order_id', $pay_order_id);
                }

                if($method_id){
                    $q->where('payable.acc_income_type_fk', $method_id);
                }
            })

            ->selectRaw('
                po.sn AS po_sn,
                po.type AS po_type,

                payable.id AS payable_id,
                payable.pay_order_id,
                payable.acc_income_type_fk,
                payable.payable_id AS payable_method_id,
                payable.all_grades_id,
                payable.tw_price,
                payable.payment_date,
                payable.accountant_id_fk,
                payable.taxation,
                payable.summary,
                payable.note
            ')
            // ->selectRaw('
            //     _cash.cardnumber AS credit_card_number,
            // ')

            ->selectRaw('
                _cheque.id AS cheque_id,
                _cheque.ticket_number AS cheque_ticket_number,
                _cheque.due_date AS cheque_due_date,

                _cheque.grade_code AS cheque_grade_code,
                _cheque.grade_name AS cheque_grade_name,

                _cheque.status_code AS cheque_status_code,
                _cheque.status AS cheque_status,
                _cheque.cashing_date AS cheque_cashing_date,
                _cheque.bounce_date AS cheque_bounce_date,
                _cheque.note_payable_order_id AS cheque_note_payable_order_id,
                _cheque.sn AS cheque_sn,
                _cheque.amt_net AS cheque_amt_net
            ')

            ->selectRaw('
                _currency.rate AS rate,
                _currency.foreign_currency AS currency_foreign,
                _currency.acc_currency_fk AS currency_fk
            ')

            ->selectRaw('
                _remit.remit_date  AS remit_date
            ')

            ->selectRaw('
                _account.status_code AS account_status_code,
                _account.sn AS account_sn,
                _account.amt_net AS account_amt_net,
                _account.payment_date AS account_payment_date
            ')
            // ->selectRaw('
            //     _other.status_code AS account_status_code,
            // ')
            ->get();

        foreach($query as $value){
            $value->payable_method_name = Payment::getDescription($value->acc_income_type_fk);
            $value->account = AllGrade::find($value->all_grades_id)->eachGrade;

            if($value->acc_income_type_fk == 4){
                $arr = explode('-', AllGrade::find($value->all_grades_id)->eachGrade->name);
                $value->currency_name = $arr[0] == '外幣' ? $arr[1] . ' - ' . $arr[2] : 'NTD';
                $value->currency_rate = DB::table('acc_payable_currency')->find($value->payable_id)->currency;
            } else {
                $value->currency_name = 'NTD';
                $value->currency_rate = 1;
            }
        }

        return $query;
    }


    public static function get_accounts_payable_list(
        $payable_account_id = null,
        $account_status_code = null,
        $sn = null,

        $account_payable_grade_id = null,
        $authamt_price = null,
        $po_created_date = null,
        $po_target = null
    ){
        $query = DB::table('acc_payable AS payable')
            ->leftJoin('acc_income_type AS i_type', function($join){
                $join->on('payable.acc_income_type_fk', '=', 'i_type.id');
            })
            ->join('pcs_paying_orders AS po', function($join){
                $join->on('payable.pay_order_id', '=', 'po.id');
                $join->where([
                    'po.deleted_at'=>null,
                ]);
            })
            ->leftJoin('acc_currency AS currency', function($join){
                $join->on('currency.id', '=', 'po.acc_currency_fk');
            })
            ->leftJoin('usr_users AS undertaker', function($join){
                $join->on('po.usr_users_id', '=', 'undertaker.id');
                $join->where([
                    'undertaker.deleted_at'=>null,
                ]);
            })

            ->join('acc_payable_account AS _account', function($join){
                $join->on('payable.payable_id', '=', '_account.id');
                $join->where([
                    'payable.acc_income_type_fk'=>5,
                ]);
            })
            ->leftJoinSub(GeneralLedger::getAllGrade(), 'all_grade', function($join) {
                $join->on('all_grade.primary_id', 'payable.all_grades_id');
            })
            ->leftJoin('pcs_paying_orders AS append_po', function($join){
                $join->on('_account.append_pay_order_id', '=', 'append_po.id');
                $join->where([
                    'append_po.deleted_at'=>null,
                ]);
            })

            ->where([
                'po.deleted_at'=>null,
            ])
            ->whereNotNull('po.balance_date')
            ->whereNotNull('po.payment_date')
            ->whereNotNull('payable.payment_date')

            ->selectRaw('
                po.id AS po_id,
                po.source_type AS po_source_type,
                po.source_id AS po_source_id,
                po.source_sub_id AS po_source_sub_id,
                po.type AS po_type,
                po.sn AS po_sn,
                undertaker.name AS po_undertaker,
                po.payee_id AS po_target_id,
                po.payee_name AS po_target_name,
                po.payee_phone AS po_target_phone,
                po.payee_address AS po_target_address,
                po.created_at AS po_created,
                currency.id AS currency_id,
                IF(currency.name IS NOT NULL, currency.name, "NTD") AS currency_name,
                IF(currency.rate IS NOT NULL, currency.rate, "1") AS currency_rate,

                payable.id AS payable_id,
                i_type.id AS payable_method_id,
                i_type.type AS payable_method_type,
                payable.all_grades_id AS po_payable_grade_id,
                payable.tw_price,
                payable.taxation,
                payable.summary,
                payable.note,

                all_grade.code AS po_payable_grade_code,
                all_grade.name AS po_payable_grade_name,

                _account.id AS account_payable_id,
                _account.status_code AS account_status_code,
                _account.amt_net AS account_amt_net,
                _account.payment_date AS account_payment_date,

                append_po.id AS append_po_id,
                append_po.source_type AS append_po_source_type,
                append_po.source_id AS append_po_source_id,
                append_po.source_sub_id AS append_po_source_sub_id,
                append_po.type AS append_po_type,
                append_po.sn AS append_po_sn
            ')
            ->orderBy('_account.id', 'asc');

        if($payable_account_id) {
            if(gettype($payable_account_id) == 'array') {
                $query->whereIn('_account.id', $payable_account_id);
            } else {
                $query->where('_account.id', $payable_account_id);
            }
        }

        if($account_status_code !== null){
            $query->where('_account.status_code', $account_status_code);
        }

        if($sn){
            $query->where('po.sn', 'like', "%{$sn}%")
                ->orWhere('append_po.sn', 'like', "%{$sn}%");
        }

        if($account_payable_grade_id) {
            if(gettype($account_payable_grade_id) == 'array') {
                $query->whereIn('payable.all_grades_id', $account_payable_grade_id);
            } else {
                $query->where('payable.all_grades_id', $account_payable_grade_id);
            }
        }

        if($authamt_price) {
            if (gettype($authamt_price) == 'array' && count($authamt_price) == 2) {
                $min_price = $authamt_price[0] ?? null;
                $max_price = $authamt_price[1] ?? null;
                if($min_price){
                    $query->where('_account.amt_net', '>=', $min_price);
                }
                if($max_price){
                    $query->where('_account.amt_net', '<=', $max_price);
                }
            }
        }

        if($po_created_date){
            $s_po_created_date = $po_created_date[0] ? date('Y-m-d', strtotime($po_created_date[0])) : null;
            $e_po_created_date = $po_created_date[1] ? date('Y-m-d', strtotime($po_created_date[1] . ' +1 day')) : null;

            if($s_po_created_date){
                $query->where('po.created_at', '>=', $s_po_created_date);
            }
            if($e_po_created_date){
                $query->where('po.created_at', '<', $e_po_created_date);
            }
        }

        if($po_target && gettype($po_target) == 'array') {
            $target_id = $po_target[0];
            $target_name = $po_target[1];

            // $query->where(function ($q1) use ($target_id, $target_name) {
            //     $q1->where([
            //         'customer.id'=>$target_id,
            //         'customer.name'=>$target_name,
            //     ])->orWhere(function ($q2) use ($target_id, $target_name) {
            //         $q2->where([
            //             'depot.id'=>$target_id,
            //             'depot.name'=>$target_name,
            //         ]);
            //     });
            // });

            $query->where([
                'po.payee_id'=>$target_id,
                'po.payee_name'=>$target_name,
            ]);
        }

        return $query;
    }


    public static function update_account_payable_method($request, $clear = false)
    {
        if($clear){
            DB::table('acc_payable_account')->where('append_pay_order_id', $request['append_pay_order_id'])->update([
                'status_code'=>0,
                'append_pay_order_id'=>null,
                'sn'=>null,
                'amt_net'=>0,
                'payment_date'=>null,
                'updated_at'=>date('Y-m-d H:i:s'),
            ]);

        } else {
            if($request['status_code'] == 0){
                foreach($request['accounts_payable_id'] as $key => $value){
                    if($request['action'] == 'new'){
                        $account = DB::table('acc_payable_account')->where('id', $value)->first();

                        if($account && $account->append_pay_order_id){
                            $parm = [
                                'append_pay_order_id' => $account->append_pay_order_id,
                            ];
                            self::update_account_payable_method($parm, true);

                            self::find($account->append_pay_order_id)->delete();
                        }

                        DB::table('acc_payable_account')->where('id', $value)->update([
                            'status_code'=>0,
                            'append_pay_order_id'=>$request['append_pay_order_id'],
                            'sn'=>$request['sn'],
                            'amt_net'=>$request['amt_net'][$key],
                            'payment_date'=>null,
                            'updated_at'=>date('Y-m-d H:i:s'),
                        ]);

                    } else if($request['action'] == 'reverse'){
                        DB::table('acc_payable_account')->where('id', $value)->update([
                            'status_code'=>0,
                            'payment_date'=>null,
                            'updated_at'=>date('Y-m-d H:i:s'),
                        ]);
                    }
                }

            } else if($request['status_code'] == 1){
                DB::table('acc_payable_account')->whereIn('id', $request['accounts_payable_id'])->update([
                    'status_code'=>1,
                    'append_pay_order_id'=>$request['append_pay_order_id'],
                    'sn'=>$request['sn'],
                    'payment_date'=>date('Y-m-d'),
                    'updated_at'=>date('Y-m-d H:i:s'),
                ]);
            }
        }
    }


    public static function payable_data_status_check($collection)
    {
        $check_result = false;
        foreach($collection as $value){
            if($value->cheque_status_code == 'cashed' || $value->account_sn != null){
                $check_result = true;
                break;
            }
        }

        return $check_result;
    }


    public static function paying_order_link($source_type, $source_id, $source_sub_id = null, $type)
    {
        $link = 'javascript:void(0);';

        if($source_type == 'pcs_purchase'){
            $link = route('cms.purchase.view-pay-order', ['id' => $source_id, 'type' => $type]);

        } else if($source_type == 'ord_orders' && $source_sub_id != null){
            $link = route('cms.order.logistic-po', ['id' => $source_id, 'sid' => $source_sub_id]);

        } else if($source_type == 'csn_consignment'){
            $link = route('cms.consignment.logistic-po', ['id' => $source_id]);

        } else if($source_type == 'acc_stitute_orders'){
            $link = route('cms.stitute.po-show', ['id' => $source_id]);

        } else if($source_type == 'ord_orders' && $source_sub_id == null){
            $link = route('cms.order.return-pay-order', ['id' => $source_id]);

        } else if($source_type == 'dlv_delivery'){
            $link = route('cms.delivery.return-pay-order', ['id' => $source_id]);

        } else if($source_type == 'pcs_paying_orders' && $type == 1){
            $link = route('cms.accounts_payable.po-show', ['id' => $source_id]);

        } else if($source_type == 'pcs_paying_orders' && $type == 2){
            $link = route('cms.collection_payment.po-show', ['id' => $source_id]);
        }

        return $link;
    }


    public static function paying_order_source_link($source_type, $source_id, $source_sub_id = null, $type, $back_domain = false)
    {
        $link = 'javascript:void(0);';

        if($back_domain){
            $link = '/';
        }

        if($source_type == 'pcs_purchase'){
            $link = route('cms.purchase.edit', ['id' => $source_id]);

        } else if($source_type == 'ord_orders' && $source_sub_id != null){
            $link = route('cms.order.detail', ['id' => $source_id, 'subOrderId' => $source_sub_id]);

        } else if($source_type == 'csn_consignment'){
            $link = route('cms.consignment.edit', ['id' => $source_id]);

        } else if($source_type == 'acc_stitute_orders'){
            $link = route('cms.stitute.show', ['id' => $source_id]);

        } else if($source_type == 'ord_orders' && $source_sub_id == null){
            $link = route('cms.order.detail', ['id' => $source_id]);

        } else if($source_type == 'dlv_delivery'){
            $dlv = Delivery::find($source_id);
            $link = route('cms.delivery.back_detail', ['event' => $dlv->event, 'eventId' => $dlv->event_id]);

        } else if($source_type == 'pcs_paying_orders' && $type == 1){
            $link = route('cms.accounts_payable.index');

        } else if($source_type == 'pcs_paying_orders' && $type == 2){
            $link = route('cms.collection_payment.index');
        }

        return $link;
    }


    public static function payee($id, $name)
    {
        $client = User::where([
                'id'=>$id,
            ])
            ->where('name', 'LIKE', "%{$name}%")
            ->select(
                'id',
                'name',
                'email'
            )
            ->selectRaw('
                IF(id IS NOT NULL, "", "") AS phone,
                IF(id IS NOT NULL, "", "") AS address
            ')
            ->first();

        if(! $client){
            $client = Customer::leftJoin('usr_customers_address AS customer_add', function ($join) {
                    $join->on('usr_customers.id', '=', 'customer_add.usr_customers_id_fk');
                    $join->where([
                        'customer_add.is_default_addr'=>1,
                    ]);
                })->where([
                    'usr_customers.id'=>$id,
                ])
                ->where('usr_customers.name', 'LIKE', "%{$name}%")
                ->select(
                    'usr_customers.id',
                    'usr_customers.name',
                    'usr_customers.phone AS phone',
                    'usr_customers.email',
                    'customer_add.address AS address'
                )->first();

            if(! $client){
                $client = Depot::where('id', '=', $id)
                    ->where('name', 'LIKE', "%{$name}%")
                    ->select(
                        'depot.id',
                        'depot.name',
                        'depot.tel AS phone',
                        'depot.address AS address'
                    )->first();

                if(! $client){
                    $client = Supplier::where([
                        'id'=>$id,
                    ])
                    ->where('name', 'LIKE', "%{$name}%")
                    ->select(
                        'id',
                        'name',
                        'contact_tel AS phone',
                        'email',
                        'contact_address AS address'
                    )->first();

                    if(! $client){
                        $client = (object)[
                            'id'=>'',
                            'name'=>'',
                            'phone'=>'',
                            'address'=>'',
                        ];
                    }
                }
            }
        }

        return $client;
    }


    public static function source_confirmation($source_type, $source_id)
    {
        $result = true;
        $po = null;

        if($source_type == app(Order::class)->getTable()){
            $po = self::where([
                'source_type'=>$source_type,
                'source_id'=>$source_id,
                'source_sub_id'=>null,
                'type'=>9,
            ])->first();

        } else if($source_type == app(Delivery::class)->getTable()){
            $po = self::where([
                'source_type'=>$source_type,
                'source_id'=>$source_id,
                'source_sub_id'=>null,
                'type'=>9,
            ])->first();
        }

        if($po){
            $result = false;
        }

        return $result;
    }


    public static function update_paying_order_append_to($request, $clear = false)
    {
        foreach($request['po_id'] as $po_id){
            $paying_order = self::where('id', $po_id)->first();

            if($clear){
                $paying_order->update([
                    'balance_date' => null,
                    'payment_date' => null,
                    'append_po_id' => null,
                    'append_po_sn' => null,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                if($paying_order->source_type == app(self::class)->getTable() && $paying_order->type == 1){
                    $accounts_payable_id = DB::table('acc_payable_account')->where('append_pay_order_id', $paying_order->id)->pluck('id')->toArray();

                    $parm = [
                        'action'=>'reverse',
                        'accounts_payable_id'=>$accounts_payable_id,
                        'status_code'=>0,
                    ];
                    self::update_account_payable_method($parm);
                }

            } else {
                if($request['action'] == 'new'){
                    if($paying_order->append_po_id && $paying_order->append_po_id != $request['append_po_id']){
                        self::where('append_po_id', $paying_order->append_po_id)->update([
                            'append_po_id'=>null
                        ]);

                        self::find($paying_order->append_po_id)->delete();
                    }

                    $paying_order->update([
                        'balance_date' => $request['balance_date'],
                        'payment_date' => $request['payment_date'],
                        'append_po_id' => $request['append_po_id'],
                        'append_po_sn' => $request['append_po_sn'],
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

                    if($request['payment_date'] && $paying_order->source_type == app(self::class)->getTable() && $paying_order->type == 1){
                        $accounts_payable_id = DB::table('acc_payable_account')->where('append_pay_order_id', $paying_order->id)->pluck('id')->toArray();

                        $parm = [
                            'accounts_payable_id'=>$accounts_payable_id,
                            'status_code'=>1,
                            'append_pay_order_id'=>$paying_order->id,
                            'sn'=>$paying_order->sn,
                        ];
                        self::update_account_payable_method($parm);
                    }

                } else if($request['action'] == 'reverse'){
                    $paying_order->update([
                        'balance_date' => null,
                        'payment_date' => null,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }
    }

    //同步物流成本
    public static function sync_logistic_cost($source_type, $id, $sid = null, $cost) {
        $whereQuery = [
            'source_type' => $source_type
            , 'source_id' => $id
            , 'deleted_at' => null
        ];
        //若為訂單則sub_id有值
        if (app(Order::class)->getTable() == $source_type) {
            $whereQuery['source_sub_id'] = $sid;
        } else {
            $whereQuery['source_sub_id'] = null;
        }
        PayingOrder::where($whereQuery)->update(['price' => $cost]);
    }

    //是否有退貨付款單
    public static function hasDeliveryBack($delivery_id) {
        $result = false;
        $query = PayingOrder::where('source_type', '=', app(Delivery::class)->getTable())
            ->where('source_id', '=', $delivery_id)
            ->whereNull('source_sub_id')
            ->whereNull('deleted_at')
            ->first();
        if (isset($query)) {
            $result = true;
        }
        return $result;
    }
}
