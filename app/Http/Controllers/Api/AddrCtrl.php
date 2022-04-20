<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Addr;
use Illuminate\Http\Request;

class AddrCtrl extends Controller
{
    //
    public function getCitys(Request $request)
    {

        return [
            'status' => '0',
            'data' => Addr::getCitys(),
        ];
    }

    public function getRegions(Request $request, $id = 1)
    {
        $can_service = $request->query('can_service');

        return [
            'status' => '0',
            'datas' => Addr::getRegions($id, $can_service),
        ];
    }

    public function addrFormating($address = '')
    {
        return [
            'status' => '0',
            'data' => Addr::addrFormating($address),
        ];
    }

    public function checkFormat($address = '')
    {
        $re = Addr::addrFormating($address);

        if (!$re->city_id) {
            return [
                'status' => 'A01',
                'message' => '地址格式錯誤',
            ];
        }

        return [
            'status' => '0',
        ];
    }

}
