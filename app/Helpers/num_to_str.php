<?php
    if (!function_exists('num_to_str')) {
        function num_to_str($amount)
        {
            $capitalNumbers = [
                '零', '壹', '貳', '參', '肆', '伍', '陸', '柒', '捌', '玖',
            ];

            $integerUnits = ['', '拾', '佰', '仟',];

            $placeUnits = ['', '萬', '億', '兆', '京'];

            $decimalUnits = ['角', '分', '厘', '毫',];

            $result = [];

            $arr = explode('.', $amount);

            $integer = trim($arr[0] ?? '', '-');
            $decimal = $arr[1] ?? '';

            if (!((int) $decimal)) {
                $decimal = '';
            }

            $integerNumbers = $integer ? array_reverse(str_split($integer)) : [];

            $last = null;
            foreach (array_chunk($integerNumbers, 4) as $chunkKey => $chunk) {
                if (!((int) implode('', $chunk))) {
                    continue;
                }

                array_unshift($result, $placeUnits[$chunkKey]);

                foreach ($chunk as $key => $number) {
                    if (!$number && (!$last || $key === 0)) {
                        $last = $number;
                        continue;
                    }
                    $last = $number;

                    if ($number) {
                        array_unshift($result, $integerUnits[$key]);
                    }

                    array_unshift($result, $capitalNumbers[$number]);
                }
            }

            if (!$result) {
                array_push($result, $capitalNumbers[0]);
            }

            array_push($result, '元');

            if (!$decimal) {
                array_push($result, '整');
            }

            $decimalNumbers = $decimal ? str_split($decimal) : [];
            foreach ($decimalNumbers as $key => $number) {
                array_push($result, $capitalNumbers[$number]);
                array_push($result, $decimalUnits[$key]);
            }

            if (strpos((string) $amount, '-') === 0) {
                array_unshift($result, '負');
            }

            // return '新台幣' . implode('', $result);
            return implode('', $result);
        }
    }