<?php

namespace App\Enums\Area;

use BenSampo\Enum\Enum;


final class Area extends Enum
{
    const Taipei = 'taipei';
    const Taoyuan = 'taoyuan';
    const Hsinchu = 'hsinchu';
    const Taichung = 'taichung';
    const Chiayi = 'chiayi';
    const Tainan = 'tainan';
    const Kaohsiung = 'kaohsiung';

    public static function getDescription($value): string
    {
        switch ($value) {
            case self::Taipei:
                return '台北';
            case self::Taoyuan:
                return '桃園';
            case self::Hsinchu:
                return '新竹';
            case self::Taichung:
                return '台中';
            case self::Chiayi:
                return '嘉義';
            case self::Tainan:
                return '台南';
            case self::Kaohsiung:
                return '高雄';
            default:
        }
    }


    public static function get_key_value()
    {
        $result = [];

        foreach (self::asArray() as $data) {
            $result[$data] = self::getDescription($data);
        }

        return $result;
    }
}
