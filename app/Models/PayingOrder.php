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
        $p_order_sn = null,
        $purchase_sn = null,
        $p_order_price = null,
        $p_order_payment_date = null
    ){
        $paying_order = DB::table(DB::raw('(
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
                    balance_date,
                    payee_id,
                    payee_name,
                    payee_phone,
                    payee_address,
                    created_at
                FROM pcs_paying_orders
                WHERE deleted_at IS NULL
                GROUP BY source_id, source_sub_id
                ) AS po')
            )
            // purchase
            ->leftJoin('pcs_purchase as purchase', function ($join) {
                $join->on('po.source_id', '=', 'purchase.id');
                $join->where([
                    'po.source_type'=>app(Purchase::class)->getTable(),
                ]);
            })
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
                GROUP BY v_po.source_id, v_po.source_sub_id
                ) AS v_table_2'), function ($join){
                    $join->whereRaw('v_table_2.pay_order_id in (po.id)');
            })

            // order
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
                $join->on('po.source_id', '=', 'so.id');
                $join->where([
                    'po.source_type'=>app(StituteOrder::class)->getTable(),
                ]);
            })

            ->whereColumn([
                ['po.price', '=', 'v_table_2.payable_price'],
            ])
            // ->whereRaw('( (v_table_1.price + purchase.logistics_price) = v_table_2.payable_price OR dlv_logistic.cost = v_table_2.payable_price )')

            ->select(
                'po.source_id as po_source_id',
                'po.source_sub_id as po_source_sub_id',
                'po.usr_users_id as po_usr_users_id',
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
                'po.created_at as po_created_at',

                'purchase.id as purchase_id',
                // 'purchase.sn as purchase_sn',
                'purchase.supplier_name as purchase_supplier_name',
                // 'purchase.logistics_price as purchase_logistics_price',//運費
                'purchase.logistics_memo as purchase_logistics_memo',
                'purchase.invoice_num as purchase_invoice_num',
                'purchase.invoice_date as purchase_invoice_date',
                'purchase.close_date as purchase_close_date',
                'purchase.audit_date as purchase_audit_date',

                'user.name as purchaser',
                'audit.name as auditor',

                // 'supplier.id as supplier_id_p',
                // 'supplier.name as supplier_name_p',
                // 'supplier.contact_person as supplier_contact_person_p',

                'v_table_1.price as product_price_sum',//採購商品金額總計(未含運費)
                'v_table_1.item_list as product_list',

                'v_table_2.payment_date as payment_date',// 付款單完成付款日期
                'v_table_2.payable_price as payable_price',// 付款單金額(實付)
                'v_table_2.pay_list as payable_list',

                // 'dlv_delivery.event_sn as order_sub_sn',
                // 'dlv_logistic.cost as order_sub_cost',

                // 'prd_suppliers.id as supplier_id_o',
                // 'prd_suppliers.name as supplier_name_o',
                // 'prd_suppliers.contact_person as supplier_contact_person_o',
            )
            ->selectRaw('IF(purchase.sn IS NULL, dlv_delivery.event_sn, purchase.sn) as purchase_order_sn') // purchase_sn or order_sub_sn
            ->selectRaw('IF(purchase.logistics_price IS NULL, dlv_logistic.cost, purchase.logistics_price) as logistics_price')
            ->selectRaw('IF(supplier.id IS NULL, prd_suppliers.id, supplier.id) as supplier_id')
            ->selectRaw('IF(supplier.name IS NULL, prd_suppliers.name, supplier.name) as supplier_name')
            ->selectRaw('IF(supplier.contact_person IS NULL, prd_suppliers.contact_person, supplier.contact_person) as supplier_contact_person');
            // ->selectRaw('DATE_FORMAT(purchase.created_at, "%Y-%m-%d") as purchase_date');

        if ($payee) {
            if (gettype($payee) == 'array') {
                $paying_order->where([
                        'po.payee_id'=>$payee['id'],
                    ])->where('po.payee_name', 'like', "%{$payee['name']}%");
            }
        }

        if ($p_order_sn) {
            $paying_order->where(function ($query) use ($p_order_sn) {
                $query->where('po.sn', 'like', "%{$p_order_sn}%");
            });
        }

        if ($purchase_sn) {
            $paying_order->where(function ($query) use ($purchase_sn) {
                $query->where('purchase.sn', 'like', "%{$purchase_sn}%")
                    ->orWhere('dlv_delivery.event_sn', 'like', "%{$purchase_sn}%");
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

        return $paying_order->orderBy('po.created_at', 'DESC');
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
