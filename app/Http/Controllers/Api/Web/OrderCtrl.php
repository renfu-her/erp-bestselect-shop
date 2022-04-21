<?php

namespace App\Http\Controllers\Api\Web;

use App\Enums\Globals\ResponseParam;
use App\Enums\Received\ReceivedMethod;
use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;

class OrderCtrl extends Controller
{
    //

    public function getGlobalDiscount(Request $request)
    {

        $dicount = Discount::getDiscounts('global-normal');

        $re = [];
        $re[ResponseParam::status()->key] = '0';
        $re[ResponseParam::msg()->key] = '';
        $re[ResponseParam::data()->key] = $dicount;
        return response()->json($re);

    }

    public function payinfo(Request $request)
    {

        $re = [];
        $re[ResponseParam::status()->key] = '0';
        $re[ResponseParam::msg()->key] = '';
        $re[ResponseParam::data()->key] = [
            ['id' => ReceivedMethod::Cash()->value,
                'name' => ReceivedMethod::Cash()->description],
            ['id' => ReceivedMethod::CreditCard()->value,
                'name' => ReceivedMethod::CreditCard()->description],
        ];
        return response()->json($re);

    }


    public function credit_card_checkout(Request $request)
    {
        include (app_path() . '/Helpers/auth_mpi_mac.php');

        // $EncRes = isset($_POST['URLResEnc']) ? $_POST['URLResEnc'] : null;
        $EncRes = request('URLResEnc') ? request('URLResEnc') : null;
        if($EncRes){
            $Key = 'LPCvSznVxZ4CFjnWbtg4mUWo';
            $debug = '0';
            $EncArray = gendecrypt($EncRes, $Key, $debug);

            if(is_array($EncArray) && count($EncArray) > 0){
                foreach($EncArray AS $key_name => $value){
                    echo $key_name . " => " . mb_convert_encoding(trim($value, "\x00..\x08"), 'UTF-8', ['BIG5', 'UTF-8']) ."<br>\n";
                    // echo $key_name . " => " . urlencode(trim($value, "\x00..\x08")) ."<br>\n";
                }

                $MACString = '';
                $status = isset($EncArray['status']) ? $EncArray['status'] : "";
                $errCode = isset($EncArray['errcode']) ? $EncArray['errcode'] : "";
                $authCode = isset($EncArray['authcode']) ? $EncArray['authcode'] : "";
                $authAmt = isset($EncArray['authamt']) ? $EncArray['authamt'] : "";
                $lidm = isset($EncArray['lidm']) ? $EncArray['lidm'] : "";
                $OffsetAmt = isset($EncArray['offsetamt']) ? $EncArray['offsetamt'] : "";
                $OriginalAmt = isset($EncArray['originalamt']) ? $EncArray['originalamt'] : "";
                $UtilizedPoint = isset($EncArray['utilizedpoint']) ? $EncArray['utilizedpoint'] : "";
                $Option = isset($EncArray['numberofpay']) ? $EncArray['numberofpay'] : "";
                //紅利交易時請帶入prodcode
                //$Option = isset($EncArray['prodcode']) ? $EncArray['prodcode'] : "";
                $Last4digitPAN = isset($EncArray['last4digitpan']) ? $EncArray['last4digitpan'] : "";

                $MACString = auth_out_mac($status, $errCode, $authCode, $authAmt, $lidm, $OffsetAmt, $OriginalAmt, $UtilizedPoint, $Option, $Last4digitPAN, $Key, $debug);
                echo "產生伺服器所回傳的資料壓碼(MACString)==> $MACString\n" . '<br>';
                // if ($MACString == $EncArray['outmac']){
                //     // then the result is right!
                // }

                $pidResult= isset($EncArray['pidresult']) ? $EncArray['pidresult'] : "";
                $CardNumber = isset($EncArray['cardnumber']) ? $EncArray['cardnumber'] : "";
                $CardNo = isset($EncArray['cardno']) ? $EncArray['cardno'] : "";
                $EInvoice = isset($EncArray['einvoice']) ? $EncArray['einvoice'] : "";

                if(empty($status) && $status == '0'){
                    echo '交易完成';
                    echo '<br>';
                    echo '<a href="' . route('cms.order.index') . '">回到訂單管理</a>';
                    die();
                    // return redirect()->back();
                } else {
                    echo '交易失敗';
                    echo '<br>';
                    echo '<a href="' . route('cms.order.index') . '">回到訂單管理</a>';
                    // return redirect()->back();
                }
            }
        }

        echo '交易失敗';
        echo '<br>';
        echo '<a href="' . route('cms.order.index') . '">回到訂單管理</a>';
        // return redirect()->back();
    }
}
