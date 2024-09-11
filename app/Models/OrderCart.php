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

    public static function cartFormater($data, $salechannel_id, $coupon_obj = null, $checkInStock = true, $customer = null, $dividend = null)
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
            'salechannel_id' => $salechannel_id,
            'get_dividend' => 0,
            'max_dividend' => 0,
            'use_dividend' => 0,
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
                    'error_status' => 'no_product',
                ];
            }
            if (!isset($errors[$value['product_style_id']])) {

                if ($checkInStock) {
                    if ($value['qty'] > $style->in_stock) {
                        $errors[$value['product_style_id']][] = [
                            'error_msg' => '購買超過上限',
                            'error_status' => 'overbought',
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
                                'error_status' => 'shipment',
                            ];
                            //  return ['success' => 0, 'error_msg' => '無運送方式(自取)', 'event' => 'product', 'event_id' => $value['product_style_id']];
                        } else {
                            $shipment->category_name = "自取";
                        }

                        break;
                    case 'deliver':
                        $shipment = Product::getShipment($value['product_id'])->where('g.id', $value['shipment_event_id'])->get()->first();

                        if (!$shipment) {
                            $errors[$value['product_style_id']][] = [
                                'error_msg' => '無運送方式(宅配)',
                                'error_status' => 'shipment',
                            ];
                            //  return ['success' => 0, 'error_msg' => '無運送方式(宅配)', 'event' => 'product', 'event_id' => $value['product_style_id']];
                        } else {
                            $shipment->rules = json_decode($shipment->rules);
                        }

                        break;
                    default:
                        $errors[$value['product_style_id']][] = [
                            'error_msg' => '無運送方式',
                            'error_status' => 'shipment',
                        ];
                        //   return ['success' => 0, 'error_msg' => '無運送方式', 'event' => 'product', 'event_id' => $value['product_style_id']];
                }

                if (!isset($errors[$value['product_style_id']])) {

                    $groupKey = $value['shipment_type'] . '_' . $value['shipment_event_id'];

                    if (!in_array($groupKey, $shipmentKeys)) {
                        $shipmentKeys[] = $groupKey;
                        $shipment->products = [];
                        $shipment->origin_price = 0;
                        $shipment->discounted_price = 0;
                        $shipment->discount_value = 0;
                        $shipment->discounts = [];
                        $shipment->category = $value['shipment_type'];
                        $shipmentGroup[] = $shipment;

                        if ($dividend && isset($dividend[$groupKey])) {
                            $shipment->dividend = $dividend[$groupKey];
                        } else {
                            $shipment->dividend = 0;
                        }
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

                    $order['max_dividend'] += ($style->dividend * $value['qty']);
                }
            }
        }

        if ($errors) {
            return [
                'success' => '0',
                'error_msg' => $errors,
                'event' => 'check',
            ];
        }

        $order['shipments'] = $shipmentGroup;
        foreach ($shipmentGroup as $shipments) {
            $order['origin_price'] += $shipments->origin_price;
            $order['use_dividend'] += $shipments->dividend;
        }

        $currentCoupon = null;

        if ($coupon_obj && $coupon_obj[0]) {
            switch ($coupon_obj[0]) {
                case DisCategory::code():
                    $currentCoupon = Discount::checkCode($coupon_obj[1], array_map(function ($n) {
                        return $n['product_id'];
                    }, $_tempProducts));

                    if ($currentCoupon['success'] == '1') {
                        $currentCoupon = $currentCoupon['data'];
                    } else {
                        return ['success' => 0, 'error_msg' => $currentCoupon['message'], 'event' => 'coupon'];
                    }
                    break;
                case DisCategory::coupon():
                    if ($customer) {

                        $currentCoupon = CustomerCoupon::getCouponByCustomerCouponId($coupon_obj[1])->get()->first();

                        if (!$currentCoupon) {
                            return ['success' => 0, 'error_msg' => "查無優惠券", 'event' => 'coupon'];
                        }

                        $currentCoupon->user_coupon_id = $coupon_obj[1];
                    }
                    break;
            }
        }

        // discounted init
        $order['discounted_price'] = $order['origin_price'];

        // 全館

        self::globalStage($order, $_tempProducts);

        self::couponStage($order, $currentCoupon, $_tempProducts);
        $re = self::useDividendStage($order, $customer);
        if ($re['success'] == '0') {
            return $re;
        }

        self::shipmentStage($order);

        if ($order['discounted_price'] < 0) {
            $order['discounted_price'] = 0;
        }

        self::getDividendStage($order, $_tempProducts);

        $order['total_price'] = $order['discounted_price'] + $order['dlv_fee'];
        if ($order['total_price'] < 10) {
            return [
                'success' => '0',
                'error_msg' => '總金額不能低於10',
                'event' => 'total_price',
            ];
        }
        $order['success'] = 1;

        return $order;
    }

    private static function globalStage(&$order, &$_tempProducts)
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
                    $salechannel = SaleChannel::where('id', $order['salechannel_id'])
                        ->where('use_coupon', 1)
                        ->get()->first();

                    if ($salechannel) {
                        foreach ($value as $cash) {
                            if ($cash->min_consume == 0 || $cash->min_consume < $order['origin_price']) {
                                $cc = floor($order['origin_price'] / $cash->min_consume);

                                $cash->title = $cash->title . " (下次使用)";
                                for ($i = 0; $i < $cc; $i++) {
                                    $coupons[] = $cash;
                                }
                            }
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

    private static function _discountReturnToProducts($discount, &$order, &$_tempProducts)
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
        foreach ($_tempProducts as $key => $p) {
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

            if ($order['shipments'][$p['groupIdx']]->products[$p['productIdx']]->discounted_price < 0) {
                $order['shipments'][$p['groupIdx']]->products[$p['productIdx']]->discounted_price = 0;
            };

            if ($order['shipments'][$p['groupIdx']]->discounted_price < 0) {
                $order['shipments'][$p['groupIdx']]->discounted_price = 0;
            }

            $_tempProducts[$key]['total_price'] = $order['shipments'][$p['groupIdx']]->products[$p['productIdx']]->discounted_price;
        }

        $fix = $discount->currentDiscount - $calc;
        $lp = $_tempProducts[count($_tempProducts) - 1];

        $order['shipments'][$lp['groupIdx']]->products[$lp['productIdx']]->discount_value += $fix;
        $order['shipments'][$lp['groupIdx']]->products[$lp['productIdx']]->discounted_price -= $fix;
        $order['shipments'][$lp['groupIdx']]->discount_value += $fix;
        $order['shipments'][$lp['groupIdx']]->discounted_price -= $fix;

        if ($order['shipments'][$lp['groupIdx']]->products[$lp['productIdx']]->discounted_price < 0) {
            $order['shipments'][$lp['groupIdx']]->products[$lp['productIdx']]->discounted_price = 0;
        }

        if ($order['shipments'][$lp['groupIdx']]->discounted_price < 0) {
            $order['shipments'][$lp['groupIdx']]->discounted_price = 0;
        }

        $order['shipments'][$p['groupIdx']]->products[$p['productIdx']]->discounts[count($order['shipments'][$p['groupIdx']]->products[$p['productIdx']]->discounts) - 1]->currentDiscount += $fix;
    }

    private static function couponStage(&$order, $currentCoupon, $_tempProducts)
    {
        if (!$currentCoupon) {
            return;
        }

        $discount_value = 0;
        if ($currentCoupon->is_global == '1') {
            if ($order['discounted_price'] >= $currentCoupon->min_consume) {

                switch ($currentCoupon->method_code) {
                    case DisMethod::cash():
                        $currentCoupon->currentDiscount = $currentCoupon->discount_value;
                        $discount_value = $currentCoupon->discount_value;
                        break;
                    case DisMethod::percent():
                        $discount_rate = 100 - $currentCoupon->discount_value;
                        $currentCoupon->currentDiscount = ceil($order['discounted_price'] / 100 * $discount_rate);
                        $discount_value = $currentCoupon->currentDiscount;
                        break;
                    default:
                }

                $order['discounts'][] = $currentCoupon;
                self::_discountReturnToProducts($currentCoupon, $order, $_tempProducts);
            }
        } else {

            $couponTargetProducts = [
                'styles' => [],
                'total_price' => 0,
                'discount' => 0
            ];
            //  dd($currentCoupon);
            foreach ($_tempProducts as $value) {
                if (in_array($value['product_id'], $currentCoupon->product_ids)) {
                    $couponTargetProducts['total_price'] += $value['total_price'];
                    $couponTargetProducts['styles'][] = $value;
                }
            }

            // dd($order, $_tempProducts, $couponTargetProducts);

            if (count($couponTargetProducts['styles']) > 0) {
                switch ($currentCoupon->method_code) {
                    case DisMethod::cash():
                        $currentCoupon->currentDiscount = $currentCoupon->discount_value;
                        $discount_value = $currentCoupon->discount_value;
                        break;
                    case DisMethod::percent():
                        $discount_rate = 100 - $currentCoupon->discount_value;
                        $currentCoupon->currentDiscount = ceil($couponTargetProducts['total_price'] / 100 * $discount_rate);
                        $discount_value = $currentCoupon->currentDiscount;

                        break;
                    default:
                }
                self::_discountReturnToProducts($currentCoupon, $order, $couponTargetProducts['styles']);

                $order['discounts'][] = $currentCoupon;
            }
        }

        $order['discounted_price'] -= $discount_value;
        $order['discount_value'] += $discount_value;
    }

    private static function useDividendStage(&$order, $customer)
    {
        if (!$customer) {
            return ['success' => '1'];
        }

        $dividend = 0;
        $di = CustomerDividend::getDividend($customer->id)->get()->first();

        if ($di && $di->dividend) {
            $dividend = $di->dividend;
        }

        if ($order['use_dividend'] > $dividend) {
            return [
                'success' => '0',
                'error_msg' => '超過可以使用點數',
                'event' => 'dividend',
            ];
        }

        if ($dividend) {
            if ($order['use_dividend'] <= $order['max_dividend']) {
                $discountObj = (object) [
                    'title' => DisCategory::dividend()->description . "折抵",
                    'category_title' => DisCategory::dividend()->description,
                    'category_code' => DisCategory::dividend()->value,
                    'method_code' => DisMethod::cash()->value,
                    'method_title' => DisMethod::cash()->description,
                    'discount_value' => $order['use_dividend'],
                    'currentDiscount' => $order['use_dividend'],
                    'is_grand_total' => 0,
                    'min_consume' => 0,
                    'coupon_id' => null,
                    'coupon_title' => null,
                    'discount_grade_id' => null,
                ];

                $order['discounts'][] = $discountObj;

                $order['total_price'] -= $order['use_dividend'];
                $order['discount_value'] += $order['use_dividend'];
                $order['discounted_price'] -= $order['use_dividend'];

                foreach ($order['shipments'] as $idx => $shipment) {

                    if ($shipment->dividend) {
                        $order['shipments'][$idx]->discount_value += $shipment->dividend;
                        $order['shipments'][$idx]->discounted_price -= $shipment->dividend;
                        if (!isset($order['shipments'][$idx]->discounts)) {
                            $order['shipments'][$idx]->discounts = [];
                        }
                        $sub_discountObj = clone $discountObj;
                        $sub_discountObj->discount_value = $shipment->dividend;
                        $sub_discountObj->currentDiscount = $shipment->dividend;
                        $order['shipments'][$idx]->discounts[] = $sub_discountObj;
                    }
                }
            } else {
                return [
                    'success' => '0',
                    'error_msg' => '超過鴻利折抵額度',
                    'error_status' => 'dividend'
                ];
            }
        }

        return ['success' => '1'];
    }

    private static function getDividendStage(&$order, $_tempProducts)
    {
        $salechannel = SaleChannel::where('id', $order['salechannel_id'])->get()->first();
        $today = date('Y-m-d H:i:s');

        if (
            $salechannel->event_sdate && $salechannel->event_edate &&
            ($today >= date('Y-m-d H:i:s', strtotime($salechannel->event_sdate)) &&
                $today <= strtotime($salechannel->event_edate))
        ) {
            $rate = $salechannel->event_dividend_rate;
        } else {
            $rate = $salechannel->dividend_rate;
        }

        $order['get_dividend'] = floor($order['discounted_price'] * $rate / 100);

        // foreach ($_tempProducts as $value) {

        //     $order['get_dividend'] += floor($value['total_price'] * $rate / 100);
        // }

    }

    private static function shipmentStage(&$order)
    {
        //     dd($order);
        foreach ($order['shipments'] as $key => $ship) {
            //  dd($ship);
            switch ($ship->category) {
                case "deliver":
                    foreach ($ship->rules as $rule) {
                        $use_rule = false;
                        $usedDividend = $ship->dividend ?  $ship->dividend : 0;
                        $discounted_price = ($ship->discounted_price > 0) ? $ship->discounted_price + $usedDividend : 0;
                        // dd($ship->discounted_price,$ship->dividend);
                        if ($rule->is_above == 'false') {
                            if ($discounted_price >= $rule->min_price && $discounted_price < $rule->max_price) {
                                $order['shipments'][$key]->dlv_fee = $rule->dlv_fee;
                                $use_rule = $rule;
                            }
                        } else {
                            if ($discounted_price >= $rule->max_price) {
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
