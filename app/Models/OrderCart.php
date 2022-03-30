<?php

namespace App\Models;

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
        $order = ['total_price' => 0, 'total_dlv_fee' => 0,
            'shipments' => [], 'discounts' => [],
            'coupons' => []];

        $discountProductIds = [1, 2];
        $discountedCouponProducts = [
            'total_price' => 0,
            'styles' => [],
            'method' => 'percent',
            'discount_value' => 90,
        ];

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
            $shipmentGroup[$idx]->products[] = $style;
            $shipmentGroup[$idx]->totalPrice += $style->total_price;

            if (in_array($style->product_id, $discountProductIds)) {

                $discountedCouponProducts['styles'][] = [
                    'groupIdx' => $idx,
                    'productIdx' => count($shipmentGroup[$idx]->products) - 1,
                    'total_price' => $style->total_price,
                ];

                $discountedCouponProducts['total_price'] = $discountedCouponProducts['total_price'] + $style->total_price;
            }

        }
        /*
         //coupon prototype 
         
        $couponDiscount = $discountedCouponProducts['total_price'] - $discountedCouponProducts['total_price'] / 100 * $discountedCouponProducts['discount_value'];
        $proportion = $discountedCouponProducts['total_price'] / $couponDiscount;
        // dd($proportion);
        foreach ($discountedCouponProducts['styles'] as $key => $value) {
            $discountedCouponProducts['styles'][$key]['discount'] = floor($value['total_price'] / $proportion);
            $groupIdx = $discountedCouponProducts['styles'][$key]['groupIdx'];
            $styleIdx = $discountedCouponProducts['styles'][$key]['productIdx'];
            // dd( $shipmentGroup[$groupIdx]->products[$styleIdx]->discount);
            $shipmentGroup[$groupIdx]->products[$styleIdx]->discount = $discountedCouponProducts['styles'][$key]['discount'];
            $shipmentGroup[$groupIdx]->totalPrice -= $discountedCouponProducts['styles'][$key]['discount'];
        }
        */
     
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

        $discount = Discount::calculatorDiscount($order['total_price']);

        if ($discount) {
            if ($discount['discount']) {
                $order['discounts'][] = $discount['discount'];
            }

            foreach ($discount['coupons'] as $coupon) {
                $order['discounts'][] = $coupon;
            }
        }

        $order['origin_price'] = $discount['origin_price'];
        $order['total_price'] = $discount['result_price'] + $order['total_dlv_fee'];
        $order['total_discount_price'] = isset($discount['discount']->currentDiscount) ? $discount['discount']->currentDiscount : 0;
        $order['discounted_price'] = $order['origin_price'] - $order['total_discount_price'];
        //  dd($order);
        $order['shipments'] = $shipmentGroup;
        $order['success'] = 1;

        return $order;

    }

}
