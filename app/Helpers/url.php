<?php

use App\Enums\Globals\AppEnvClass;
use App\Enums\Globals\ImageDomain;
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
    function concatStr($data)
    {
        $arr = [];
        foreach ($data as $key => $d) {
            $arr[] = '\\"' . $key . '\\":\\"",' . $d . ',"\\"';
        }

        return 'CONCAT("[",' . 'GROUP_CONCAT("{' . implode(',', $arr) . '}")' . ',"]")';

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
        if (App::environment(AppEnvClass::Release) && $cdn) {
            return ImageDomain::CDN . $subImageUrl;
        } else {
            if (preg_match('/.*\/(cyberbiz|liquor)\/.*/', $subImageUrl) === 1) {
                return ImageDomain::CDN . $subImageUrl;
            } else {
                return asset($subImageUrl);
            }
        }
    }
}
