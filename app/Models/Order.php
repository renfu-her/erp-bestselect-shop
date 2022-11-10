<?php

namespace App\Models;

use App\Enums\Customer\ProfitStatus;
use App\Enums\Delivery\Event;
use App\Enums\Delivery\LogisticStatus;
use App\Enums\Discount\DividendCategory;
use App\Enums\Discount\DividendFlag;
use App\Enums\Order\CarrierType;
use App\Enums\Order\InvoiceMethod;
use App\Enums\Order\OrderStatus;
use App\Enums\Order\PaymentStatus;
use App\Enums\Order\UserAddrType;
use App\Enums\Received\ReceivedMethod;
use App\Mail\OrderEstablished;
use App\Mail\OrderPaid;
use App\Mail\OrderShipped;
use App\Models\CustomerDividend;
use App\Models\OrderCart;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class Order extends Model
{
    use HasFactory;
    protected $table = 'ord_orders';
    protected $guarded = [];

    public static function orderList($keyword = null,
        $order_status = null,
        $sale_channel_id = null,
        $order_date = null,
        $shipment_status = null,
        $profit_user = null,
        $email = null,
        $item_title = null,
        $purchase_sn = null,
        $received_method = null,
        $dlv_date = null,
        $has_back_sn = null
    ) {
        $order = DB::table('ord_orders as order')
            ->select(['order.id as id',
                'order.sn as main_order_sn',
                'order.status as order_status',
                'order.payment_method',
                'order.payment_method_title',
                'ord_address.name',
                'sale.title as sale_title',
                'so.ship_category_name',
                'so.ship_event',
                'so.ship_sn',
                'so.total_price',
                'dlv_delivery.logistic_status as logistic_status',
                'dlv_logistic.package_sn as package_sn',
                'shi_group.name as ship_group_name',
                'ord_received_orders.sn as or_sn',
                'so.projlgt_order_sn',
                'so.dlv_audit_date',
//                'so.package_sn',
                'ord_items.product_title',
                'ord_items.sub_order_id',
            ])
            ->selectRaw('DATE_FORMAT(order.created_at,"%Y-%m-%d") as order_date')
            ->selectRaw('so.sn as order_sn')
            ->leftJoin('ord_sub_orders as so', 'order.id', '=', 'so.order_id')
            ->leftJoin('ord_items', 'so.id', '=', 'ord_items.sub_order_id')
            ->leftJoin('usr_customers as customer', 'order.email', '=', 'customer.email')
            ->leftJoin('prd_sale_channels as sale', 'sale.id', '=', 'order.sale_channel_id')
            ->leftJoin('ord_address', function ($join) {
                $join->on('ord_address.order_id', '=', 'order.id')
                    ->where('ord_address.type', '=', UserAddrType::orderer);
            })
            ->leftJoin('dlv_delivery', function ($join) {
                $join->on('dlv_delivery.event_id', '=', 'so.id');
                $join->where('dlv_delivery.event', '=', Event::order()->value);
            })
            ->leftJoin('dlv_logistic', function ($join) {
                $join->on('dlv_logistic.delivery_id', '=', 'dlv_delivery.id');
            })
            ->leftJoin('shi_group', function ($join) {
                $join->on('shi_group.id', '=', 'dlv_logistic.ship_group_id');
                $join->whereNotNull('dlv_logistic.ship_group_id');
            })
            ->leftJoin('ord_received_orders', function ($join) {
                $join->on('order.id', '=', 'ord_received_orders.source_id');
                $join->where([
                    'ord_received_orders.source_type' => app(Order::class)->getTable(),
                    'ord_received_orders.deleted_at' => null,
                ]);
            })
        ;

        if ($keyword) {
            $order->where(function ($query) use ($keyword) {
                $query->Where('so.sn', 'like', "%{$keyword}%")
                    ->orWhere('ord_address.name', 'like', "%{$keyword}%")
                    ->orWhere('ord_address.phone', 'like', "%{$keyword}%");
            });
        }

        if ($email) {
            $order->where('order.email', $email);
        }

        if ($sale_channel_id) {
            if (gettype($sale_channel_id) == 'array') {
                $order->whereIn('order.sale_channel_id', $sale_channel_id);
            } else {
                $order->where('order.sale_channel_id', $sale_channel_id);
            }
        }

        if ($order_status) {
            if (gettype($order_status) == 'array') {
                $order->whereIn('order.status_code', $order_status);
            } else {
                $order->where('order.status_code', $order_status);
            }
        }

        if ($received_method) {
            if (gettype($received_method) == 'array') {
                $order->whereIn('order.payment_method', $received_method);
            } else {
                $order->where('order.payment_method', $received_method);
            }
        }

        if ($order_date) {
            if (gettype($order_date) == 'array' && count($order_date) == 2) {
                $sDate = date('Y-m-d 00:00:00', strtotime($order_date[0]));
                $eDate = date('Y-m-d 23:59:59', strtotime($order_date[1]));
                $order->whereBetween('order.created_at', [$sDate, $eDate]);
            }
        }

        if ($dlv_date) {
            if (gettype($dlv_date) == 'array' && count($dlv_date) == 2) {
                $sDate = date('Y-m-d 00:00:00', strtotime($dlv_date[0]));
                $eDate = date('Y-m-d 23:59:59', strtotime($dlv_date[1]));
                $order->whereBetween('so.dlv_audit_date', [$sDate, $eDate]);
            }
        }

        if ($shipment_status && is_array($shipment_status)) {
            $order->whereIn('so.logistic_status_code', $shipment_status);
        }

        $order->leftJoin('ord_order_profit as ord_profit', 'ord_profit.order_id', '=', 'order.id');
        if ($profit_user) {
            $order->where('ord_profit.customer_id', $profit_user);
        }

        if ($item_title) {
            $order->where(function ($query) use ($item_title) {
                $query->Where('ord_items.product_title', 'like', "%{$item_title}%")
                    ->orWhere('ord_items.sku', 'like', "%{$item_title}%");
            });
        }

        if ($purchase_sn) {
            //整理出入庫單和採購單的關係
            $inbound = DB::table(app(PurchaseInbound::class)->getTable() . ' as inbound')
                ->leftJoin('pcs_purchase as pcs', function ($join) {
                    $join->on('pcs.id', '=', 'inbound.event_id')
                        ->where('inbound.event', '=', Event::purchase()->value);
                })
                ->select('pcs.id as pcs_id', 'pcs.sn as pcs_sn', 'inbound.*')
                ->whereNull('inbound.deleted_at');

            //找出子訂單出貨的商品
            $order->leftJoin('dlv_receive_depot as dlv_receive_depot', function ($join) {
                $join->on('dlv_receive_depot.delivery_id', '=', 'dlv_delivery.id');
            })
                ->leftJoinSub($inbound, 'inbound', function ($join) {
                    $join->on('inbound.id', '=', 'dlv_receive_depot.inbound_id');
                })
                ->whereNull('dlv_receive_depot.deleted_at');

            $order->where(function ($query) use ($purchase_sn) {
                $query->Where('inbound.pcs_sn', '=', "$purchase_sn");
            });

        }

        if ($has_back_sn) {
            $order->whereNotNull('dlv_delivery.back_sn');
        }

        $order->orderByDesc('order.id');

        return [
            'dataList' => $order,
        ];
//           dd($order->get()->toArray());
    }
    // 簡易版
    public static function orderListSimple(
        $email
    ) {
        $order = DB::table('ord_orders as order')
            ->select(['order.id as id',
                'order.sn as main_order_sn',
                'order.status as order_status',
                'order.payment_method',
                'order.payment_method_title',
                'ord_address.name',
                'sale.title as sale_title',
                'so.ship_category_name',
                'so.ship_event',
                'so.ship_sn',
                'so.total_price',
                'dlv_delivery.logistic_status as logistic_status',
                'dlv_logistic.package_sn as package_sn',
                'shi_group.name as ship_group_name',
                'ord_received_orders.sn as or_sn',
                'so.projlgt_order_sn',
                'so.dlv_audit_date'
            ])
            ->selectRaw('DATE_FORMAT(order.created_at,"%Y-%m-%d") as order_date')
            ->selectRaw('so.sn as order_sn')
            ->leftJoin('ord_sub_orders as so', 'order.id', '=', 'so.order_id')
            ->leftJoin('usr_customers as customer', 'order.email', '=', 'customer.email')
            ->leftJoin('prd_sale_channels as sale', 'sale.id', '=', 'order.sale_channel_id')
            ->leftJoin('ord_address', function ($join) {
                $join->on('ord_address.order_id', '=', 'order.id')
                    ->where('ord_address.type', '=', UserAddrType::orderer);
            })
            ->leftJoin('dlv_delivery', function ($join) {
                $join->on('dlv_delivery.event_id', '=', 'so.id');
                $join->where('dlv_delivery.event', '=', Event::order()->value);
            })
            ->leftJoin('dlv_logistic', function ($join) {
                $join->on('dlv_logistic.delivery_id', '=', 'dlv_delivery.id');
            })
            ->leftJoin('shi_group', function ($join) {
                $join->on('shi_group.id', '=', 'dlv_logistic.ship_group_id');
                $join->whereNotNull('dlv_logistic.ship_group_id');
            })
            ->leftJoin('ord_received_orders', function ($join) {
                $join->on('order.id', '=', 'ord_received_orders.source_id');
                $join->where([
                    'ord_received_orders.source_type' => app(Order::class)->getTable(),
                    'ord_received_orders.deleted_at' => null,
                ]);
            });

        if ($email) {
            $order->where('order.email', $email);
        }

        $order->orderByDesc('order.id');

        return $order;

    }

    public static function orderDetail($order_id, $email = null)
    {
        $orderQuery = DB::table('ord_orders as order')
            ->leftJoin('usr_customers as customer', 'order.email', '=', 'customer.email')
            ->leftJoin('prd_sale_channels as sale', 'sale.id', '=', 'order.sale_channel_id')
            ->leftJoin('usr_customers as customer_m', function ($join) {
                $join->on('customer_m.sn', '=', 'order.mcode')
                    ->whereNotNull('order.mcode');
            })
            ->leftJoin('pcs_paying_orders as po', function ($join) {
                $join->on('po.source_id', '=', 'order.id');
                $join->where([
                    'po.source_type' => app(Order::class)->getTable(),
                    'po.source_sub_id' => null,
                    'po.type' => 9,
                    'po.deleted_at' => null,
                ]);
            })
            ->select([
                'order.id',
                'order.sn',
                'order.discount_value',
                'order.discounted_price',
                'order.dlv_fee',
                'order.origin_price',
                'order.status',
                'order.allotted_dividend',
                'order.auto_dividend',
                'order.total_price',
                'order.created_at',
                'order.category',
                'order.invoice_category',
                'order.carrier_type as order_carrier_type',
                'order.carrier_num',
                'order.inv_title',
                'order.buyer_ubn',
                DB::raw('(case when "' . CarrierType::mobile()->value . '" = order.carrier_type then "' . CarrierType::getDescription(CarrierType::mobile) . '"
                    when "' . CarrierType::certificate()->value . '" = order.carrier_type then "' . CarrierType::getDescription(CarrierType::certificate) . '"
                    when "' . CarrierType::member()->value . '" = order.carrier_type then "' . CarrierType::getDescription(CarrierType::member) . '"
                    else order.carrier_type end) as carrier_type'),
                DB::raw('ifnull(order.carrier_num, "") as carrier_num'),
                'customer.name',
                'customer.email',
                'customer_m.name as name_m',
                'customer_m.sn as sn_m',
                'order.dividend_active_at',
                'sale.title as sale_title'])
            ->selectRaw("IF(order.inv_title IS NULL,'',order.inv_title) as inv_title")
            ->selectRaw("IF(order.buyer_ubn IS NULL,'',order.buyer_ubn) as buyer_ubn")
            ->selectRaw("IF(order.unique_id IS NULL,'',order.unique_id) as unique_id")
            ->selectRaw("IF(order.carrier_num IS NULL,'',order.carrier_num) as carrier_num")
            ->selectRaw("IF(order.invoice_category IS NULL,'',order.invoice_category) as invoice_category")
            ->selectRaw("IF(order.invoice_number IS NULL,'',order.invoice_number) as invoice_number")
            ->selectRaw("IF(order.note IS NULL,'',order.note) as note")
            ->selectRaw("IF(order.payment_status IS NULL,'',order.payment_status) as payment_status")
            ->selectRaw("IF(order.payment_status_title IS NULL,'',order.payment_status_title) as payment_status_title")
            ->selectRaw("IF(order.payment_method IS NULL,'',order.payment_method) as payment_method")
            ->selectRaw("IF(order.payment_method_title IS NULL,'',order.payment_method_title) as payment_method_title")
            ->selectRaw("IF(order.dlv_taxation IS NULL,'',order.dlv_taxation) as dlv_taxation")
            ->selectRaw("IF(po.id IS NOT NULL, po.id, '') as return_pay_order_id")
            ->selectRaw("IF(po.sn IS NOT NULL, po.sn, '') as return_pay_order_sn")

            ->where('order.id', $order_id);

        if ($email) {
            $orderQuery->where('order.email', $email);
        }
        self::orderAddress($orderQuery);

        return $orderQuery;
    }

    public static function subOrderDetail($order_id, $sub_order_id = null, $get_logistic_paying = null)
    {
        $concatString = concatStr([
            'product_id' => 'product.id',
            'product_title' => 'item.product_title',
            'product_sku' => 'product.sku',
            'product_taxation' => 'product.has_tax',
            'sku' => 'item.sku',
            'price' => 'item.price',
            'qty' => 'item.qty',
            'style_id' => 'item.product_style_id',
            'img_url' => 'IF(item.img_url IS NULL,"",item.img_url)',
            'total_price' => 'item.origin_price',
            'note' => 'IF(item.note IS NULL,"",item.note)',
            'ro_note' => 'IF(item.ro_note IS NULL,"",item.ro_note)',
            'po_note' => 'IF(item.po_note IS NULL,"",item.po_note)',
            'dealer_price' => 'item.dealer_price',
            'item_id' => 'item.id',
        ]);

        $itemQuery = DB::table('ord_items as item')
            ->leftJoin('prd_product_styles as style', 'item.product_style_id', '=', 'style.id')
            ->leftJoin('prd_products as product', 'style.product_id', '=', 'product.id')
            ->groupBy('item.sub_order_id')
            ->select('item.sub_order_id')
            ->selectRaw($concatString . ' as items')
            ->where('item.order_id', $order_id);

        $concatConsumeString = concatStr([
            'consum_id' => 'dlv_consum.id',
            'inbound_id' => 'dlv_consum.inbound_id',
            'inbound_sn' => 'dlv_consum.inbound_sn',
            'depot_id' => 'dlv_consum.depot_id',
            'depot_name' => 'dlv_consum.depot_name',
            'product_style_id' => 'dlv_consum.product_style_id',
            'sku' => 'dlv_consum.sku',
            'product_title' => 'dlv_consum.product_title',
            'qty' => 'dlv_consum.qty',
            'back_qty' => 'dlv_consum.back_qty']);

        $itemConsumeQuery = DB::table('dlv_logistic')
            ->leftJoin('dlv_consum', 'dlv_consum.logistic_id', '=', 'dlv_logistic.id')
            ->select('dlv_consum.logistic_id')
            ->selectRaw($concatConsumeString . ' as consume_items')
            ->whereNotNull('dlv_logistic.audit_date')
            ->groupBy('dlv_consum.logistic_id');

        if ($sub_order_id) {
            $itemQuery->where('item.sub_order_id', $sub_order_id);
        }

        $orderQuery = DB::table('ord_sub_orders as sub_order')
            ->leftJoinSub($itemQuery, 'i', function ($join) {
                $join->on('sub_order.id', '=', 'i.sub_order_id');
            })
//->mergeBindings($itemQuery) ;

            ->leftJoin('dlv_delivery', function ($join) {
                $join->on('dlv_delivery.event_id', '=', 'sub_order.id');
                $join->where('dlv_delivery.event', '=', Event::order()->value);
            })
            ->leftJoin('dlv_logistic', function ($join) {
                $join->on('dlv_logistic.delivery_id', '=', 'dlv_delivery.id');
            })
            ->leftJoin('shi_group', function ($join) {
                $join->on('shi_group.id', '=', 'dlv_logistic.ship_group_id');
                $join->whereNotNull('dlv_logistic.ship_group_id');
            })
            ->leftJoin('shi_method', function ($join) {
                $join->on('shi_group.method_fk', '=', 'shi_method.id');
            })
            ->leftJoin('prd_suppliers', function ($join) {
                $join->on('prd_suppliers.id', '=', 'shi_group.supplier_fk');
                $join->whereNotNull('shi_group.supplier_fk');
            })
            ->leftJoinSub($itemConsumeQuery, 'consume_items', function ($join) {
                $join->on('consume_items.logistic_id', '=', 'dlv_logistic.id');
            })
            ->leftJoin('ord_received_orders', function ($join) {
                $join->on('sub_order.order_id', '=', 'ord_received_orders.source_id');
                $join->where([
                    'ord_received_orders.source_type' => app(Order::class)->getTable(),
                    'ord_received_orders.deleted_at' => null,
                ]);
            })
            ->select('sub_order.*', 'i.items'
                , 'dlv_delivery.sn as delivery_sn'
                , 'dlv_delivery.logistic_status as logistic_status'
                , 'dlv_delivery.audit_date as delivery_audit_date'
                , 'consume_items.consume_items'
                , 'ord_received_orders.sn as received_sn'
                , 'sub_order.projlgt_order_sn'

            )
            ->selectRaw("IF(sub_order.ship_sn IS NULL,'',sub_order.ship_sn) as ship_sn")
            ->selectRaw("IF(sub_order.actual_ship_group_id IS NULL,'',sub_order.actual_ship_group_id) as actual_ship_group_id")
            ->selectRaw("IF(sub_order.statu IS NULL,'',sub_order.statu) as statu")
            ->selectRaw("IF(sub_order.statu_code IS NULL,'',sub_order.statu_code) as statu_code")
            ->selectRaw("IF(sub_order.close_date IS NULL,'',DATE_FORMAT(sub_order.close_date,'%Y-%m-%d')) as close_date")
            ->selectRaw("IF(dlv_logistic.id IS NULL,'',dlv_logistic.id) as logistic_id")
            ->selectRaw("IF(dlv_logistic.sn IS NULL,'',dlv_logistic.sn) as logistic_sn")
            ->selectRaw("IF(dlv_logistic.package_sn IS NULL,'',dlv_logistic.package_sn) as package_sn")
            ->selectRaw("IF(dlv_logistic.ship_group_id IS NULL,'',dlv_logistic.ship_group_id) as ship_group_id")
            ->selectRaw("IF(dlv_logistic.cost IS NULL,'',dlv_logistic.cost) as logistic_cost")
            ->selectRaw("IF(dlv_logistic.memo IS NULL,'',dlv_logistic.memo) as logistic_memo")
            ->selectRaw("IF(dlv_logistic.po_note IS NULL,'',dlv_logistic.po_note) as logistic_po_note")
        // ->selectRaw("IF(dlv_logistic.projlgt_order_sn IS NULL,'',dlv_logistic.projlgt_order_sn) as projlgt_order_sn")
            ->selectRaw("IF(shi_group.name IS NULL,'',shi_group.name) as ship_group_name")
            ->selectRaw("IF(shi_group.note IS NULL,'',shi_group.note) as ship_group_note")
            ->selectRaw("IF(shi_method.method IS NULL,'',shi_method.method) as ship_method")
            ->selectRaw("IF(prd_suppliers.id IS NULL,'',prd_suppliers.id) as supplier_id")
            ->selectRaw("IF(prd_suppliers.name IS NULL,'',prd_suppliers.name) as supplier_name")
            ->selectRaw("IF(sub_order.ship_temp IS NULL,'',sub_order.ship_temp) as ship_temp")
            ->selectRaw("IF(sub_order.ship_temp_id IS NULL,'',sub_order.ship_temp_id) as ship_temp_id")
            ->selectRaw("IF(sub_order.ship_rule_id IS NULL,'',sub_order.ship_rule_id) as ship_rule_id")
            ->where('sub_order.order_id', $order_id);

        if ($sub_order_id) {
            $orderQuery->where('sub_order.id', $sub_order_id);
        }

        if ($get_logistic_paying) {
            $orderQuery->leftJoin('pcs_paying_orders as po', function ($join) {
                $join->on('po.source_id', '=', 'sub_order.order_id');
                $join->where([
                    'po.source_sub_id' => DB::raw('sub_order.id'),
                    'po.source_type' => app(Order::class)->getTable(),
                    'po.type' => 1,
                    'po.deleted_at' => null,
                ]);
            })
            // ->selectRaw("('" . app(Order::class)->getTable() . "') as payable_source_type")
                ->selectRaw("IF(po.sn IS NULL, NULL, po.sn) as logistic_po_sn")
                ->selectRaw("IF(po.balance_date IS NULL, NULL, po.balance_date) as logistic_po_balance_date")
                ->selectRaw("IF(po.created_at IS NULL, NULL, po.created_at) as logistic_po_created_at");
        }

        return $orderQuery;

    }

    public static function orderAddress(&$query, $joinTable = 'order', $joinKey = 'order_id')
    {
        foreach (UserAddrType::asArray() as $value) {
            $query->leftJoin('ord_address as ' . $value, function ($q) use ($value, $joinTable, $joinKey) {
                $q->on($joinTable . '.id', '=', $value . '.' . $joinKey)
                    ->where($value . '.type', '=', $value);
            });
            switch ($value) {
                case UserAddrType::receiver()->value:
                    $prefix = 'rec_';
                    break;
                case UserAddrType::orderer()->value:
                    $prefix = 'ord_';
                    break;
                case UserAddrType::sender()->value:
                    $prefix = 'sed_';
                    break;
            }
            $_name = "IF($value.name IS NULL,'',$value.name) as ${prefix}name";
            $_address = "IF($value.address IS NULL,'',$value.address) as ${prefix}address";
            $_phone = "IF($value.phone IS NULL,'',$value.phone) as ${prefix}phone";
            $_zipcode = "IF($value.zipcode IS NULL,'',$value.zipcode) as ${prefix}zipcode";

            $query->selectRaw($_name);
            $query->selectRaw($_address);
            $query->selectRaw($_phone);
            $query->selectRaw($_zipcode);

        }
    }

    /**
     * @param string $email
     * @param string $sale_channel_id
     * @param array $address
     * @param array $items
     * @param string $note
     * @param array $coupon_obj [type,value]
     *
     */
    public static function createOrder($email, $sale_channel_id, $address, $items, $mcode = null, $note = null, $coupon_obj = null, $payinfo = null, $payment = null, $dividend = [], $operator_user)
    {

        $order_sn = Sn::createSn('order', 'O');
        DB::beginTransaction();

        $customer = Customer::where('email', $email)->get()->first();
        if (isset($mcode) && !empty($mcode)) {
            $customerM = Customer::where('sn', $mcode)->get()->first();
            if (null == $customerM) {
                DB::rollBack();
                return ['success' => '0', 'error_msg' => '無此推薦人'];
            }
        } else {
            $mcode = null;
        }
        $order = OrderCart::cartFormater($items, $sale_channel_id, $coupon_obj, true, $customer, $dividend);

        if ($order['success'] != 1) {
            DB::rollBack();
            return $order;
        }
        /*
        $order_sn = "O" . date("Ymd") . str_pad((self::whereDate('created_at', '=', date('Y-m-d'))
        ->get()
        ->count()) + 1, 4, '0', STR_PAD_LEFT);
         */

        $order['order_sn'] = $order_sn;
        // 處理紅利
        $dividend_re = CustomerDividend::orderDiscount($customer->id, $order); //$order_sn, $order['use_dividend']
        // dd($order);
        if ($dividend_re['success'] != '1') {
            DB::rollBack();
            return $dividend_re;
        }

        $dividendSetting = DividendSetting::getData();

        $updateData = [
            "sn" => $order_sn,
            "sale_channel_id" => $sale_channel_id,
            "email" => $email,
            "total_price" => $order['total_price'],
            "origin_price" => $order['origin_price'],
            "mcode" => $mcode ?? null,
            "dlv_fee" => $order['dlv_fee'],
            "discount_value" => $order['discount_value'],
            "discounted_price" => $order['discounted_price'],
            'note' => $note,
            'unique_id' => self::generate_unique_id(),
            'payment_status' => PaymentStatus::Unpaid()->value,
            'payment_status_title' => PaymentStatus::Unpaid()->description,
            'dividend_lifecycle' => $dividendSetting->limit_day,
            'active_delay_day' => $dividendSetting->auto_active_day,
            'love_code' => $love_code ?? null,
            'carrier_type' => $carrier_type ?? null,
            'carrier_num' => $carrier_num ?? null,
        ];

        if ($payment) {
            $updateData['payment_method'] = $payment->value;
            $updateData['payment_method_title'] = $payment->description;
        }

        $order_id = self::create($updateData)->id;
        $order['order_id'] = $order_id;
        Discount::createOrderDiscount('main', $order_id, $customer, $order['discounts']);

        foreach ($address as $key => $user) {

            $city = Addr::where('id', $user['city_id'])->get()->first();
            $region = Addr::where('id', $user['region_id'])->get()->first();
            $city_title = $city ? $city->title : '';
            $region_title = $region ? $region->title : '';
            $zipcode = $region ? $region->zipcode : '';
            $_address = $zipcode . " " . $city_title . $region_title . $user['address'];
            //   dd($user['city_id']);
            $address[$key]['city_id'] = $user['city_id'];
            $address[$key]['city_title'] = $city_title;
            $address[$key]['region_id'] = $user['region_id'];
            $address[$key]['region_title'] = $region_title;
            $address[$key]['zipcode'] = $zipcode;
            $address[$key]['addr'] = $user['address'];
            $address[$key]['address'] = $_address;
            $address[$key]['order_id'] = $order_id;
        }
        try {
            DB::table('ord_address')->insert($address);
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => '0', 'error_msg' => 'address format error', 'event' => 'address', 'event_id' => ''];
        }
        //   dd($order);
        foreach ($order['shipments'] as $key => $value) {
            $sub_order_sn = $order_sn . "-" . str_pad((DB::table('ord_sub_orders')->where('order_id', $order_id)
                    ->get()
                    ->count()) + 1, 2, '0', STR_PAD_LEFT);

            $order['shipments'][$key]->sub_order_sn = $sub_order_sn;

            $insertData = [
                'order_id' => $order_id,
                'sn' => $sub_order_sn,
                'ship_category' => $value->category,
                'ship_category_name' => $value->category_name,
                'dlv_fee' => $value->dlv_fee,
                'total_price' => $value->origin_price,
                'origin_price' => $value->origin_price,
                'discounted_price' => $value->discounted_price,
                'discount_value' => $value->discount_value,
                'status' => '',
            ];

            switch ($value->category) {
                case 'deliver':
                    // dd( $value['use_role']);
                    $insertData['ship_event_id'] = $value->group_id;
                    $insertData['ship_event'] = $value->group_name;
                    $insertData['ship_temp'] = $value->temps;
                    $insertData['ship_temp_id'] = $value->temp_id;
                    $insertData['ship_rule_id'] = $value->use_rule->id;
                    break;
                case 'pickup':
                    $insertData['ship_event_id'] = $value->id;
                    $insertData['ship_event'] = $value->depot_name;
                    break;

            }

            $subOrderId = DB::table('ord_sub_orders')->insertGetId($insertData);
            $order['shipments'][$key]->sub_order_id = $subOrderId;
            Discount::createOrderDiscount('sub', $order_id, $customer, $value->discounts, $subOrderId);
            //TODO 目前做DEMO 在新增訂單時，就新增出貨單，若未來串好付款，則在付款完畢後才新增出貨單
            $reDelivery = Delivery::createData(
                $operator_user
                , Event::order()->value
                , $subOrderId
                , $insertData['sn']
                , $insertData['ship_temp_id'] ?? null
                , $insertData['ship_temp'] ?? null
                , $insertData['ship_category'] ?? null
                , $insertData['ship_category_name'] ?? null
                , $insertData['ship_event_id'] ?? null
            );
            if ($reDelivery['success'] == 0) {
                DB::rollBack();
                return $reDelivery;
            }

            foreach ($value->products as $product) {

                $reStock = ProductStock::stockChange($product->id, $product->qty * -1, 'order', $order_id, $product->sku . "新增訂單");
                if ($reStock['success'] == 0) {
                    DB::rollBack();
                    return $reStock;
                }
                $pid = DB::table('ord_items')->insertGetId([
                    'order_id' => $order_id,
                    'sub_order_id' => $subOrderId,
                    'product_style_id' => $product->product_style_id,
                    'sku' => $product->sku,
                    'product_title' => $product->product_title . '-' . $product->spec,
                    'price' => $product->price,
                    'dealer_price' => $product->dealer_price,
                    'bonus' => $product->bonus,
                    'qty' => $product->qty,
                    'discounted_price' => $product->discounted_price,
                    'discount_value' => $product->discount_value,
                    'origin_price' => $product->origin_price,
                    'img_url' => $product->img_url,
                ]);
                ProductStyle::willBeShipped($product->product_style_id, $product->qty);

                Discount::createOrderDiscount('item', $order_id, $customer, $product->discounts, $subOrderId, $pid);

            }

        }

        //付款資訊
        $updateOrdUPM = self::updateOrderUsrPayMethod($order_id, $payinfo);
        if ($updateOrdUPM['success'] == 0) {
            DB::rollBack();
            return $updateOrdUPM;
        }
        // 分潤
        self::orderProfit($order, $mcode);

        OrderFlow::changeOrderStatus($order_id, OrderStatus::Add());

        if ($order['get_dividend']) {
            CustomerDividend::fromOrder($customer->id, $order_sn, $order['get_dividend']);
        }
        // CustomerDividend::activeDividend(DividendCategory::Order(), $order_sn);
        //  CustomerCoupon::activeCoupon($order_id);

        Order::sendMail_OrderEstablished($order_id);
        DB::commit();
        return ['success' => '1', 'order_id' => $order_id];

    }

    //付款資訊
    //參考OrderInvoice::create_invoice的付款資訊來改
    public static function updateOrderUsrPayMethod($order_id, $payinfo)
    {
        // $item_tax_type_arr = [];
        // $n_order = Order::orderDetail($order_id)->first();
        // $n_sub_order = Order::subOrderDetail($order_id)->get();
        // foreach ($n_sub_order as $key => $value) {
        //     $n_sub_order[$key]->items = json_decode($value->items);
        //     $n_sub_order[$key]->consume_items = json_decode($value->consume_items);
        // }
        // $n_order_discount = DB::table('ord_discounts')->where([
        //     'order_type' => 'main',
        //     'order_id' => $order_id,
        // ])->where('discount_value', '>', 0)->get()->toArray();

        // foreach ($n_sub_order as $s_value) {
        //     foreach ($s_value->items as $i_value) {
        //         $item_tax_type_arr[] = $i_value->product_taxation == 1 ? 1 : 3;
        //     }
        // }
        // if ($n_order->dlv_fee > 0) {
        //     $item_tax_type_arr[] = $n_order->dlv_taxation == 1 ? 1 : 3;
        // }
        // foreach ($n_order_discount as $d_value) {
        //     $item_tax_type_arr[] = $d_value->discount_taxation == 1 ? 1 : 3;
        // }

        // if (count(array_unique($item_tax_type_arr)) == 1) {
        //     if (array_unique($item_tax_type_arr)[0] == 1) {
        //         $tax_type = 1;
        //     } else if (array_unique($item_tax_type_arr)[0] == 3) {
        //         $tax_type = 3;
        //     } else {
        //         $tax_type = 9;
        //     }
        // } else {
        //     $tax_type = 9;
        // }
        $category = $payinfo['category'] ?? 'B2C';
        $invoice_method = $payinfo['invoice_method'] ?? null;
        $inv_title = $payinfo['inv_title'] ?? null;
        $buyer_ubn = $payinfo['buyer_ubn'] ?? null;
        $buyer_email = isset($payinfo['carrier_email']) ? trim($payinfo['carrier_email']) : null;
        $carrier_type = $payinfo['carrier_type'] ?? null;
        $carrier_num = isset($payinfo['carrier_num']) ? trim($payinfo['carrier_num']) : null;
        $love_code = $payinfo['love_code'] ?? null;
        if (isset($carrier_type) && true == empty(CarrierType::getDescription($carrier_type))) {
            return ['success' => '0', 'error_msg' => '無此載具'];
        }
        if (false == InvoiceMethod::hasKey($invoice_method)) {
            return ['success' => '0', 'error_msg' => '無此發票方式'];
        }
        $invoice_category = InvoiceMethod::getDescription($invoice_method);

        $print_flag = $carrier_type != null || $love_code ? 'N' : 'Y';

        if ($category === 'B2B') {
            // if ($tax_type == 9) {
            //     DB::rollBack();
            //     return ['success' => '0', 'error_msg' => '三聯式發票稅別不可為混合課稅'];
            // }

            $carrier_type = null;
            $carrier_num = null;
            $love_code = null;
        } else if ($category === 'B2C') {
            $buyer_ubn = null;
            if ($print_flag == 'N') {
                if ($carrier_type != null && $carrier_type == 0) {
                    if (preg_match('/^\/[A-Z0-9+-.]{7}$/', $carrier_num) == 0 || strlen($carrier_num) != 8) {
                        DB::rollBack();
                        return ['success' => '0', 'error_msg' => '手機條碼載具格式錯誤'];
                    }

                } else if ($carrier_type == 1) {
                    if (preg_match('/^[A-Z]{2}[0-9]{14}$/', $carrier_num) == 0 || strlen($carrier_num) != 16) {
                        DB::rollBack();
                        return ['success' => '0', 'error_msg' => '自然人憑證條碼載具格式錯誤'];
                    }

                } else if ($carrier_type == 2) {
                    $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";
                    if (preg_match($pattern, $buyer_email) == 0) {
                        DB::rollBack();
                        return ['success' => '0', 'error_msg' => '會員電子發票載具格式錯誤'];
                    }
                    $carrier_num = $buyer_email;
                }

            } else {
                $carrier_type = null;
                $carrier_num = null;
                $love_code = null;
            }

            if (false == empty($carrier_type)) {
                $love_code = null;
                $print_flag = 'Y';

            } else {
                if ($love_code != '' && preg_match('/^[0-9]{3,7}$/', $love_code) !== 1) {
                    DB::rollBack();
                    return ['success' => '0', 'error_msg' => '捐贈碼格式錯誤'];
                }
            }
        }
        self::where('id', $order_id)->update([
            'category' => $category
            , 'invoice_category' => $invoice_category
            , 'inv_title' => $inv_title
            , 'buyer_ubn' => $buyer_ubn
            , 'carrier_type' => $carrier_type
            , 'carrier_num' => $carrier_num
            , 'love_code' => $love_code,
        ]);
        return ['success' => '1', 'error_msg' => ''];
    }

    public static function generate_unique_id()
    {
        $unique_id = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, 9); // return 9 characters

        if (self::where('unique_id', $unique_id)->first()) {
            return self::generate_unique_id();
        } else {
            return $unique_id;
        }
    }

    // public static function change_order_payment_status($order_id, PaymentStatus $p_status = null, ReceivedMethod $r_method = null)
    public static function change_order_payment_status($order_id, PaymentStatus $p_status = null, $r_method = null)
    {
        $target = self::where('id', $order_id);

        if ($p_status) {
            $target->update([
                'payment_status' => $p_status->value,
                'payment_status_title' => $p_status->description,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($r_method) {
            $target->update([
                'payment_method' => $r_method->value,
                'payment_method_title' => $r_method->description,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public static function assign_dividend_active_date($order_id)
    {
        $order = self::where('id', $order_id)
            ->whereNull('dividend_active_at')
            ->whereNotNull('active_delay_day')->get()->first();

        if (!$order) {
            return;
        }

        $date = date('Y-m-d H:i:s', strtotime(now() . " + $order->active_delay_day days"));

        self::where('id', $order_id)->update([
            'dividend_active_at' => $date,
        ]);

    }

    public static function update_dlv_taxation($parm)
    {
        self::where('id', $parm['order_id'])->update([
            'dlv_taxation' => $parm['taxation'],
        ]);
    }

    public static function update_invoice_info($parm)
    {
        self::where('id', $parm['order_id'])->update([
            'invoice_number' => $parm['invoice_number'],
        ]);
    }

    private static function orderProfit($order, $mcode)
    {
        // dd($order);
        if (!$mcode) {
            return;
        }
        $reCustomer = Customer::where('sn', $mcode)->get()->first();
        if (!$reCustomer) {
            return;
        }
        //確認資格
        $customerProfit = CustomerProfit::getProfitData($reCustomer->id, ProfitStatus::Success());

        if (!$customerProfit) {
            return;
        }
        //上一代分潤資格
        $parentCustomerProfit = null;

        if ($customerProfit->parent_customer_id) {
            $parentCustomerProfit = CustomerProfit::getProfitData($customerProfit->parent_customer_id, ProfitStatus::Success());
        }

        $profit_rate = 100;

        if ($parentCustomerProfit) {
            $profit_rate = $customerProfit->profit_rate;
        }
        // dd($order);
        foreach ($order['shipments'] as $shipment) {
            foreach ($shipment->products as $product) {
                $bonus = $product->bonus * $product->qty;
                // dd($bonus);
                if ($profit_rate != 100) {
                    $cBonus = floor($bonus / 100 * $profit_rate);
                } else {
                    $cBonus = $bonus;
                }
                $pBonus = $bonus - $cBonus;
                // dd($bonus, $cBonus,$pBonus);

                $updateData = ['order_id' => $order['order_id'],
                    'order_sn' => $order['order_sn'],
                    'sub_order_sn' => $shipment->sub_order_sn,
                    'sub_order_id' => $shipment->sub_order_id,
                    'style_id' => $product->product_style_id,
                    'total_bonus' => $bonus];
                //    dd($updateData);

                $pid = OrderProfit::create(array_merge($updateData, [
                    'bonus' => $cBonus,
                    'customer_id' => $reCustomer->id,
                ]))->id;

                if ($parentCustomerProfit && $pBonus) {
                    OrderProfit::create(array_merge($updateData, [
                        'bonus' => $pBonus,
                        'customer_id' => $customerProfit->parent_customer_id,
                        'parent_id' => $pid,
                    ]));
                }

            }
        }

//        exit;

    }
    // 是否可取消訂單
    public static function checkCanCancel($order_id, $type)
    {
        $order = self::where('id', $order_id)->select('status_code', 'payment_status')->get()->first();

        if ($type == 'frontend') {
            $order_status = [OrderStatus::Add()];
            $payment_status = [PaymentStatus::Unpaid()];

            //   dd(in_array($order->status_code, $order_status) ,in_array($order->payment_status, $payment_status));
            if (in_array($order->status_code, $order_status) && in_array($order->payment_status, $payment_status)) {
                return true;
            } else {
                return false;
            }
        }

        if ($type == 'backend') {

            $order_status = [OrderStatus::Closed(), OrderStatus::Canceled()];

            if (!in_array($order->status_code, $order_status)) {
                return true;
            } else {
                return false;
            }
        }

    }
    // 取消訂單
    public static function cancelOrder($order_id, $type)
    {
        if (!self::checkCanCancel($order_id, $type)) {
            return;
        }

        $order = self::where('id', $order_id)->get()->first();
        if (!$order) {
            return;
        }
        $customer = Customer::where('email', $order->email)->get()->first();
        if (!$customer) {
            return;
        }

        DB::beginTransaction();
        // 狀態變更
        OrderFlow::changeOrderStatus($order_id, OrderStatus::Canceled());

        //已退貨入庫已計算可售數量 則不寫入
        $subOrders = SubOrders::where('order_id', '=', $order->id)->get();
        if (null != $subOrders && 0 < count($subOrders)) {
            foreach ($subOrders as $sub_ord) {
                $is_calc_in_stock = true;
                $delivery = Delivery::where('event_id', '=', $sub_ord->id)->where('event', '=', Event::order()->value)->first();
                //判斷已退貨入庫
                if (null != $delivery
                    && null != $delivery->back_inbound_date
                    && LogisticStatus::C3000()->key == $delivery->logistic_status_code)
                {
                    $is_calc_in_stock = false;
                }
                if (true == $is_calc_in_stock) {
                    // 返還訂購商品數量
                    $items = OrderItem::where('order_id', $order_id)->where('sub_order_id', $sub_ord->id)->get();
                    foreach ($items as $item) {
                        ProductStock::stockChange($item->product_style_id,
                            $item->qty, 'order', $order_id, $item->sku . "取消訂單");

                        ProductStyle::willBeShipped($item->product_style_id, $item->qty * -1);
                    }
                }
            }
        }

        // 返還使用的優惠券
        CustomerCoupon::where('order_id', $order_id)->update(['used' => 0, 'order_id' => null]);

        // 刪除贈送的優惠券
        CustomerCoupon::where('from_order_id', $order_id)->delete();

        // 刪除贈送紅利
        CustomerDividend::where('category', DividendCategory::Order())
            ->where('category', DividendCategory::Order())
            ->where('category_sn', $order->sn)
            ->where('flag', DividendFlag::NonActive())->delete();

        // 紅利返還
        $dividend = DB::table('ord_dividend as d')
            ->leftJoin('usr_cusotmer_dividend as cd', 'd.customer_dividend_id', '=', 'cd.id')
            ->select(['cd.*', 'd.dividend as new_dividend'])
            ->where('d.order_sn', $order->sn)->get();

        foreach ($dividend as $value) {
            CustomerDividend::create([
                'category' => $value->category,
                'category_sn' => $value->category_sn,
                'customer_id' => $customer->id,
                'type' => 'get',
                'flag' => DividendFlag::Back(),
                'flag_title' => DividendFlag::Back()->description,
                'dividend' => $value->new_dividend,
                'weight' => $value->weight,
                'deadline' => $value->deadline,
                'active_sdate' => $value->active_sdate,
                'active_edate' => $value->active_edate,
                'note' => '由' . $order->sn . "訂單返還",
            ]);

        }
        DB::table('ord_dividend')->where('order_sn', $order->sn)->delete();
        // 刪除分潤
        OrderProfit::where('order_id', $order_id)->delete();

        DB::commit();

        return;

    }
    // 分割訂單
    public static function splitOrder($order_id, $items, $operator_user)
    {
        if (!Order::checkCanSplit($order_id)) {
            return;
        }

        $order = self::where('id', $order_id)->get()->first();
        if (!$order) {
            return;
        }

        if ($order->status_code != OrderStatus::Add()) {
            return;
        }

        DB::beginTransaction();

        $nSubOrders = [];
        //  $originDiscount = $order->dlv_fee;
        $total_price = 0;
        // dd($items);
        foreach ($items as $key => $qty) {
            $_item = OrderItem::where('order_id', $order_id)->where('product_style_id', $key)->get()->first();
            //  dd($_item);

            $n_price = $_item->price * $qty;

            $total_price += $n_price;

            OrderItem::where('order_id', $order_id)->where('product_style_id', $key)
                ->update([
                    'qty' => $_item->qty - $qty,
                    'origin_price' => $_item->origin_price - $n_price,
                    'discounted_price' => $_item->discounted_price - $n_price,
                ]);

            $_item->qty = $qty;
            $_item->origin_price = $n_price;
            $_item->discounted_price = $n_price;

            $_suborder = SubOrders::where('id', $_item->sub_order_id)->get()->first();

            SubOrders::where('id', $_item->sub_order_id)->update([
                'total_price' => $_suborder->total_price - $n_price,
                'origin_price' => $_suborder->origin_price - $n_price,
                'discounted_price' => $_suborder->discounted_price - $n_price,
            ]);

            if (!isset($nSubOrders[$_suborder->sn])) {
                $_suborder->items = collect([]);
                $_suborder->total_price = 0;
                $_suborder->origin_price = 0;
                $_suborder->discounted_price = 0;
                $_suborder->discount_value = 0;

                $nSubOrders[$_suborder->sn] = $_suborder;

                // $originDiscount += $_suborder->dlv_fee;

            }

            $_suborder->total_price += $n_price;
            $_suborder->origin_price += $n_price;
            $_suborder->discounted_price += $n_price;

            $nSubOrders[$_suborder->sn]->items[] = $_item;

        }

        $order_sn = "O" . date("Ymd") . str_pad((self::whereDate('created_at', '=', date('Y-m-d'))
                ->get()
                ->count()) + 1, 4, '0', STR_PAD_LEFT);

        $order->where('id', $order_id)->update([
            'origin_price' => $order->origin_price - $total_price,
            'total_price' => $order->total_price - $total_price,
            'discounted_price' => $order->discounted_price - $total_price,
            'note' => $order->note . " " . "拆分" . $order_sn,
        ]);
        // create order
        $nid = self::create([
            'sn' => $order_sn,
            'email' => $order->email,
            'sale_channel_id' => $order->sale_channel_id,
            'status_code' => $order->status_code,
            'status' => $order->status,
            'mcode' => $order->mcode,
            'dlv_fee' => 0,
            'dlv_taxation' => $order->dlv_taxation,
            'origin_price' => $total_price,
            'total_price' => $total_price,
            'discount_value' => 0,
            'discounted_price' => $total_price,
            'note' => $order->sn . " 拆單",
            'auto_dividend' => $order->auto_dividend,
            'allotted_dividend' => $order->allotted_dividend,
            'dividend_lifecycle' => $order->dividend_lifecycle,
            'active_delay_day' => $order->active_delay_day,
            'unique_id' => self::generate_unique_id(),
            'category' => $order->category,
            'carrier_type' => $order->carrier_type,
            'carrier_num' => $order->carrier_num,
            'payment_status' => $order->payment_status,
            'payment_status_title' => $order->payment_status_title,
        ])->id;
        // copy address
        $address = DB::table('ord_address')->where('order_id', $order_id)->get()->toArray();

        DB::table('ord_address')->insert(array_map(function ($n) use ($nid) {
            return [
                "order_id" => $nid,
                "type" => $n->type,
                "city_id" => $n->city_id,
                "city_title" => $n->city_title,
                "region_id" => $n->region_id,
                "region_title" => $n->region_title,
                "addr" => $n->addr,
                "address" => $n->address,
                "zipcode" => $n->zipcode,
                "name" => $n->name,
                "phone" => $n->phone,
            ];
        }, $address));

        $idx = 1;
        foreach ($nSubOrders as $sorder) {
            $ssn = $order_sn . "-" . str_pad($idx, 2, '0', STR_PAD_LEFT);
            $soid = SubOrders::create([
                'sn' => $ssn,
                'order_id' => $nid,
                "ship_sn" => null,
                "ship_category" => $sorder->ship_category,
                "ship_category_name" => $sorder->ship_category_name,
                "ship_event" => $sorder->ship_event,
                "ship_event_id" => $sorder->ship_event_id,
                "ship_temp" => $sorder->ship_temp,
                "ship_temp_id" => $sorder->ship_temp_id,
                "ship_rule_id" => $sorder->ship_rule_id,
                "package_sn" => null,
                "actual_ship_group_id" => null,
                "dlv_fee" => 0,
                "status" => $sorder->status,
                "total_price" => $sorder->total_price,
                "origin_price" => $sorder->origin_price,
                "discount_value" => $sorder->discount_value,
                "discounted_price" => $sorder->discounted_price,
                "statu" => null,
                "statu_code" => null,
                "close_date" => null,
                "dlv_audit_date" => null,
            ])->id;

            Delivery::createData(
                $operator_user
                , Event::order()->value
                , $soid
                , $ssn
                , $sorder->ship_temp_id ?? null
                , $sorder->ship_temp ?? null
                , $sorder->ship_category ?? null
                , $sorder->ship_category_name ?? null
                , $sorder->ship_event_id ?? null
            );

            foreach ($sorder->items as $item) {

                OrderItem::create([
                    "order_id" => $nid,
                    "sub_order_id" => $soid,
                    "product_style_id" => $item->product_style_id,
                    "sku" => $item->sku,
                    "product_title" => $item->product_title,
                    "price" => $item->price,
                    "unit_cost" => null,
                    "qty" => $item->qty,
                    "bonus" => 0,
                    "type" => null,
                    "origin_price" => $item->origin_price,
                    "discount_value" => 0,
                    "discounted_price" => $item->discounted_price,
                    "img_url" => null,
                ]);
            }
            $idx++;
        }

        DB::commit();

    }

    // 是否可取消訂單
    public static function checkCanSplit($order_id)
    {

        $sub = SubOrders::where('order_id', $order_id)->whereNotNull('dlv_audit_date')->get();

        if (count($sub) == 0) {
            return true;
        }

        return false;
    }

    //取得發信的寄件人、收件人訊息
    public static function getSendMailAddressInfo($order_id, &$orderer, &$receiver): void
    {
        $address = DB::table('ord_address as addr')
            ->where('addr.order_id', '=', $order_id)
            ->get();

        if (isset($address) && 0 < count($address)) {
            foreach ($address as $addr) {
                if (UserAddrType::orderer()->value == $addr->type) {
                    $orderer = $addr;
                } elseif (UserAddrType::receiver()->value == $addr->type) {
                    $receiver = $addr;
                }
            }
        }
    }

    //訂單成立 發信給消費者
    public static function sendMail_OrderEstablished($order_id)
    {
        $mail_set = DB::table('shared_preference as sp')
            ->where('sp.category', '=', \App\Enums\Globals\SharedPreference\Category::mail()->value)
            ->where('sp.event', '=', \App\Enums\Globals\SharedPreference\Event::mail_order()->value)
            ->where('sp.feature', '=', \App\Enums\Globals\SharedPreference\Feature::mail_order_established()->value)
            ->first();
        if (\App\Enums\Globals\StatusOffOn::On()->value == $mail_set->status) {
            $order = Order::where('id', '=', $order_id)->first();
            $orderer = null;
            $receiver = null;

            self::getSendMailAddressInfo($order_id, $orderer, $receiver);

            //連結網址
            $link_url_type = 'orderDetail';
            if (ReceivedMethod::Remittance()->value == $order->payment_method) {
                $link_url_type = 'payRemit';
            }
            $link_url = env('FRONTEND_URL') . '' . $link_url_type . '/' . $order_id . '?em=' . base64_encode(trim($order->email));

            $email = $order->email;
            $data = [
                'order_name' => $orderer->name ?? ''
                , 'sn' => $order->sn ?? ''
                , 'link_url' => $link_url,
            ];
            Mail::to($email)->queue(new OrderEstablished($data));
        }
    }

    //訂單已付款 發信給消費者
    public static function sendMail_OrderPaid($order_id)
    {
        $mail_set = DB::table('shared_preference as sp')
            ->where('sp.category', '=', \App\Enums\Globals\SharedPreference\Category::mail()->value)
            ->where('sp.event', '=', \App\Enums\Globals\SharedPreference\Event::mail_order()->value)
            ->where('sp.feature', '=', \App\Enums\Globals\SharedPreference\Feature::mail_order_paid()->value)
            ->first();
        if (\App\Enums\Globals\StatusOffOn::On()->value == $mail_set->status) {
            $order = Order::where('id', '=', $order_id)->first();
            $orderer = null;
            $receiver = null;

            self::getSendMailAddressInfo($order_id, $orderer, $receiver);

            $email = $order->email;
            $data = [
                'order_name' => $orderer->name ?? ''
                , 'sn' => $order->sn ?? '',
            ];
            Mail::to($email)->queue(new OrderPaid($data));
        }
    }

    //訂單已出貨 發信給消費者
    public static function sendMail_OrderShipped($sub_order_id)
    {
        $mail_set = DB::table('shared_preference as sp')
            ->where('sp.category', '=', \App\Enums\Globals\SharedPreference\Category::mail()->value)
            ->where('sp.event', '=', \App\Enums\Globals\SharedPreference\Event::mail_order()->value)
            ->where('sp.feature', '=', \App\Enums\Globals\SharedPreference\Feature::mail_order_shipped()->value)
            ->first();
        if (\App\Enums\Globals\StatusOffOn::On()->value == $mail_set->status) {
            $sub_order = SubOrders::where('id', '=', $sub_order_id)->first();
            $order = Order::where('id', '=', $sub_order->order_id)->first();
            $order_id = $order->id;
            $orderer = null;
            $receiver = null;

            self::getSendMailAddressInfo($order_id, $orderer, $receiver);

            $order_items = DB::table(app(OrderItem::class)->getTable() . ' as item')
                ->where('order_id', '=', $order_id)
                ->where('sub_order_id', '=', $sub_order_id)
                ->get();

            $email = $order->email;
            $data = [
                'order_name' => $orderer->name ?? ''
                , 'sn' => $order->sn ?? ''
                , 'receive_name' => $receiver->name ?? ''
                , 'receive_address' => $receiver->address ?? ''
                , 'receive_phone' => $receiver->phone ?? ''
                , 'order_items' => $order_items ?? null,
            ];
            Mail::to($email)->queue(new OrderShipped($data));
        }
    }

    public static function checkReceived($order_id)
    {
        $re = DB::table('ord_orders as order')
            ->join('ord_received_orders as receive', 'order.id', '=', 'receive.source_id')
            ->select('order.id')
            ->where('source_type', 'ord_orders')
            ->where('order.id', $order_id)
            ->whereNotNull('receive.receipt_date')->get()->first();

        return $re ? true : false;
    }

    public static function orderDividendList($order_id)
    {
        $titles = "Case";
        foreach (DividendCategory::getValues() as $value) {
            $t = DividendCategory::fromValue($value)->description;
            $titles .= " WHEN cd.category = \"$value\" THEN \"$t\"";
        }
        $titles .= " END as category_title";

        return DB::table('ord_dividend as od')
            ->leftJoin('usr_cusotmer_dividend as cd', 'od.customer_dividend_id', '=', 'cd.id')
            ->leftJoin('ord_orders as order', 'od.order_sn', '=', 'order.sn')
            ->select([
                'od.dividend as dividend',
                'cd.category_sn',
                'cd.category',
            ])
            ->selectRaw($titles)
            ->where('order.id', '=', $order_id);

    }

    /**
     * 是否可查看所有訂單？
     * 否：只能查到「訂單管理」自己、分潤人是本人的訂單
     * @return bool
     */
    public static function canViewWholeOrder()
    {
        $user = Auth::user();

        if (DB::table('per_permissions')
            ->where('name', 'cms.order.whole')
            ->exists()
        ) {
            $wholeOrderPermissionId = DB::table('per_permissions')
                ->where('name', 'cms.order.whole')
                ->select('id')
                ->get()
                ->first()
                ->id;
        } else {
            return true;
        }

        $roleNames = User::find($user->id)->getRoleNames()->toArray();

        if (in_array('Super Admin', $roleNames)) {
            return true;
        }

        //直接權限(direct permission)
        if (User::find($user->id)->hasPermissionTo('cms.order.whole')
        ) {
            return true;
        } else {
            //檢查：角色的權限(role permission)是否只可以瀏覽自己,這裡別用Spatie套件的function，會有問題
            foreach ($roleNames as $roleName) {
                $roleId = DB::table('per_roles')
                    ->where('name', $roleName)
                    ->get()
                    ->first()
                    ->id;
                if (
                    DB::table('per_role_has_permissions')
                    ->where([
                        'role_id' => $roleId,
                        'permission_id' => $wholeOrderPermissionId,
                    ])->exists()
                ) {
                    return true;
                }
            }
            return false;
        }
    }
}
