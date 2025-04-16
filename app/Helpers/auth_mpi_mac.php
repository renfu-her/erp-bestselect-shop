<?php
if(env('APP_ENV') == 'local' || env('APP_ENV') == 'dev'){
    $str_mer_id = '77725';
    $str_merchant_id = '8220300000043';
    $str_terminal_id = '90300043';

    $str_url = 'https://testepos.ctbcbank.com/mauth/SSLAuthUI.jsp';

    $auth_key = 'LPCvSznVxZ4CFjnWbtg4mUWo';

} else {
    // formal env
    // if($card_installment > 0){
    //     $str_mer_id = '80492';
    //     $str_merchant_id = '8220158811357';
    //     $str_terminal_id = '99720375';

    //     $str_url = 'https://epos.ctbcbank.com/mauth/SSLAuthUI.jsp';

    //     $auth_key = 'v0jSzd1eY64mSzYISy8wSosT';
    // } else {

    // }

    $str_mer_id = '80491';
    $str_merchant_id = '8220130031554';
    $str_terminal_id = '99878570';

    $str_url = 'https://epos.ctbcbank.com/mauth/SSLAuthUI.jsp';

    $auth_key = 'IhkmFPa9EFBM3L7jSHbrPSOP';
}

if (!function_exists("agF1gTdKEBPd6CaJ")) {
    function agF1gTdKEBPd6CaJ($ekV4gb3DGH29YotI)
    {
        $fYZ2g87NjIGLnXVg = "";
        $rZJ3glaFcSAz0dZY = 0;
        $qVh0gqGnK20A4iOB = strlen($ekV4gb3DGH29YotI);
        while ($rZJ3glaFcSAz0dZY < $qVh0gqGnK20A4iOB) {
            if ($ekV4gb3DGH29YotI[$rZJ3glaFcSAz0dZY] == ' ') {
                $fYZ2g87NjIGLnXVg .= " ";
            } else if ($ekV4gb3DGH29YotI[$rZJ3glaFcSAz0dZY] == '!') {
                $fYZ2g87NjIGLnXVg .= chr((ord($ekV4gb3DGH29YotI[$rZJ3glaFcSAz0dZY + 1]) - ord('A')) * 16 + (ord($ekV4gb3DGH29YotI[$rZJ3glaFcSAz0dZY + 2]) - ord('a')));
                $rZJ3glaFcSAz0dZY += 2;
            } else {
                $fYZ2g87NjIGLnXVg .= chr(ord($ekV4gb3DGH29YotI[$rZJ3glaFcSAz0dZY]) + 1);
            }
            $rZJ3glaFcSAz0dZY++;
        }
        return $fYZ2g87NjIGLnXVg;
    }
}


function auth_in_mac($MerchantID, $TerminalID, $lidm, $purchAmt, $txType, $Option, $Key, $MerchantName, $AuthResURL, $OrderDetail, $AutoCap, $Customize, $debug) {
    $CombineStr = "|" . $MerchantID . "|" . $TerminalID . "|" . $lidm . "|" . $purchAmt . "|" . $txType . "|" . $Option . "|";
    $ParameterArray = array($MerchantID, $TerminalID, $lidm, $purchAmt, $txType, $Option, $Key, $MerchantName, $AuthResURL, $OrderDetail, $AutoCap, $Customize);
    if ($debug == 1) {
        echo "\144\x65bug=$$debug \n";
        echo "C\x6fm\142\151\156\145S\164\x72 \x69\163 : $CombineStr \n";
        foreach ($ParameterArray as $key => $value) {
            echo "$key i\163 $value\n";
        }
    }
    $CMP = checkAuthInMacParameter($ParameterArray);
    if ($CMP == "000") {
        $MACString = DESMAC($CombineStr, $Key, $debug);
        $MACString = substr($MACString, -48, 48);
        return $MACString;
    } else {
        return "0x" . dechex($CMP);
    }
}
function get_auth_urlenc($MerchantID, $TerminalID, $lidm, $purchAmt, $txType, $Option, $Key, $MerchantName, $AuthResURL, $OrderDetail, $AutoCap, $Customize, $InMac, $debug) {
    if ($txType == "2") {
        $ProdCode = $Option;
        $NumberOfPay = "";
    } else {
        $ProdCode = "";
        $NumberOfPay = $Option;
    }
    $encStr = "\115\145\x72c\x68\x61\156t\111D=" . $MerchantID . "&" . "T\145\x72m\x69na\x6c\111\104=" . $TerminalID . "&" . "\154\x69\x64\x6d=" . $lidm . "&" . "\160\165\x72\x63h\x41\x6dt=" . $purchAmt . "&" . "\164\x78T\171\160\145=" . $txType . "&" . "M\145\x72\143h\x61nt\x4e\141\155e=" . $MerchantName . "&" . "\x41u\164\150R\145s\x55\122\114=" . $AuthResURL . "&" . "Or\144erD\x65\164\141i\154=" . $OrderDetail . "&" . "\x50\162o\144Co\144\x65=" . $ProdCode . "&" . "A\x75\164\x6f\x43\x61\160=" . $AutoCap . "&" . "\143\x75s\164\157mi\x7ae=" . $Customize . "&" . "N\x75m\x62\145r\117f\120a\x79=" . $NumberOfPay . "&" . "In\115\x61\143=" . $InMac;
    $URLEnc = DESMAC($encStr, $Key, $debug);
    return $URLEnc;
}
function checkAuthInMacParameter($ParameterArray) {
    if ($ParameterArray[0] == NULL || !is_numeric($ParameterArray[0]) || strlen($ParameterArray[0]) != 13) return '285212673';
    if ($ParameterArray[1] == NULL || !is_numeric($ParameterArray[1]) || strlen($ParameterArray[1]) != 8) return '285212674';
    if ($ParameterArray[2] == NULL || strlen($ParameterArray[2]) < 1 || strlen($ParameterArray[2]) > 19 || (!preg_match('/^[a-zA-Z0-9_]+$/', $ParameterArray[2]))) return '285212675';
    if ($ParameterArray[3] == NULL || !is_numeric($ParameterArray[3]) || strlen($ParameterArray[3]) < 1) return '285212676';
    if ($ParameterArray[4] == NULL || !is_numeric($ParameterArray[4]) || strlen($ParameterArray[4]) != 1) return '285212677';
    if ($ParameterArray[5] == NULL && ($ParameterArray[4] == '0' || $ParameterArray[4] == '1' || $ParameterArray[4] == '2' || $ParameterArray[4] == '6' || $ParameterArray[4] == '9'));
    elseif ($ParameterArray[5] == NULL || !is_numeric($ParameterArray[5])) return '285212679';
    if ($ParameterArray[4] == '4') {
        if (strlen($ParameterArray[5]) < 3 || strlen($ParameterArray[5]) > 4) {
            return '285212679';
        }
    } else {
        if (strlen($ParameterArray[5]) != 0 && strlen($ParameterArray[5]) > 2) return '285212679';
    }
    if ($ParameterArray[6] == NULL || strlen($ParameterArray[6]) != 24) return '285212697';
    else return "000";
}
function auth_out_mac($status, $errCode, $authCode, $authAmt, $lidm, $OffsetAmt, $OriginalAmt, $UtilizedPoint, $Option, $Last4digitPAN, $Key, $debug) {
    $CombineStr = "|" . $status . "|" . $errCode . "|" . $authCode . "|" . $authAmt . "|" . $lidm . "|" . $OffsetAmt . "|" . $OriginalAmt . "|" . $UtilizedPoint . "|" . $Option . "|" . $Last4digitPAN . "|";
    $ParameterArray = array($status, $errCode, $authCode, $authAmt, $lidm, $OffsetAmt, $OriginalAmt, $UtilizedPoint, $Option, $Last4digitPAN, $Key);
    if ($debug == 1) {
        echo "\x64\145b\x75\147=$$debug \n";
        echo "\x43om\x62\151\x6ee\123\164r \x69\x73 : $CombineStr \n";
        foreach ($ParameterArray as $key => $value) {
            echo "$key \151s $value\n";
        }
    }
    $CMP = checkAuthOutMacParameter($ParameterArray);
    if ($CMP == "000") {
        $MACString = DESMAC($CombineStr, $Key, $debug);
        $MACString = substr($MACString, -48, 48);
        return $MACString;
    } else {
        return "0x" . dechex($CMP);
    }
}
function checkAuthOutMacParameter($ParameterArray) {
    if ($ParameterArray[0] == NULL || !is_numeric($ParameterArray[0]) || strlen($ParameterArray[0]) < 0 || strlen($ParameterArray[0]) > 2) return '285212680';
    if ($ParameterArray[1] == NULL || strlen($ParameterArray[1]) < 2 || strlen($ParameterArray[1]) > 4) return '285212681';
    if (strlen($ParameterArray[2]) < 0 || strlen($ParameterArray[2]) > 7) return '285212682';
    if ($ParameterArray[3] == NULL || !is_numeric($ParameterArray[3]) || strlen($ParameterArray[3]) < 1 || strlen($ParameterArray[3]) > 7) return '285212683';
    if ($ParameterArray[4] == NULL || strlen($ParameterArray[4]) < 1 || strlen($ParameterArray[4]) > 19 || (!preg_match('/^[a-zA-Z0-9_]+$/', $ParameterArray[4]))) return '285212675';
    if ($ParameterArray[5] != NULL && (!is_numeric($ParameterArray[5]) || strlen($ParameterArray[5]) > 7)) return '285212684';
    if ($ParameterArray[6] != NULL && (!is_numeric($ParameterArray[6]) || strlen($ParameterArray[6]) > 7)) return '285212685';
    if ($ParameterArray[7] != NULL && (!is_numeric($ParameterArray[7]) || strlen($ParameterArray[7]) > 7)) return '285212686';
    if (strlen($ParameterArray[8]) != 0 && strlen($ParameterArray[8]) > 4) return '285212679';
    if (!is_numeric($ParameterArray[8]) && $ParameterArray[8] != NULL) return '285212679';
    if (strlen($ParameterArray[9]) != 0 && strlen($ParameterArray[9]) != 4) return '285212687';
    if (!is_numeric($ParameterArray[9]) && $ParameterArray[9] != NULL) return '285212687';
    if ($ParameterArray[10] == NULL || strlen($ParameterArray[10]) != 24) return '285212697';
    else return "000";
}
function checkDecryptParameter($ParameterArray) {
    if ($ParameterArray[0] == NULL || $ParameterArray[0] % 8 != 0) return '285212701';
    if ($ParameterArray[1] == NULL || strlen($ParameterArray[1]) != 24) return '285212697';
    else return "000";
}
function mpiauth_in_mac($MerchantID, $TerminalID, $AcquireBIN, $CardNo, $ExpYear, $ExpMonth, $authAmt, $lidm, $Key, $RetURL, $debug) {
    $CombineStr = "|" . $MerchantID . "|" . $AcquireBIN . "|" . $CardNo . "|" . $ExpYear . "|" . $ExpMonth . "|" . $authAmt . "|" . $lidm . "|";
    $ParameterArray = array($MerchantID, $TerminalID, $AcquireBIN, $CardNo, $ExpYear, $ExpMonth, $authAmt, $lidm, $Key, $RetURL);
    if ($debug == 1) {
        echo "\x64e\142\x75\147=$$debug \n";
        echo "\x43om\142\151\156\145Str is : $CombineStr \n";
        foreach ($ParameterArray as $var => $val) {
            echo "$var \151\x73 $val\n";
        }
    }
    $CMP = checkMPIinMacParameter($ParameterArray);
    if ($CMP == "000") {
        $MACString = DESMAC($CombineStr, $Key, $debug);
        $MACString = substr($MACString, -48, 48);
        return $MACString;
    } else {
        return "0x" . dechex($CMP);
    }
}
function get_mpi_urlenc($MerchantID, $TerminalID, $AcquireBIN, $CardNo, $ExpYear, $ExpMonth, $authAmt, $lidm, $Key, $RetURL, $InMac, $debug) {
    $encStr = "me\x72cha\156\x74\x49\104=" . $MerchantID . "&" . "t\145\162m\x69nal\x49\104=" . $TerminalID . "&" . "a\143q\165\x69re\162\x42IN=" . $AcquireBIN . "&" . "c\141\x72d\116\165\155\142\x65\x72=" . $CardNo . "&" . "\x65\x78\x70\131\145\x61r=" . $ExpYear . "&" . "exp\115\x6f\156t\x68=" . $ExpMonth . "&" . "\x74o\x74\141\x6c\101m\157\x75\x6e\x74=" . $authAmt . "&" . "\x58\111\x44=" . $lidm . "&" . "\122\x65t\x55r\x6c=" . $RetURL . "&" . "\111\x6eM\x61c=" . $InMac;
    $URLEnc = DESMAC($encStr, $Key, $debug);
    return $URLEnc;
}
function checkMPIinMacParameter($ParameterArray) {
    if ($ParameterArray[0] == NULL || !is_numeric($ParameterArray[0]) || strlen($ParameterArray[0]) < 4 || strlen($ParameterArray[0]) > 15) return '285212673';
    if ($ParameterArray[1] == NULL || !is_numeric($ParameterArray[1]) || strlen($ParameterArray[1]) != 8) return '285212674';
    if (strlen($ParameterArray[2]) != 0 && strlen($ParameterArray[2]) != 6) return '285212688';
    if ($ParameterArray[2] != NULL && !is_numeric($ParameterArray[2])) return '285212688';
    if (strlen($ParameterArray[3]) != 16) return '285212689';
    if (strlen($ParameterArray[4]) != 4) return '285212690';
    if (strlen($ParameterArray[5]) != 2) return '285212691';
    if ($ParameterArray[6] == NULL || !is_numeric($ParameterArray[6]) || $ParameterArray[6] > 9999999999) return '285212683';
    if ($ParameterArray[7] == NULL || strlen($ParameterArray[7]) < 1 || strlen($ParameterArray[7]) > 20 || (!preg_match('/^[a-zA-Z0-9_]+$/', $ParameterArray[7]))) return '285212675';
    if ($ParameterArray[8] == NULL || strlen($ParameterArray[8]) != 24) return '285212697';
    else return "000";
}
function mpiauth_out_mac($CardNo, $ExpDate, $lidm, $ECI, $CAVV, $errCode, $Key, $debug) {
    $CombineStr = "|" . $CardNo . "|" . $ExpDate . "|" . $lidm . "|" . $ECI . "|" . $CAVV . "|" . $errCode . "|";
    $ParameterArray = array($CardNo, $ExpDate, $lidm, $ECI, $CAVV, $errCode, $Key);
    if ($debug == 1) {
        echo "de\142\x75\x67=$$debug \n";
        echo "\x43\157m\x62\x69\156e\x53tr i\163 : $CombineStr \n";
        foreach ($ParameterArray as $var => $val) {
            echo "$var \151s $val\n";
        }
    }
    $CMP = checkMPIoutMacParameter($ParameterArray);
    if ($CMP == "000") {
        $MACString = DESMAC($CombineStr, $Key, $debug);
        $MACString = substr($MACString, -48, 48);
        return $MACString;
    } else {
        return "0x" . dechex($CMP);
    }
}
function checkMPIoutMacParameter($ParameterArray) {
    if (strlen($ParameterArray[0]) != 16) return '285212689';
    if (strlen($ParameterArray[1]) != 6) return '285212694';
    if ($ParameterArray[2] == NULL || strlen($ParameterArray[2]) < 1 || strlen($ParameterArray[2]) > 20 || (!preg_match('/^[a-zA-Z0-9_]+$/', $ParameterArray[2]))) return '285212675';
    if (strlen($ParameterArray[3]) != 1 || !is_numeric($ParameterArray[3])) return '285212695';
    if ($ParameterArray[5] == NULL || strlen($ParameterArray[5]) < 1 || strlen($ParameterArray[5]) > 4) return '285212681';
    if ($ParameterArray[6] == NULL || strlen($ParameterArray[6]) != 24) return '285212697';
    else return "000";
}
function checkMPIDecryptMacParameter($ParameterArray) {
    if ($ParameterArray[0] == NULL || $ParameterArray[0] % 8 != 0) return '285212701';
    if ($ParameterArray[1] == NULL || strlen($ParameterArray[1]) != 24) return '285212697';
    else return "000";
}
function DESMAC($msg, $key, $debug) {
    $block_size = 8;
    $padlen = $block_size - (strlen($msg) % $block_size);
    for ($i = 0;$i < $padlen;$i++) $msg.= chr($padlen);
    if ($debug == 1) {
        echo "\104\x45\123\x4dAC:\x6be\171=$key\n";
        echo "\x44E\x53M\101\103:\155s\x67=$msg\n";
    }
    $iv = "\x68\171w\x65bp\x675";
    if ($key == null) return '285212698';
    $key = substr($key, 0, 24);
    $cipherText = opensslEncrypt($msg, $key, $iv);
    return strtoupper(bin2hex($cipherText));
}
function opensslEncrypt($plain_text, $key, $iv) {
    $method = 'DES-EDE3-CBC';
    return openssl_encrypt($plain_text, $method, $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);
}
function opensslDecrypt($cipher_text, $key, $iv) {
    $method = 'DES-EDE3-CBC';
    $plain_text = openssl_decrypt($cipher_text, $method, $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);
    return $plain_text;
}
function pkcs5Unpad($text) {
    $pad = ord($text{strlen($text) - 1});
    if ($pad > strlen($text)) return false;
    if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
    return substr($text, 0, -1 * $pad);
}
function hexToStr($hex) {
    echo 'hexlen =' . strlen($hex) . PHP_EOL;
    $string = "";
    for ($i = 0;$i < strlen($hex) - 1;$i+= 2) {
        $string.= chr(hexdec($hex[$i] . $hex[$i + 1]));
    }
    return $string;
}
function utf8_2_big5($utf8_str) {
    $i = 0;
    $len = strlen($utf8_str);
    $big5_str = "";
    for ($i = 0;$i < $len;$i++) {
        $sbit = ord(substr($utf8_str, $i, 1));
        if ($sbit < 128) {
            $big5_str.= substr($utf8_str, $i, 1);
        } else if ($sbit > 191 && $sbit < 224) {
            $new_word = iconv("U\124F-8", "\x42i\x675", substr($utf8_str, $i, 2));
            $big5_str.= ($new_word == "") ? "��" : $new_word;
            $i++;
        } else if ($sbit > 223 && $sbit < 240) {
            $new_word = iconv("\125\x54\x46-8", "Bi\147\x35", substr($utf8_str, $i, 3));
            $big5_str.= ($new_word == "") ? "��" : $new_word;
            $i+= 2;
        } else if ($sbit > 239 && $sbit < 248) {
            $new_word = iconv("\125\124F-8", "Bi\x675", substr($utf8_str, $i, 4));
            $big5_str.= ($new_word == "") ? "��" : $new_word;
            $i+= 3;
        }
    }
    return $big5_str;
}
if (!function_exists("he\x782b\151n")) {
    function hex2bin($hex) {
        $len = strlen($hex);
        return pack("H" . $len, $hex);
    }
}
function pairstr2Arr($str, $separator, $delim) {
    $elems = explode($delim, $str);
    foreach ($elems as $elem => $val) {
        $val = trim($val);
        $len = strlen($val);
        $point = strpos($val, $separator, 0);
        if ($point > 0) {
            $nameVal[0] = substr($val, 0, $point);
            $res = $len - ($point + 1);
            if ($res > 0) {
                $nameVal[1] = substr($val, $point + 1, $len - $point);
            } else {
                $nameVal[1] = "";
            }
            $arr[trim(strtolower($nameVal[0])) ] = trim($nameVal[1]);
        } else {
            $arr = "";
        }
    }
    return $arr;
}
function genencrypt($encStr, $Key, $debug) {
    $URLEnc = DESMAC($encStr, $Key, $debug);
    return $URLEnc;
}
function gendecrypt($EncRes, $Key, $debug) {
    $ParameterArray = array($EncRes, $Key);
    $CombineStr = "|" . $EncRes . "|" . $Key . "|";
    if ($debug == 1) {
        echo "\x64ebu\x67=$$debug \n";
        echo "\x43\157m\x62i\x6ee\x53\x74r \x69\163 : $CombineStr \n";
        foreach ($ParameterArray as $key => $value) {
            echo "$key \151s $value\n";
        }
    }
    $CMP = "000";
    if ($CMP == "000") {
        $iv = "\x68\171\x77\145\x62p\x675";
        $DesText = opensslDecrypt(hex2bin($EncRes), $Key, $iv);
        $DesText = trim($DesText, "\x00..\x08");
        $ParseArray = pairstr2Arr($DesText, "=", "&");
        return $ParseArray;
    } else {
        return "0x" . dechex($CMP);
    }
}
function genmpidecrypt($EncRes, $Key, $debug) {
    $ParameterArray = array($EncRes, $Key);
    $CombineStr = "|" . $EncRes . "|" . $Key . "|";
    if ($debug == 1) {
        echo "\x64\145\x62\165g=$$debug \n";
        echo "C\x6fmb\x69\156eS\x74\x72 i\x73 : $CombineStr \n";
        foreach ($ParameterArray as $var => $val) {
            echo "$var i\x73 $val\n";
        }
    }
    $CMP = "000";
    if ($CMP == "000") {
        $iv = "\x68\171\x77\145\x62p\x675"; // 初始化向量
        $cipher = 'DES-EDE3-CBC'; // 使用的加密方法

        // 使用 openssl_decrypt 解密
        $DesText = openssl_decrypt(hex2bin($EncRes), $cipher, $Key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $iv);
        $DesText = trim($DesText, "\x00..\x08"); // 去除填充
        $ParseArray = pairstr2Arr($DesText, "=", "&");
        return $ParseArray;
    } else {
        return "0x" . dechex($CMP);
    }
}
function get_auth_atmurlenc($MerchantID, $TerminalID, $lidm, $purchAmt, $txType, $Option, $Key, $storeName, $AuthResURL, $billShortDesc, $WebATMAcct, $note, $InMac, $debug) {
    $encStr = "M\145\x72\x63\x68\x61nt\111\104=" . $MerchantID . "&" . "\x54\x65r\155in\x61\x6cI\104=" . $TerminalID . "&" . "\154\x69\144\x6d=" . $lidm . "&" . "\x70\165\x72\x63\x68\101m\164=" . $purchAmt . "&" . "\x74\170\124\x79\160e=" . $txType . "&" . "I\156\115ac=" . $InMac . "&" . "\x41u\x74\x68\122es\125R\x4c=" . $AuthResURL . "&" . "\x57ebA\124MA\x63c\164=" . $WebATMAcct . "&" . "\142i\154\x6c\x53\150\157rtD\145s\x63=" . $billShortDesc . "&" . "\x6e\x6f\164\x65=" . $note . "&" . "s\164\x6f\162\x65N\141m\145=" . $storeName;
    $URLEnc = DESMAC($encStr, $Key, $debug);
    return $URLEnc;
}
function get_auth_dbcurlenc($MerchantID, $TerminalID, $lidm, $purchAmt, $txType, $Option, $Key, $storeName, $AuthResURL, $billShortDesc, $note, $InMac, $debug) {
    $encStr = "\115\x65\162cha\x6e\164I\104=" . $MerchantID . "&" . "\x54e\x72min\x61\154\x49D=" . $TerminalID . "&" . "l\151\x64m=" . $lidm . "&" . "\160\x75\x72\143\150\101\x6d\164=" . $purchAmt . "&" . "tx\124\x79\x70\145=" . $txType . "&" . "\x49\156\115a\143=" . $InMac . "&" . "A\165t\x68\122e\163U\x52\x4c=" . $AuthResURL . "&" . "\142\151\x6c\154\123\x68\x6f\x72\x74\104\145\x73c=" . $billShortDesc . "&" . "no\x74\145=" . $note . "&" . "\x73\164\157r\145N\141\x6de=" . $storeName;
    $URLEnc = DESMAC($encStr, $Key, $debug);
    return $URLEnc;
};
