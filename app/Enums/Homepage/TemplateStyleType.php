<?php

namespace App\Enums\Homepage;

use BenSampo\Enum\Enum;

class TemplateStyleType extends Enum
{
    const style_1 = '1';
    const style_2 = '2';
    const style_3 = '3';
    const style_4 = '4';

    public static function getDescription($value): string
    {
        $result = '';
        switch ($value) {
            case self::style_1:
                $result = '樣式一（左右滑動）';
                break;
            case self::style_2:
                $result = '樣式二（二維選單）';
                break;
            case self::style_3:
                $result = '樣式三（一維選單）';
                break;
            case self::style_4:
                $result = '樣式四（瀑布式）';
                break;
            default:
                $result = parent::getDescription($value);
                break;
        }
        return $result;
    }

    public static function getAsset($value): string
    {
        $result = '';
        switch ($value) {
            case self::style_1:
                $result = 'images/frontend/template_1.svg';
                break;
            case self::style_2:
                $result = 'images/frontend/template_2.svg';
                break;
            case self::style_3:
                $result = 'images/frontend/template_3.svg';
                break;
            case self::style_4:
                $result = 'images/frontend/template_4.svg';
                break;
            default:
                $result = '';
                break;
        }
        return $result;
    }
}
