<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'ord_orders';
    protected $guarded = [];

    public static function orderList()
    {
        $order = DB::table('ord_orders as order')
            ->select('order.order_sn', 'customer.name', 'sale.title as sale_title')
            ->selectRaw('DATE_FORMAT(order.created_at,"%Y-%m-%d") as order_date')
            ->leftJoin('usr_customers as customer', 'order.email', '=', 'customer.email')
            ->leftJoin('prd_sale_channels as sale', 'sale.id', '=', 'order.sale_channel_id');
            
        return $order;
        //   dd($order->get()->toArray());
    }

    public static function createOrder($data)
    {
        $data = [
            [
                'product_id' => 1,
                'product_style_id' => 1,
                'customer_id' => 1,
                'qty' => 10,
                'shipment_type' => 'deliver',
                'shipment_event_id' => 1,
            ],
            [
                'product_id' => 1,
                'product_style_id' => 1,
                'customer_id' => 1,
                'qty' => 2,
                'shipment_type' => 'pickup',
                'shipment_event_id' => 2,
            ],
            [
                'product_id' => 1,
                'product_style_id' => 1,
                'customer_id' => 1,
                'qty' => 2,
                'shipment_type' => 'pickup',
                'shipment_event_id' => 3,
            ],
        ];

        $userAddress = [
            ['name' => 'hans', 'phone' => '0123313', 'address' => '桃園市八德區永福街', 'type' => 'reciver'],
            ['name' => 'hans', 'phone' => '0123313', 'address' => '桃園市八德區永福街', 'type' => 'orderer'],
            ['name' => 'hans', 'phone' => '0123313', 'address' => '桃園市八德區永福街', 'type' => 'sender'],
        ];

        return DB::transaction(function () use ($data, $userAddress) {
            $order = OrderCart::cartFormater($data);

            if ($order['success'] != 1) {
                DB::rollBack();
                return $order;
            }

            // dd($order);

            // createOrder
            $order_sn = date("Ymd") . str_pad((self::whereDate('created_at', '=', date('Y-m-d'))
                    ->withTrashed()
                    ->get()
                    ->count()) + 1, 3, '0', STR_PAD_LEFT);

            // dd($order['shipments']);
            $order_id = self::create([
                "order_sn" => $order_sn,
                "sale_channel_id" => 1,
                "payment_method" => 1,
                "email" => 'hayashi0126@gmail.com',
                "total_price" => $order['total_price'],
            ])->id;

            foreach ($userAddress as $key => $user) {
                $addr = Addr::addrFormating($user['address']);
                if (!$addr->city_id) {
                    DB::rollBack();
                    return ['success' => '0', 'message' => 'address format error'];
                }
                $userAddress[$key]['city_id'] = $addr->city_id;
                $userAddress[$key]['city_title'] = $addr->city_title;
                $userAddress[$key]['region_id'] = $addr->region_id;
                $userAddress[$key]['region_title'] = $addr->region_title;
                $userAddress[$key]['zipcode'] = $addr->zipcode;
                $userAddress[$key]['addr'] = $addr->addr;
                $userAddress[$key]['order_id'] = $order_id;
            }
            try {
                DB::table('ord_address')->insert($userAddress);
            } catch (\Exception $e) {
                DB::rollBack();
                return ['success' => '0', 'message' => 'address format error'];
            }
            //   dd($order);
            foreach ($order['shipments'] as $value) {
                $sub_order_sn = $order_sn . str_pad((DB::table('ord_sub_orders')->where('order_id', $order_id)
                        ->get()
                        ->count()) + 1, 2, '0', STR_PAD_LEFT);

                $insertData = [
                    'order_id' => $order_id,
                    'order_sn' => $sub_order_sn,
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
                        'sku' => $product->sku,
                        'product_title' => $product->product_title . $product->spec,
                        'price' => $product->price,
                        'qty' => $product->qty,
                        'total_price' => $product->total_price,
                    ]);

                }

            }

            return ['success' => '1', 'order_id' => $order_id];
        });

    }

}
