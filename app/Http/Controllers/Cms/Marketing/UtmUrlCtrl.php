<?php

namespace App\Http\Controllers\Cms\Marketing;

use App\Enums\Globals\AppEnvClass;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class UtmUrlCtrl extends Controller
{
    //測試用token去PicSee粉絲團申請，PicSee帳號查詢token，登入帳密在企劃部那邊
    const PICSEE_TOKEN_ARRAY = [
        AppEnvClass::Local => '20f07f91f3303b2f66ab6f61698d977d69b83d64',
        AppEnvClass::Development => '20f07f91f3303b2f66ab6f61698d977d69b83d64',
        AppEnvClass::Release => '32e5b4c2031799dc40841114844a3aa35343a923'
    ];

    public function index(Request $request)
    {
        if (App::environment(AppEnvClass::Release)){
            $picseeToken = self::PICSEE_TOKEN_ARRAY[AppEnvClass::Release];
        } else {
            $picseeToken = self::PICSEE_TOKEN_ARRAY[AppEnvClass::Development];
        }
        return view('cms.marketing.utm_url.index', [
            'PicSeeToken' => $picseeToken,
        ]);
    }
}
