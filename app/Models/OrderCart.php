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
            'total_price' => 0,
            'total_dlv_fee' => 0,
            'shipments' => [],
            'discounts' => [],
            'coupons' => [],
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
                $shipment->totalPrice = 0;
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
            $shipmentGroup[$idx]->totalPrice += $style->total_price;

            $_tempProducts[] = [
                'groupIdx' => $idx,
                'productIdx' => count($shipmentGroup[$idx]->products) - 1,
                'total_price' => $style->total_price,
                'product_id' => $style->product_id,
            ];

        }

        $currentCoupon = Discount::checkCode('fkfk', array_map(function ($n) {
            return $n['product_id'];
        }, $_tempProducts));

        if ($currentCoupon['success'] == '1') {
            $currentCoupon = $currentCoupon['data'];
        }
       

        foreach ($shipmentGroup as $key => $ship) {
            //  dd($ship);
            switch ($ship->category) {
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
                default:
                    $shipmentGroup[$key]->dlv_fee = 0;

            }

            $order['total_price'] += $ship->totalPrice;
            $order['total_dlv_fee'] += $shipmentGroup[$key]->dlv_fee;

        }
        // 全館
        $discount = Discount::calculatorDiscount($order['total_price']);

        if ($discount) {
            if ($discount['discount']) {
                $order['discounts'][] = $discount['discount'];
            }

            foreach ($discount['coupons'] as $coupon) {
                $order['discounts'][] = $coupon;
            }
        }

        // 不變
        $order['origin_price'] = $discount['origin_price'];
        // 需減去優惠
        $order['total_price'] = $discount['result_price'] + $order['total_dlv_fee'];
        // 需增加
        $order['total_discount_price'] = isset($discount['discount']->currentDiscount) ? $discount['discount']->currentDiscount : 0;
        // 需減去優惠
        $order['discounted_price'] = $order['origin_price'] - $order['total_discount_price'];
        //  dd($order);
        $order['shipments'] = $shipmentGroup;

        // coupon處理
        if ($currentCoupon->is_global == '1') {
            if ($order['discounted_price'] > $currentCoupon->min_consume) {
                $order['discounted_price'] -= $currentCoupon->discount_value;
                $order['total_discount_price'] += $currentCoupon->discount_value;
                $order['total_price'] -= $currentCoupon->discount_value;
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

                    $order['shipments'][$gIdx]->products[$pIdx]->discount = floor($tPrice - $tPrice * $proportion);
                    $order['shipments'][$gIdx]->products[$pIdx]->discounted_price = $tPrice - $order['shipments'][$gIdx]->products[$pIdx]->discount;
                    $tempDiscount += $order['shipments'][$gIdx]->products[$pIdx]->discount;
                    $order['shipments'][$gIdx]->products[$pIdx]->discounts[] = $currentCoupon;

                }
                if ($currentCoupon->method_code == DisMethod::cash() && $currentCoupon->discount_value > $tempDiscount && count($couponTargetProducts['styles']) > 0) {
                    $lastStyle = $couponTargetProducts['styles'][count($couponTargetProducts['styles']) - 1];

                    $gIdx = $lastStyle['groupIdx'];
                    $pIdx = $lastStyle['productIdx'];
                    $order['shipments'][$gIdx]->products[$pIdx]->discount += $currentCoupon->discount_value - $tempDiscount;
                    $order['shipments'][$gIdx]->products[$pIdx]->discounted_price -= $currentCoupon->discount_value - $tempDiscount;
                }

                $order['discounted_price'] -= $couponTargetProducts['discount'];
                $order['total_discount_price'] += $couponTargetProducts['discount'];
                $order['total_price'] -= $couponTargetProducts['discount'];

            }

        }



        $order['success'] = 1;
      

        return $order;

    }

}
