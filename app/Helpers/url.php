<?php

use App\Enums\Globals\AppEnvClass;
use App\Enums\Globals\ImageDomain;
use App\Models\PayingOrder;
use App\Models\ReceivedOrder;
use Illuminate\Support\Facades\App;

if (!function_exists('isActive')) {
    function isActive(String $routeName, String $currentRouteName)
    {
        if ($routeName === $currentRouteName) {
            return 'active';
        } else {
            return '';
        }
    }
}

if (!function_exists('getPageCount')) {
    function getPageCount($pageCount)
    {
        $maxPage = config('global.dataPerPage');
        if (!is_numeric($pageCount)) {
            return $maxPage[0];
        }

        rsort($maxPage);
        if ($pageCount > $maxPage[0]) {
            return $maxPage[0];
        }

        return $pageCount;

    }
}

if (!function_exists('concatStr')) {
    function concatStr($data, $orderBy = null)
    {
        $arr = [];
        foreach ($data as $key => $d) {
            $arr[] = '\\"' . $key . '\\":\\"",' . $d . ',"\\"';
        }
        $orderBy = $orderBy ? ' ' . $orderBy : '';
        return 'CONCAT("[",' . 'GROUP_CONCAT("{' . implode(',', $arr) . '}"' . $orderBy . ')' . ',"]")';

    }
}

if (!function_exists('frontendUrl')) {
    /**
     * 回傳一般商品（例如：不含酒類）的網域
     * @return mixed|string
     */
    function frontendUrl()
    {

        $url = '';
        switch (AppEnvClass::fromValue(env('APP_ENV'))) {
            case AppEnvClass::Local():
                $url = env('FRONTEND_LOCAL_URL');
                break;
            case AppEnvClass::Development():
                $url = env('FRONTEND_DEV_URL');
                break;
            case AppEnvClass::Release():
                $url = env('FRONTEND_URL');
                break;
        }
        return $url;

    }
}

if (!function_exists('getImageUrl')) {
    /**
     * 回傳商品資訊url
     */
    function getImageUrl($subImageUrl, $cdn = false)
    {
        if (preg_match('/.*\/(cyberbiz|liquor)\/.*/', $subImageUrl) === 1) {
            return ImageDomain::CDN . $subImageUrl;
        }
        if (App::environment(AppEnvClass::Release) && $cdn) {
            return ImageDomain::CDN . $subImageUrl;
        } else {
            return asset($subImageUrl);
        }
    }
}
if (!function_exists('getErpOrderUrl')) {
    // 取得各類訂單頁面
    function getErpOrderUrl($order)
    {

        switch ($order->order_type) {
            case "O":
                $order->url = route('cms.order.detail', ['id' => $order->order_id]);
                break;
            case "KSG":
                $order->url = route('cms.request.show', ['id' => $order->order_id]);
                break;
            case "MSG":
                $ro = ReceivedOrder::find($order->order_id);
                $url = '';
                if ($ro) {
                    $url = ReceivedOrder::received_order_link($ro->source_type, $ro->source_id);
                }
                $order->url = $url;
                break;
            case "PSG":
                $order->url = route('cms.stitute.show', ['id' => $order->order_id]);
                break;
            case "ISG":
                $po = PayingOrder::find($order->order_id);
                $url = '';
                if ($po) {
                    $url = PayingOrder::paying_order_link($po->source_type, $po->source_id, $po->source_sub_id, $po->type);
                }
                $order->url = $url;
                break;
            case "B":
                $order->url = route('cms.purchase.edit', ['id' => $order->order_id]);
                break;
            case "EXP":
                $order->url = route('cms.expenditure.show', ['id' => $order->order_id]);
                break;
            case "PET":
                $order->url = route('cms.petition.show', ['id' => $order->order_id]);
                break;

            default:
                $order->url = '#';
        }

        return $order;
    }
}
