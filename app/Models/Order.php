<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = 'ord_orders';
    protected $guarded = [];

    public static function createOrderFromData($data)
    {
        $data = [[
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
        ];

        $shipmentGroup = [];
        $shipmentKeys = [];
        foreach ($data as $value) {
            $style = Product::productStyleList(null, null, null, ['price' => 1])->where('s.id', $value['product_style_id'])
                ->get()->first();

            if (!$style) {
                return ['success' => 0, 'message' => '查無此商品 style_id:' . $value['product_style_id']];
            }
            if ($value['qty'] > $style->in_stock) {
                return ['success' => 0, 'message' => '購買超過上限 style_id:' . $value['product_style_id']];
            }

            switch ($value['shipment_type']) {
                case 'pickup':
                    $shipment = Product::getPickup($value['product_id'])->where('pick_up.id', $value['shipment_event_id'])->get()->first();
                    if (!$shipment) {
                        return ['success' => 0, 'message' => '無運送方式 style_id:' . $value['product_style_id']];
                    }

                    break;
                case 'deliver':
                    $shipment = Product::getShipment($value['product_id'])->where('g.id', $value['product_style_id'])->get()->first();
                    if (!$shipment) {
                        return ['success' => 0, 'message' => '無運送方式 style_id:' . $value['product_style_id']];
                    }
                    $shipment->rules = json_decode($shipment->rules);

                    break;
                default:
                    return ['success' => 0, 'message' => '無運送方式 style_id:' . $value['product_style_id']];
            }

            $groupKey = $value['shipment_type'] . '-' . $value['shipment_event_id'];
            if (!in_array($groupKey, $shipmentKeys)) {
                $shipmentKeys[] = $groupKey;
                $shipment->products = [];
                $shipment->totalPrice = 0;
                $shipment->event = $value['shipment_type'];
                $shipmentGroup[] = $shipment;
            }

            $idx = array_search($groupKey, $shipmentKeys);
            $style->shipment_type = $value['shipment_type'];
            $style->qty = $value['qty'];
            $style->dlv_fee = 0;
            $style->total_price = $value['qty'] * $style->price;
            $shipmentGroup[$idx]->products[] = $style;
            $shipmentGroup[$idx]->totalPrice += $style->total_price;

        }
        foreach ($shipmentGroup as $key => $ship) {

            switch ($ship->event) {
                case "deliver":
                    foreach ($ship->rules as $rule) {
                        $use_rule = false;
                        if ($rule->is_above == 'false') {
                            if ($ship->totalPrice >= $rule->min_price && $ship->totalPrice < $rule->max_price) {
                                $shipmentGroup[$key]->dlv_fee = $rule->dlv_fee;
                                $use_rule = $rule;
                            }
                        } else {
                            if ($ship->totalPrice >= $rule->max_price) {
                                $shipmentGroup[$key]->dlv_fee = $rule->dlv_fee;
                                $use_rule = $rule;

                            }
                        }

                        if ($use_rule) {
                            $shipmentGroup[$key]->use_rule = $use_rule;
                            break;
                        }
                    }
                    break;
            }
        }

        dd($shipmentGroup);

    }
}
