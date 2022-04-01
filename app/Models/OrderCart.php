<?php

namespace App\Models;

use App\Enums\Discount\DisMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderCart extends Model
{
    use HasFactory;
    protected $table = 'ord_cart';
    protected $guarded = [];
    public $timestamps = false;

    public static function productAdd($customer_id, $product_id, $product_style_id, $qty, $shipment_type, $shipment_event_id = null)
    {
        if (!self::where('customer_id', $customer_id)->where('product_style_id', $product_style_id)->get()->first()) {
            self::create([
                'customer_id' => $customer_id,
                'product_id' => $product_id,
                'product_style_id' => $product_style_id,
                'qty' => $qty,
                'shipment_type' => $shipment_type,
                'shipment_event_id' => $shipment_event_id,
            ]);
        }
    }

    public static function productRemove($id)
    {
        self::where('id', $id)->delete();
    }

    public static function productUpdate($id, $qty)
    {
        self::where('id', $id)->update(['qty' => $qty]);
    }

    public static function productList($customer_id)
    {
        $productSubQuery = DB::table('prd_product_styles as style')
            ->leftJoin('prd_salechannel_style_price as price', 'style.id', '=', 'price.style_id')
            ->leftJoin('prd_products as product', 'style.product_id', '=', 'product.id')
            ->select('product.title as product_title', 'style.title as product_style_title', 'style.id as style_id', 'price.price')
            ->where('price.sale_channel_id', 1);

        $cart = DB::table('ord_cart as cart')
            ->leftJoin(DB::raw("({$productSubQuery->toSql()}) as style"), function ($join) {
                $join->on('cart.product_style_id', '=', 'style.style_id');
            })
            ->select('cart.id as id', 'cart.customer_id as customer_id', 'product_title', 'product_style_title', 'price', 'shipment_type', 'shipment_event_id')
            ->mergeBindings($productSubQuery)
            ->where('cart.customer_id', $customer_id);

        // dd($cart->get()->toArray());
        return $cart;

    }

    public static function cartFormater($data, $checkInStock = true)
    {
        $shipmentGroup = [];
        $shipmentKeys = [];
        $order = [
            'origin_price' => 0,
            'total_price' => 0,
            'total_dlv_fee' => 0,
            'total_discount_price' => 0,
            'discounted_price' => 0,
            'shipments' => [],
            'discounts' => [],
        ];

        $_tempProducts = [];

        foreach ($data as $value) {
            $style = Product::productStyleList(null, null, null, ['price' => 1])->where('s.id', $value['product_style_id'])
                ->get()->first();

            if (!$style) {
                return ['success' => 0, 'error_msg' => '查無此商品', 'event' => 'product', 'event_id' => $value['product_style_id']];
            }
            if ($checkInStock) {
                if ($value['qty'] > $style->in_stock) {
                    return ['success' => 0, 'error_msg' => '購買超過上限', 'event' => 'product', 'event_id' => $value['product_style_id']];
                }
            }
            // shipment
            switch ($value['shipment_type']) {
                case 'pickup':
                    $shipment = Product::getPickup($value['product_id'])->where('depot.id', $value['shipment_event_id'])->get()->first();
                    if (!$shipment) {
                        return ['success' => 0, 'error_msg' => '無運送方式(自取)', 'event' => 'product', 'event_id' => $value['product_style_id']];
                    }

                    $shipment->category_name = "自取";

                    break;
                case 'deliver':
                    $shipment = Product::getShipment($value['product_id'])->where('g.id', $value['shipment_event_id'])->get()->first();

                    if (!$shipment) {
                        return ['success' => 0, 'error_msg' => '無運送方式(宅配)', 'event' => 'product', 'event_id' => $value['product_style_id']];
                    }
                    $shipment->rules = json_decode($shipment->rules);

                    break;
                default:
                    return ['success' => 0, 'error_msg' => '無運送方式', 'event' => 'product', 'event_id' => $value['product_style_id']];
            }

            $groupKey = $value['shipment_type'] . '-' . $value['shipment_event_id'];
            if (!in_array($groupKey, $shipmentKeys)) {
                $shipmentKeys[] = $groupKey;
                $shipment->products = [];
                $shipment->origin_price = 0;
                $shipment->discounted_price = 0;
                $shipment->discount_value = 0;
                $shipment->category = $value['shipment_type'];
                $shipmentGroup[] = $shipment;
            }

            $idx = array_search($groupKey, $shipmentKeys);
            $style->shipment_type = $value['shipment_type'];
            $style->product_id = $value['product_id'];
            $style->product_style_id = $value['product_style_id'];
            $style->qty = $value['qty'];
            $style->dlv_fee = 0;
            $style->total_price = $value['qty'] * $style->price;
            $style->discount = 0;
            $style->discounted_price = $style->total_price;
            $style->discounts = [];
            $shipmentGroup[$idx]->products[] = $style;
            $shipmentGroup[$idx]->origin_price += $style->total_price;
            $shipmentGroup[$idx]->discounted_price = $shipmentGroup[$idx]->origin_price;
            $_tempProducts[] = [
                'groupIdx' => $idx,
                'productIdx' => count($shipmentGroup[$idx]->products) - 1,
                'total_price' => $style->total_price,
                'product_id' => $style->product_id,
            ];

        }
        // get coupon
        $currentCoupon = Discount::checkCode('fkfk', array_map(function ($n) {
            return $n['product_id'];
        }, $_tempProducts));

        if ($currentCoupon['success'] == '1') {
            $currentCoupon = $currentCoupon['data'];
        }
        // shipment 計算
        foreach ($shipmentGroup as $key => $ship) {
            //  dd($ship);
            switch ($ship->category) {
                case "deliver":
                    foreach ($ship->rules as $rule) {
                        $use_rule = false;
                        if ($rule->is_above == 'false') {
                            if ($ship->origin_price >= $rule->min_price && $ship->origin_price < $rule->max_price) {
                                $shipmentGroup[$key]->dlv_fee = $rule->dlv_fee;
                                $use_rule = $rule;
                            }
                        } else {
                            if ($ship->origin_price >= $rule->max_price) {
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
                default:
                    $shipmentGroup[$key]->dlv_fee = 0;

            }

            $order['origin_price'] += $ship->origin_price;
            $order['total_dlv_fee'] += $shipmentGroup[$key]->dlv_fee;

        }
        // discounted init
        $order['discounted_price'] = $order['origin_price'];
        $order['shipments'] = $shipmentGroup;
        // 全館
        self::globalStage($order);

        self::couponStage($order, $currentCoupon, $_tempProducts);

        dd($order);
        $order['success'] = 1;

        return $order;

    }

    private static function globalStage(&$order)
    {
        $discounts = Discount::getDiscounts('global-normal');

        //   dd($discounts);
        $dis = [];
        $coupons = [];
        foreach ($discounts as $key => $value) {

            switch ($key) {
                case DisMethod::cash()->value:

                    foreach ($value as $cash) {
                        if ($cash->min_consume == 0 || $cash->min_consume < $order['origin_price']) {
                            if ($cash->is_grand_total == 1) {
                                $cash->currentDiscount = intval(floor($order['origin_price'] / $cash->min_consume) * $cash->discount_value);
                                $cash->title = $cash->title . "(累計)";

                            } else {
                                $cash->currentDiscount = $cash->discount_value;
                            }
                            $dis[] = $cash;
                        }

                    }

                    break;
                case DisMethod::percent()->value:
                    foreach ($value as $cash) {
                        if ($cash->min_consume == 0 || $cash->min_consume < $order['origin_price']) {
                            $cash->currentDiscount = $order['origin_price'] - intval($order['origin_price'] / 100 * $cash->discount_value);
                            $cash->discount_value = $cash->discount_value;
                            $dis[] = $cash;
                        }
                    }
                    break;
                case DisMethod::coupon()->value:

                    foreach ($value as $cash) {
                        if ($cash->min_consume == 0 || $cash->min_consume < $order['origin_price']) {
                            $cash->title = $cash->title . " (下次使用)";
                            $coupons[] = $cash;
                        }
                    }
                    break;

            }
        }

        usort($dis, function ($a, $b) {
            return strcmp($b->currentDiscount, $a->currentDiscount);
        });

        if ($dis[0]) {
            $order['discounts'][] = $dis[0];
            $order['total_discount_price'] += $dis[0]->currentDiscount;
            $order['discounted_price'] -= $dis[0]->currentDiscount;
        }

        foreach ($coupons as $coupon) {
            $order['discounts'][] = $coupon;
        }

    }

    private static function couponStage(&$order, $currentCoupon, $_tempProducts)
    {
        $discount_value = 0;
        if ($currentCoupon->is_global == '1') {
            if ($order['discounted_price'] > $currentCoupon->min_consume) {
                $discount_value = $currentCoupon->discount_value;
                $order->discounts[] = $currentCoupon;
            }
        } else {
            $couponTargetProducts = ['styles' => [],
                'total_price' => 0,
                'discount' => 0];
            //  dd($currentCoupon);
            foreach ($_tempProducts as $value) {
                if (in_array($value['product_id'], $currentCoupon->product_ids)) {
                    $couponTargetProducts['total_price'] += $value['total_price'];
                    $couponTargetProducts['styles'][] = $value;
                }
            }

            if (count($couponTargetProducts['styles']) > 0) {
                switch ($currentCoupon->method_code) {
                    case DisMethod::cash():
                        $couponTargetProducts['discount'] = $currentCoupon->discount_value;
                        $couponTargetProducts['discounted_price'] = $couponTargetProducts['total_price'] - $couponTargetProducts['discount'];
                        break;
                    case DisMethod::percent():
                        $tPrice = $couponTargetProducts['total_price'];
                        $couponTargetProducts['discount'] = floor($tPrice - $tPrice / 100 * $currentCoupon->discount_value);
                        $couponTargetProducts['discounted_price'] = $tPrice - $couponTargetProducts['discount'];

                        break;
                    default:

                }

                $proportion = $couponTargetProducts['discounted_price'] / $couponTargetProducts['total_price'];
                $tempDiscount = 0;
                foreach ($couponTargetProducts['styles'] as $value) {
                    $gIdx = $value['groupIdx'];
                    $pIdx = $value['productIdx'];
                    $tPrice = $order['shipments'][$gIdx]->products[$pIdx]->total_price;
                    $discount_value = floor($tPrice - $tPrice * $proportion);
                    $order['shipments'][$gIdx]->products[$pIdx]->discount = $discount_value;
                    $order['shipments'][$gIdx]->products[$pIdx]->discounted_price = $tPrice - $discount_value;
                    $tempDiscount += $discount_value;
                    $order['shipments'][$gIdx]->products[$pIdx]->discounts[] = $currentCoupon;

                    $order['shipments'][$gIdx]->discount_value += $discount_value;
                    $order['shipments'][$gIdx]->discounted_price -= $discount_value;
                }
                if ($currentCoupon->method_code == DisMethod::cash() && $currentCoupon->discount_value > $tempDiscount && count($couponTargetProducts['styles']) > 0) {
                    $lastStyle = $couponTargetProducts['styles'][count($couponTargetProducts['styles']) - 1];

                    $gIdx = $lastStyle['groupIdx'];
                    $pIdx = $lastStyle['productIdx'];
                    $fix_value = $currentCoupon->discount_value - $tempDiscount;
                    $order['shipments'][$gIdx]->products[$pIdx]->discount += $fix_value;
                    $order['shipments'][$gIdx]->products[$pIdx]->discounted_price -= $fix_value;
                    $order['shipments'][$gIdx]->discount_value += $fix_value;
                    $order['shipments'][$gIdx]->discounted_price -= $fix_value;
                }

                $discount_value = $couponTargetProducts['discount'];

            }

        }

        $order['discounted_price'] -= $discount_value;
        $order['total_discount_price'] += $discount_value;

    }

}
