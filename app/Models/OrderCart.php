<?php

namespace App\Models;

use App\Enums\Discount\DisCategory;
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
    /**
     * @param array $coupon_obj ["type"=>"code/sn","value"=>"string"]
     */

    public static function cartFormater($data, $salechannel_id, $coupon_obj = null, $checkInStock = true)
    {
        $shipmentGroup = [];
        $shipmentKeys = [];
        $order = [
            'origin_price' => 0,
            'total_price' => 0,
            'dlv_fee' => 0,
            'discount_value' => 0,
            'discounted_price' => 0,
            'shipments' => [],
            'discounts' => [],
        ];

        $_tempProducts = [];
        $errors = [];
        //  step1 validate;
        foreach ($data as $value) {

            $style = Product::productStyleList(null, null, null, ['price' => $salechannel_id])
                ->where('s.id', $value['product_style_id'])
                ->get()
                ->first();

            if (!$style) {
                $errors[$value['product_style_id']][] = [
                    'error_msg' => '查無此商品',
                    'error_stauts' => 'no_product',
                ];
            }
            if (!isset($errors[$value['product_style_id']])) {

                if ($checkInStock) {
                    if ($value['qty'] > $style->in_stock) {
                        $errors[$value['product_style_id']][] = [
                            'error_msg' => '購買超過上限',
                            'error_stauts' => 'overbought',
                        ];
                        //  return ['success' => 0, 'error_msg' => '購買超過上限', 'event' => 'product', 'event_id' => $value['product_style_id']];
                    }
                }
                // shipment
                switch ($value['shipment_type']) {
                    case 'pickup':
                        $shipment = Product::getPickup($value['product_id'])->where('depot.id', $value['shipment_event_id'])->get()->first();
                        if (!$shipment) {
                            $errors[$value['product_style_id']][] = [
                                'error_msg' => '無運送方式(自取)',
                                'error_stauts' => 'shipment',
                            ];
                            //  return ['success' => 0, 'error_msg' => '無運送方式(自取)', 'event' => 'product', 'event_id' => $value['product_style_id']];
                        }

                        $shipment->category_name = "自取";

                        break;
                    case 'deliver':
                        $shipment = Product::getShipment($value['product_id'])->where('g.id', $value['shipment_event_id'])->get()->first();

                        if (!$shipment) {
                            $errors[$value['product_style_id']][] = [
                                'error_msg' => '無運送方式(宅配)',
                                'error_stauts' => 'shipment',
                            ];
                            //  return ['success' => 0, 'error_msg' => '無運送方式(宅配)', 'event' => 'product', 'event_id' => $value['product_style_id']];
                        }
                        $shipment->rules = json_decode($shipment->rules);

                        break;
                    default:
                        $errors[$value['product_style_id']][] = [
                            'error_msg' => '無運送方式',
                            'error_stauts' => 'shipment',
                        ];
                        //   return ['success' => 0, 'error_msg' => '無運送方式', 'event' => 'product', 'event_id' => $value['product_style_id']];
                }

                if (!isset($errors[$value['product_style_id']])) {

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
                    $style->origin_price = $value['qty'] * $style->price;
                    $style->discount_value = 0;
                    $style->discounted_price = $style->origin_price;
                    $style->discounts = [];
                    $shipmentGroup[$idx]->products[] = $style;
                    $shipmentGroup[$idx]->origin_price += $style->origin_price;
                    $shipmentGroup[$idx]->discounted_price = $shipmentGroup[$idx]->origin_price;
                    $_tempProducts[] = [
                        'groupIdx' => $idx,
                        'productIdx' => count($shipmentGroup[$idx]->products) - 1,
                        'total_price' => $style->origin_price,
                        'product_id' => $style->product_id,
                    ];
                }
            }
        }

        $order['shipments'] = $shipmentGroup;
        foreach ($shipmentGroup as $shipments) {
            $order['origin_price'] += $shipments->origin_price;
        }

        $currentCoupon = null;
        if ($coupon_obj && $coupon_obj[0]) {
            switch ($coupon_obj[0]) {
                case DisCategory::code();
                    $currentCoupon = Discount::checkCode($coupon_obj[1], array_map(function ($n) {
                        return $n['product_id'];
                    }, $_tempProducts));

                    if ($currentCoupon['success'] == '1') {
                        $currentCoupon = $currentCoupon['data'];
                    } else {
                        return ['success' => 0, 'error_msg' => $currentCoupon['message'], 'event' => 'coupon'];
                    }
                    break;
            }
        }

        // discounted init
        $order['discounted_price'] = $order['origin_price'];
        //   dd($order);

        // 全館

        self::globalStage($order, $_tempProducts);
        self::couponStage($order, $currentCoupon, $_tempProducts);
        self::shipmentStage($order);

        $order['total_price'] = $order['discounted_price'] + $order['dlv_fee'];
        $order['success'] = 1;

        return $order;

    }

    private static function globalStage(&$order, $_tempProducts)
    {
        $discounts = Discount::getDiscounts('global-normal');

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

                            $discount_rate = 100 - $cash->discount_value;
                            $cash->currentDiscount = ceil($order['origin_price'] / 100 * $discount_rate);

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
            return $b->currentDiscount > $a->currentDiscount;
        });

        if ($dis && $dis[0]) {
            $order['discounts'][] = $dis[0];
            $order['discount_value'] += $dis[0]->currentDiscount;
            $order['discounted_price'] -= $dis[0]->currentDiscount;
            self::_discountReturnToProducts($dis[0], $order, $_tempProducts);
        }

        foreach ($coupons as $coupon) {
            $order['discounts'][] = $coupon;
        }

    }

    private static function _discountReturnToProducts($discount, &$order, $_tempProducts)
    {

        switch ($discount->method_code) {
            case DisMethod::percent():
                $discount_rate = 100 - $discount->discount_value;
                break;
            case DisMethod::cash():
                $discount_rate = (1 - ($order['discounted_price'] - $discount->currentDiscount) / $order['discounted_price']) * 100;
                break;
        }

        $calc = 0;
        foreach ($_tempProducts as $p) {
            $discounted_price = $order['shipments'][$p['groupIdx']]->products[$p['productIdx']]->discounted_price;

            $currentDiscount = floor($discounted_price / 100 * $discount_rate);
            $calc += $currentDiscount;
            $discount2 = clone $discount;
            $discount2->currentDiscount = $currentDiscount;
            $order['shipments'][$p['groupIdx']]->products[$p['productIdx']]->discounts[] = $discount2;
            $order['shipments'][$p['groupIdx']]->products[$p['productIdx']]->discount_value += $currentDiscount;
            $order['shipments'][$p['groupIdx']]->products[$p['productIdx']]->discounted_price -= $currentDiscount;
            $order['shipments'][$p['groupIdx']]->discount_value += $currentDiscount;
            $order['shipments'][$p['groupIdx']]->discounted_price -= $currentDiscount;

        }

        $fix = $discount->currentDiscount - $calc;
        $lp = $_tempProducts[count($_tempProducts) - 1];

        $order['shipments'][$lp['groupIdx']]->products[$lp['productIdx']]->discount_value += $fix;
        $order['shipments'][$lp['groupIdx']]->products[$lp['productIdx']]->discounted_price -= $fix;
        $order['shipments'][$lp['groupIdx']]->discount_value += $fix;
        $order['shipments'][$lp['groupIdx']]->discounted_price -= $fix;
        $order['shipments'][$p['groupIdx']]->products[$p['productIdx']]->discounts[count($order['shipments'][$p['groupIdx']]->products[$p['productIdx']]->discounts) - 1]->currentDiscount += $fix;

    }

    private static function couponStage(&$order, $currentCoupon, $_tempProducts)
    {
        if (!$currentCoupon) {
            return;
        }
        $discount_value = 0;
        if ($currentCoupon->is_global == '1') {
            if ($order['discounted_price'] > $currentCoupon->min_consume) {

                switch ($currentCoupon->method_code) {
                    case DisMethod::cash():
                        $discount_value = $currentCoupon->discount_value;
                        break;
                    case DisMethod::percent():
                        $tPrice = $order['discounted_price'];
                        $discount_value = floor($tPrice - $tPrice / 100 * $currentCoupon->discount_value);
                        break;
                    default:
                }

                $order['discounts'][] = $currentCoupon;

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
                $_positions = [];
                foreach ($couponTargetProducts['styles'] as $value) {
                    $gIdx = $value['groupIdx'];
                    $pIdx = $value['productIdx'];
                    $tPrice = $order['shipments'][$gIdx]->products[$pIdx]->origin_price;
                    $discount_value = floor($tPrice - $tPrice * $proportion);
                    $order['shipments'][$gIdx]->products[$pIdx]->discount_value = $discount_value;
                    $order['shipments'][$gIdx]->products[$pIdx]->discounted_price = $tPrice - $discount_value;

                    $tempDiscount += $discount_value;
                    $order['shipments'][$gIdx]->products[$pIdx]->discounts[] = clone $currentCoupon;
                    $order['shipments'][$gIdx]->discount_value += $discount_value;
                    $order['shipments'][$gIdx]->discounted_price -= $discount_value;

                    $discount_idx = count($order['shipments'][$gIdx]->products[$pIdx]->discounts) - 1;
                    $_positions[] = [$gIdx, $pIdx, $discount_idx];

                    $order['shipments'][$gIdx]->products[$pIdx]->discounts[$discount_idx]->currentDiscount = $discount_value;

                }

                if ($currentCoupon->method_code == DisMethod::cash() && $currentCoupon->discount_value > $tempDiscount && count($couponTargetProducts['styles']) > 0) {
                    $lastStyle = $_positions[count($_positions) - 1];

                    $gIdx = $lastStyle[0];
                    $pIdx = $lastStyle[1];
                    $dIdx = $lastStyle[2];
                    $fix_value = $currentCoupon->discount_value - $tempDiscount;
                    $order['shipments'][$gIdx]->products[$pIdx]->discount_value += $fix_value;
                    $order['shipments'][$gIdx]->products[$pIdx]->discounted_price -= $fix_value;
                    $order['shipments'][$gIdx]->discount_value += $fix_value;
                    $order['shipments'][$gIdx]->discounted_price -= $fix_value;
                    $order['shipments'][$gIdx]->products[$pIdx]->discounts[$dIdx]->currentDiscount += $fix_value;
                }

                $discount_value = $couponTargetProducts['discount'];

            }

        }

        $order['discounted_price'] -= $discount_value;
        $order['discount_value'] += $discount_value;

    }

    private static function shipmentStage(&$order)
    {
        foreach ($order['shipments'] as $key => $ship) {
            //  dd($ship);
            switch ($ship->category) {
                case "deliver":
                    foreach ($ship->rules as $rule) {
                        $use_rule = false;
                        if ($rule->is_above == 'false') {
                            if ($ship->discounted_price >= $rule->min_price && $ship->discounted_price < $rule->max_price) {
                                $order['shipments'][$key]->dlv_fee = $rule->dlv_fee;
                                $use_rule = $rule;
                            }
                        } else {
                            if ($ship->discounted_price >= $rule->max_price) {
                                $order['shipments'][$key]->dlv_fee = $rule->dlv_fee;
                                $use_rule = $rule;

                            }
                        }

                        if ($use_rule) {
                            $order['shipments'][$key]->use_rule = $use_rule;
                            break;
                        }
                    }
                    break;
                default:
                    $order['shipments'][$key]->dlv_fee = 0;

            }

            $order['dlv_fee'] += $order['shipments'][$key]->dlv_fee;

        }
    }

}
