<?php

namespace App\Models;

use App\Enums\Order\UserAddrType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;
    protected $table = 'ord_orders';
    protected $guarded = [];

    public static function orderList($keyword = null, $sale_channel_id = null)
    {
        $order = DB::table('ord_orders as order')
            ->select('order.id as id', 'customer.name', 'sale.title as sale_title', 'so.ship_category_name',
                'so.ship_event', 'so.ship_sn')
            ->selectRaw('DATE_FORMAT(order.created_at,"%Y-%m-%d") as order_date')
            ->selectRaw('so.sn as order_sn')
            ->leftJoin('ord_sub_orders as so', 'order.id', '=', 'so.order_id')
            ->leftJoin('usr_customers as customer', 'order.email', '=', 'customer.email')
            ->leftJoin('prd_sale_channels as sale', 'sale.id', '=', 'order.sale_channel_id');

        if ($keyword) {
            $order->where('so.sn', 'like', "%$keyword%");
        }

        return $order;
        //   dd($order->get()->toArray());
    }

    public static function orderDetail($order_id)
    {
        $orderQuery = DB::table('ord_orders as order')
            ->leftJoin('usr_customers as customer', 'order.email', '=', 'customer.email')
            ->leftJoin('prd_sale_channels as sale', 'sale.id', '=', 'order.sale_channel_id')
            ->select('order.sn', 'order.note', 'order.status', 'order.total_price', 'order.created_at', 'customer.name', 'customer.email', 'sale.title as sale_title')
            ->where('order.id', $order_id);
        self::orderAddress($orderQuery);

        return $orderQuery;
    }

    public static function subOrderDetail($order_id)
    {
        $concatString = concatStr([
            'product_title' => 'item.product_title',
            'sku' => 'item.sku',
            'price' => 'item.price',
            'qty' => 'item.qty',
            'total_price' => 'item.total_price']);

        $itemQuery = DB::table('ord_items as item')
            ->groupBy('item.sub_order_id')
            ->select('item.sub_order_id')
            ->selectRaw($concatString . ' as items')
            ->where('item.order_id', $order_id);

        $orderQuery = DB::table('ord_sub_orders as sub_order')
            ->leftJoin(DB::raw("({$itemQuery->toSql()}) as i"), function ($join) {
                $join->on('sub_order.id', '=', 'i.sub_order_id');
            })
            ->mergeBindings($itemQuery)
            ->select('sub_order.*', 'i.items')
            ->where('order_id', $order_id);

        return $orderQuery;

    }

    private static function orderAddress(&$query)
    {
        foreach (UserAddrType::asArray() as $value) {
            $query->leftJoin('ord_address as ' . $value, function ($q) use ($value) {
                $q->on('order.id', '=', $value . '.order_id')
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

    public static function createOrder($email, $sale_channel_id, $address, $items, $note = null)
    {

        return DB::transaction(function () use ($email, $sale_channel_id, $address, $items, $note) {
            $order = OrderCart::cartFormater($items);

            if ($order['success'] != 1) {
                DB::rollBack();
                return $order;
            }

            $order_sn = date("Ymd") . str_pad((self::whereDate('created_at', '=', date('Y-m-d'))
                    ->get()
                    ->count()) + 1, 2, '0', STR_PAD_LEFT);

            // dd($order['shipments']);
            $order_id = self::create([
                "sn" => $order_sn,
                "sale_channel_id" => $sale_channel_id,
                "email" => $email,
                "total_price" => $order['total_price'],
                'note' => $note,
            ])->id;

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
            } catch (\Exception $e) {
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
                    'total_price' => $value->totalPrice,
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

                foreach ($value->products as $product) {

                    $reStock = ProductStock::stockChange($product->id, $product->qty * -1, 'order', $order_id, $product->sku . "新增訂單");
                    if ($reStock['success'] == 0) {
                        DB::rollBack();
                        return $reStock;
                    }
                    DB::table('ord_items')->insert([
                        'order_id' => $order_id,
                        'sub_order_id' => $subOrderId,
                        'product_style_id' => $product->product_style_id,
                        'sku' => $product->sku,
                        'product_title' => $product->product_title . $product->spec,
                        'price' => $product->price,
                        'qty' => $product->qty,
                        'total_price' => $product->total_price,
                    ]);

                }

            }

            OrderFlow::changeOrderStatus($order_id, 'O01');

            return ['success' => '1', 'order_id' => $order_id];
        });

    }

}
