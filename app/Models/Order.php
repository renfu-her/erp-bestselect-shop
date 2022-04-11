<?php

namespace App\Models;

use App\Enums\Delivery\Event;
use App\Enums\Order\UserAddrType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;
    protected $table = 'ord_orders';
    protected $guarded = [];

    public static function orderList($keyword = null,
        $order_status = null,
        $sale_channel_id = null,
        $order_date = null) {
        $order = DB::table('ord_orders as order')
            ->select(['order.id as id',
                'order.status as order_status',
                'customer.name',
                'sale.title as sale_title',
                'so.ship_category_name',
                'so.ship_event', 'so.ship_sn',
                'dlv_delivery.logistic_status as logistic_status',
                'dlv_logistic.package_sn as package_sn',
                'shi_group.name as ship_group_name'])
            ->selectRaw('DATE_FORMAT(order.created_at,"%Y-%m-%d") as order_date')
            ->selectRaw('so.sn as order_sn')
            ->leftJoin('ord_sub_orders as so', 'order.id', '=', 'so.order_id')
            ->leftJoin('usr_customers as customer', 'order.email', '=', 'customer.email')
            ->leftJoin('prd_sale_channels as sale', 'sale.id', '=', 'order.sale_channel_id')

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
        ;

        if ($keyword) {
            $order->where('so.sn', 'like', "%$keyword%");
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

        if ($order_date) {
            if (gettype($order_date) == 'array' && count($order_date) == 2) {
                $sDate = date('Y-m-d 00:00:00', strtotime($order_date[0]));
                $eDate = date('Y-m-d 23:59:59', strtotime($order_date[1]));
                $order->whereBetween('order.created_at', [$sDate, $eDate]);
            }
        }

        return $order;
        //   dd($order->get()->toArray());
    }

    public static function orderDetail($order_id)
    {
        $orderQuery = DB::table('ord_orders as order')
            ->leftJoin('usr_customers as customer', 'order.email', '=', 'customer.email')
            ->leftJoin('prd_sale_channels as sale', 'sale.id', '=', 'order.sale_channel_id')
            ->select([
                'order.id',
                'order.sn',
                'order.discount_value',
                'order.discounted_price',
                'order.dlv_fee',
                'order.origin_price',
                'order.note',
                'order.status',
                'order.total_price',
                'order.created_at',
                'customer.name',
                'customer.email',
                'sale.title as sale_title'])
            ->where('order.id', $order_id);
        self::orderAddress($orderQuery);

        return $orderQuery;
    }

    public static function subOrderDetail($order_id, $sub_order_id = null)
    {
        $concatString = concatStr([
            'product_title' => 'item.product_title',
            'sku' => 'item.sku',
            'price' => 'item.price',
            'qty' => 'item.qty',
            'total_price' => 'item.origin_price']);

        $itemQuery = DB::table('ord_items as item')
            ->groupBy('item.sub_order_id')
            ->select('item.sub_order_id')
            ->selectRaw($concatString . ' as items')
            ->where('item.order_id', $order_id);
        // dd($itemQuery->get()->toArray());
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
            ->select('sub_order.*', 'i.items'
                , 'dlv_delivery.sn as delivery_sn'
                , 'dlv_delivery.logistic_status as logistic_status'
                , 'dlv_logistic.sn as logistic_sn'
                , 'dlv_logistic.package_sn as package_sn'
                , 'dlv_logistic.ship_group_id as ship_group_id'
                , 'shi_group.name as ship_group_name'
                , 'shi_group.note as ship_group_note')
            ->where('order_id', $order_id);

        if ($sub_order_id) {
            $orderQuery->where('sub_order.id', $sub_order_id);
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
                case UserAddrType::reciver()->value:
                    $prefix = 'rec_';
                    break;
                case UserAddrType::orderer()->value:
                    $prefix = 'ord_';
                    break;
                case UserAddrType::sender()->value:
                    $prefix = 'sed_';
                    break;
            }

            $query->addSelect($value . '.name as ' . $prefix . 'name');
            $query->addSelect($value . '.address as ' . $prefix . 'address');
            $query->addSelect($value . '.phone as ' . $prefix . 'phone');
            $query->addSelect($value . '.zipcode as ' . $prefix . 'zipcode');

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
    public static function createOrder($email, $sale_channel_id, $address, $items, $note = null, $coupon_obj = null)
    {

        return DB::transaction(function () use ($email, $sale_channel_id, $address, $items, $note, $coupon_obj) {
            $order = OrderCart::cartFormater($items, $coupon_obj);
            // dd($order);
            if ($order['success'] != 1) {
                DB::rollBack();
                return $order;
            }

            $order_sn = date("Ymd") . str_pad((self::whereDate('created_at', '=', date('Y-m-d'))
                    ->get()
                    ->count()) + 1, 2, '0', STR_PAD_LEFT);

            $order_id = self::create([
                "sn" => $order_sn,
                "sale_channel_id" => $sale_channel_id,
                "email" => $email,
                "total_price" => $order['total_price'],
                "origin_price" => $order['origin_price'],
                "dlv_fee" => $order['dlv_fee'],
                "discount_value" => $order['discount_value'],
                "discounted_price" => $order['discounted_price'],
                'note' => $note,
            ])->id;

            Discount::createOrderDiscount('main', $order_id, $order['discounts']);

            foreach ($address as $key => $user) {

                $addr = Addr::addrFormating($user['address']);
                if (!$addr->city_id) {
                    DB::rollBack();
                    return ['success' => '0', 'error_msg' => 'address format error', 'event' => 'address', 'event_id' => $user['type']];
                }
                $address[$key]['city_id'] = $addr->city_id;
                $address[$key]['city_title'] = $addr->city_title;
                $address[$key]['region_id'] = $addr->region_id;
                $address[$key]['region_title'] = $addr->region_title;
                $address[$key]['zipcode'] = $addr->zipcode;
                $address[$key]['addr'] = $addr->addr;
                $address[$key]['order_id'] = $order_id;
            }
            try {
                DB::table('ord_address')->insert($address);
            } catch (\Exception$e) {
                DB::rollBack();
                return ['success' => '0', 'error_msg' => 'address format error', 'event' => 'address', 'event_id' => ''];
            }
            //   dd($order);
            foreach ($order['shipments'] as $value) {
                $sub_order_sn = $order_sn . "-" . str_pad((DB::table('ord_sub_orders')->where('order_id', $order_id)
                        ->get()
                        ->count()) + 1, 2, '0', STR_PAD_LEFT);

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

                //TODO 目前做DEMO 在新增訂單時，就新增出貨單，若未來串好付款，則在付款完畢後才新增出貨單
                $reDelivery = Delivery::createData(
                    Event::order()->value
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
                        'qty' => $product->qty,
                        'discounted_price' => $product->discounted_price,
                        'discount_value' => $product->discount_value,
                        'origin_price' => $product->origin_price,
                    ]);

                    Discount::createOrderDiscount('item', $pid, $product->discounts);

                }

            }

            OrderFlow::changeOrderStatus($order_id, 'O01');

            return ['success' => '1', 'order_id' => $order_id];
        });

    }

}
