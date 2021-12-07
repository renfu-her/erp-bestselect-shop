<?php


if (!function_exists('catchAddress')) {
    function catchAddress(String $addr)
    {
        preg_match('/([\x{4e00}-\x{9fa5}]+[市縣])([\x{4e00}-\x{9fa5}]+[區鄉鎮])(.*)/u', $addr, $matches);
        return $matches;
    }

}
