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
        $check_balance = 'all'
    ){
        $query = DB::table(DB::raw('(
                SELECT
                    GROUP_CONCAT(id) AS id,
                    source_type,
                    source_id,
                    source_sub_id,
                    GROUP_CONCAT(DISTINCT usr_users_id) AS usr_users_id,
                    GROUP_CONCAT(type) AS type,
                    GROUP_CONCAT(sn) AS sn,
                    SUM(price) AS price,
                    GROUP_CONCAT(DISTINCT logistics_grade_id) AS logistics_grade_id,
                    GROUP_CONCAT(DISTINCT product_grade_id) AS product_grade_id,
                    CASE WHEN COUNT(*) = COUNT(balance_date) THEN MAX(balance_date) END AS balance_date,
                    payee_id,
                    payee_name,
                    payee_phone,
                    payee_address,
                    created_at
                FROM pcs_paying_orders
                WHERE deleted_at IS NULL
                GROUP BY source_type, source_id, source_sub_id
                ) AS po')
            )
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
                        "summary":"\', COALESCE(acc_payable.summary, ""), \'",
                        "note":"\', COALESCE(note, ""), \'"
                    }\' ORDER BY acc_payable.id), \']\') AS pay_list
                FROM acc_payable
                LEFT JOIN pcs_paying_orders AS v_po ON v_po.id = acc_payable.pay_order_id WHERE v_po.deleted_at IS NULL
                GROUP BY v_po.source_type, v_po.source_id, v_po.source_sub_id
                ) AS payable_table'), function ($join){
                    $join->whereRaw('payable_table.pay_order_id in (po.id)');
            })

            // purchase
            ->leftJoin('pcs_purchase as purchase', function ($join) {
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
                        "price":"\', pcs_purchase_items.price, \'",
                        "num":"\', pcs_purchase_items.num, \'"
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
                    id,
                    CONCAT(\'[\', GROUP_CONCAT(\'{
                        "product_owner":"\', "", \'",
                        "title":"\', "代墊單", \'",
                        "sku":"\', "", \'",
                        "price":"\', total_price, \'",
                        "num":"\', qty, \'"
                    }\' ORDER BY id), \']\') AS items
                FROM acc_stitute_orders
                WHERE deleted_at IS NULL
                GROUP BY id
                ) AS stitute_table'), function ($join){
                    $join->on('so.id', '=', 'stitute_table.id');
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
                        "price":"\', ord_items.price * ord_items.qty, \'",
                        "num":"\', ord_items.qty, \'"
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
                        "title":"\', COALESCE(title, ""), \'",
                        "sn":"\', COALESCE(sn, ""), \'",
                        "category_title":"\', category_title, \'",
                        "category_code":"\', category_code, \'",
                        "extra_id":"\', COALESCE(extra_id, ""), \'",
                        "extra_title":"\', COALESCE(extra_title, ""), \'",
                        "discount_value":"\', COALESCE(discount_value, ""), \'"
                    }\' ORDER BY ord_discounts.id), \']\') AS items
                FROM ord_discounts
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
                        "price":"\', dlv_back.price * dlv_back.qty, \'",
                        "num":"\', dlv_back.qty, \'"
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
                'po.product_grade_id as po_product_grade_id',
                'po.balance_date AS po_balance_date',
                'po.payee_id AS po_target_id',
                'po.payee_name AS po_target_name',
                'po.payee_phone AS po_target_phone',
                'po.payee_address AS po_target_address',

                'payable_table.payment_date as payment_date',// 付款單完成付款日期
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
                    WHEN po.source_type = "' . app(StituteOrder::class)->getTable() . '" AND po.type = 1 THEN so.sn
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.type = 9 THEN order_return.sn
                    WHEN po.source_type = "' . app(Delivery::class)->getTable() . '" AND po.type = 9 THEN sub_order_return.event_sn
                    ELSE NULL
                END AS source_sn
            ')
            ->selectRaw('
                CASE
                    WHEN po.source_type = "' . app(Purchase::class)->getTable() . '" AND po.source_sub_id IS NULL THEN purchase_item_table.items
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.source_sub_id IS NOT NULL AND po.type = 1 THEN NULL
                    WHEN po.source_type = "' . app(StituteOrder::class)->getTable() . '" AND po.type = 1 THEN stitute_table.items
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.type = 9 THEN order_item_table.items
                    WHEN po.source_type = "' . app(Delivery::class)->getTable() . '" AND po.type = 9 THEN delivery_back.items
                    ELSE NULL
                END AS product_items
            ')
            ->selectRaw('
                CASE
                    WHEN po.source_type = "' . app(Purchase::class)->getTable() . '" AND po.source_sub_id IS NULL THEN purchase.logistics_price
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.source_sub_id IS NOT NULL AND po.type = 1 THEN dlv_logistic.cost
                    WHEN po.source_type = "' . app(StituteOrder::class)->getTable() . '" AND po.type = 1 THEN 0
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.type = 9 THEN order_return.dlv_fee
                    WHEN po.source_type = "' . app(Delivery::class)->getTable() . '" AND po.type = 9 THEN 0
                    ELSE 0
                END AS logistics_price
            ')
            ->selectRaw('
                CASE
                    WHEN po.source_type = "' . app(Purchase::class)->getTable() . '" AND po.source_sub_id IS NULL THEN 0
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.source_sub_id IS NOT NULL AND po.type = 1 THEN 0
                    WHEN po.source_type = "' . app(StituteOrder::class)->getTable() . '" AND po.type = 1 THEN 0
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.type = 9 THEN order_return.discount_value
                    WHEN po.source_type = "' . app(Delivery::class)->getTable() . '" AND po.type = 9 THEN 0
                    ELSE 0
                END AS discount_value
            ')
            ->selectRaw('
                CASE
                    WHEN po.source_type = "' . app(Purchase::class)->getTable() . '" AND po.source_sub_id IS NULL THEN NULL
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.source_sub_id IS NOT NULL AND po.type = 1 THEN NULL
                    WHEN po.source_type = "' . app(StituteOrder::class)->getTable() . '" AND po.type = 1 THEN NULL
                    WHEN po.source_type = "' . app(Order::class)->getTable() . '" AND po.type = 9 THEN discounts_table.items
                    WHEN po.source_type = "' . app(Delivery::class)->getTable() . '" AND po.type = 9 THEN NULL
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
            $query->where(function ($query) use ($po_sn) {
                $query->where('po.sn', 'like', "%{$po_sn}%");
            });
        }

        if ($source_sn) {
            $query->where(function ($query) use ($source_sn) {
                $query->where('purchase.sn', 'like', "%{$source_sn}%")
                    ->orWhere('dlv_delivery.event_sn', 'like', "%{$source_sn}%")
                    ->orWhere('so.sn', 'like', "%{$source_sn}%")
                    ->orWhere('order_return.sn', 'like', "%{$source_sn}%")
                    ->orWhere('sub_order_return.event_sn', 'like', "%{$source_sn}%");
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
                // $query->where('payable_table.payment_date', '>=', $s_payment_date);
                $query->where('po.balance_date', '>=', $s_payment_date);
            }
            if($e_payment_date){
                // $query->where('payable_table.payment_date', '<', $e_payment_date);
                $query->where('po.balance_date', '<', $e_payment_date);
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


    public static function get_payable_detail($pay_order_id = null, int $method_id = null)
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

                payable.id AS payable_id,
                payable.pay_order_id,
                payable.acc_income_type_fk,
                payable.all_grades_id,
                payable.tw_price,
                payable.accountant_id_fk,
                payable.taxation,
                payable.summary,
                payable.note
            ')
            // ->selectRaw('
            //     _cash.cardnumber AS credit_card_number,
            // ')

            ->selectRaw('
                _cheque.maturity_date AS cheque_maturity_date,
                _cheque.cash_cheque_date AS cheque_cash_cheque_date,
                _cheque.cheque_status AS cheque_cheque_status
            ')

            ->selectRaw('
                _currency.rate AS rate,
                _currency.foreign_currency AS currency_foreign,
                _currency.acc_currency_fk AS currency_fk
            ')

            ->selectRaw('
                _remit.remit_date  AS remit_date
            ')

            // ->selectRaw('
            //     _account.status_code AS account_status_code,
            // ')
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
}
