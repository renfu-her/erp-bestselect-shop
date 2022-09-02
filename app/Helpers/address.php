<?php


if (!function_exists('catchAddress')) {
    /**
     * @param  String  $addr 例: 新北市淡水區民權路1號1樓
     *
     * @return mixed matches[0] 縣市名稱 , matches[1] 地區名 ,  matches[3]剩下的地址名稱
     */
    function catchAddress(String $addr)
    {
        preg_match('/([\x{4e00}-\x{9fa5}]+[市縣])([\x{4e00}-\x{9fa5}]+[市區鄉鎮])(.*)/u', $addr, $matches);
        return $matches;
    }

}
