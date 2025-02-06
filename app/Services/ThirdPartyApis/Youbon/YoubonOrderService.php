<?php

namespace App\Services\ThirdPartyApis\Youbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use SimpleXMLElement;
use App\Models\TikYoubonApiLog;
use App\Enums\Globals\ResponseParam;
use App\Enums\eTicket\YoubonErrorCode;
use App\Enums\Globals\ApiStatusMessage;

class YoubonOrderService
{
    private const API_URL = 'https://b2b.youbon.com/api/orders.php';

    private $departId = "5001"; // 部門代碼
    private $userid = "627"; // API 交易帳號
    private $username = "張若心"; // API 交易帳號
    private $listnumbertype = "1"; // 商品清單編號

    private $code = "5001star"; // API 交易密碼

    private const SELE_TYPE = [
        'B2C' => 'B2C',
        'B2B2C' => 'B2B2C'
    ];

    // 付款方式列舉
    private const PAYMENT_TYPE = [
        'B2C' => [
            'CASH' => '001',     // 現金
            'CREDIT' => '002',   // 刷卡
            'OTHER' => '003',    // 其他
            'TRANSFER' => '004', // 匯款
            'CHECK' => '005'     // 支票
        ],
        'B2B2C' => [
            'OTHER' => '003' // 其他
        ]
    ];

    /**
     * 送出訂單
     *
     * @param array $orderData 訂單資料
     * @return array 處理結果
     */
    public function sendOrder($delivery_id, array $orderData): array
    {
        $xmlData = $this->generateOrderXml($orderData);
        Log::channel('daily')->info('xmlData:', [ 'xmlData' => $xmlData ]);

        $encodedXmlData = urlencode($xmlData);

        $hash = md5($encodedXmlData . $this->code);

        $departid = urlencode($this->departId);

        $requestData = [
            'as_xmldata' => $encodedXmlData,
            'as_hash' => $hash,
            'departid' => $departid
        ];

        $response = Http::asForm()->post(self::API_URL, $requestData);

        $responseBody = $response->body();

        Log::channel('daily')->info('requestData:', [ 'API_URL' => self::API_URL, 'xmlData' => $xmlData, 'request' => $requestData, 'response' => $responseBody ]);
        TikYoubonApiLog::createData($delivery_id, json_encode($requestData), json_encode($responseBody));

        // 解析回應
        return $this->parseResponse($responseBody);
    }

    public function validInputValue(array $data)
    {
        $validator = Validator::make($data, [
            'custbillno' => 'required|string|max:20',
            'fullname' => 'required|string|max:50',
            'telephone' => 'required|string|max:20',
            'email' => 'required|email|max:50',
            'items' => 'required|array',
            'items.*.productnumber' => 'required|string|max:20',
            'items.*.quantity' => 'required|string|max:8',
            'items.*.price' => 'required|string|max:10',
        ]);
        return $validator;
    }

    /**
     * 產生訂單XML
     *
     * @param array $data 訂單資料
     * @return string XML字串
     */
    private function generateOrderXml(array $data): string
    {
        $xml = new SimpleXMLElement('<saleorder/>');
        // 加入基本資料
        $xml->addChild('departid', $this->departId);
        $xml->addChild('userid', $this->userid);
        $xml->addChild('username', $this->username);
        $xml->addChild('saletype', self::SELE_TYPE['B2B2C']);

        // $xml->addChild('departid', $data['departid']);
        // $xml->addChild('userid', $data['userid']);
        // $xml->addChild('username', $data['username']);
        // $xml->addChild('saletype', $data['saletype']);

        $xml->addChild('custbillno', $data['custbillno']);
        $xml->addChild('fullname', $data['fullname']);
        $xml->addChild('telephone', $data['telephone']);
        $xml->addChild('email', $data['email']);

        $xml->addChild('paymenttype', self::PAYMENT_TYPE['B2B2C']['OTHER']);
        $xml->addChild('listnumbertype', $this->listnumbertype);

        // 加入商品資料
        $items = $xml->addChild('items');
        foreach ($data['items'] as $item) {
            $itemNode = $items->addChild('item');
            $itemNode->addChild('productnumber', $item['productnumber']);
            $itemNode->addChild('quantity', $item['quantity']);
            $itemNode->addChild('price', $item['price']);
        }

        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->encoding = 'UTF-8';
        $xmlString = $dom->saveXML($dom->documentElement);
        return $xmlString;
    }

    /**
     * 解析API回應
     *
     * @param string $response XML回應內容
     * @return array 解析結果
     */
    public function parseResponse(string $response): array
    {
        $xml = simplexml_load_string(urldecode($response));
        $result = [];

        // 基本資料
        $result['statcode'] = (string)$xml->statcode;
        $result['statdesc'] = (string)$xml->statdesc;

        // 如果成功才解析其他資料
        if ($result['statcode'] === YoubonErrorCode::SUCCESS) {
            $result['billno'] = (string)$xml->billno;
            $result['borrowno'] = (string)$xml->borrowno;
            $result['billdate'] = (string)$xml->billdate;
            $result['weburl'] = (string)$xml->weburl;

            // 解析商品資料
            $result['items'] = [];
            if (isset($xml->item)) {
                foreach ($xml->item as $item) {
                    $result['items'][] = $this->parseItemByListNumberType($item);
                }
            }
        } else if($result['statcode'] === YoubonErrorCode::ORDER_DUPLICATE) {
            $result['billno'] = (string)$xml->billno;
            $result['borrowno'] = (string)$xml->borrowno;
            $result['billdate'] = (string)$xml->billdate;
            $result['weburl'] = (string)$xml->weburl;

            // 解析商品資料
            $result['items'] = [];
            if (isset($xml->item)) {
                foreach ($xml->item as $item) {
                    $result['items'][] = $this->parseItemByListNumberType($item);
                }
            }
        } else {
            // 處理錯誤情況
            $result['error'] = $this->getErrorMessage($result['statcode']);

            // 額外錯誤資訊
            if(isset($xml->departid)) {
                $result['departid'] = (string)$xml->departid;
            }
            if(isset($xml->userid)) {
                $result['userid'] = (string)$xml->userid;
            }
            if(isset($xml->username)) {
                $result['username'] = (string)$xml->username;
            }
            if(isset($xml->saletype)) {
                $result['saletype'] = (string)$xml->saletype;
            }
            if(isset($xml->custbillno)) {
                $result['custbillno'] = (string)$xml->custbillno;
            }
            if(isset($xml->paymenttype)) {
                $result['paymenttype'] = (string)$xml->paymenttype;
            }
            if(isset($xml->statdesc)) {
                $result['statdesc'] = (string)$xml->statdesc;
            }
        }

        return $result;
    }

    /**
     * 根據listnumbertype解析商品資料
     */
    private function parseItemByListNumberType(\SimpleXMLElement $item): array
    {
        $result = [];

        // 基本欄位
        if (isset($item->productnumber)) {
            $result['productnumber'] = (string)$item->productnumber;
        }

        // 根據不同類型解析不同欄位
        switch ($this->listnumbertype) {
            case '1':
                $result['prodid'] = (string)$item->prodid;
                $result['batchid'] = (string)$item->batchid;
                $result['ordernumber'] = (string)$item->ordernumber;
                $result['price'] = (string)$item->price;
                break;

            case '2':
                $result['prodid'] = (string)$item->prodid;
                $result['batchid'] = (string)$item->batchid;
                $result['ordernumber'] = (string)$item->ordernumber;
                $result['price'] = (string)$item->price;
                break;

            case '3':
                $result['trustnumber'] = (string)$item->trustnumber;
                $result['ordernumber'] = (string)$item->ordernumber;
                $result['ticketstardate'] = (string)$item->ticketstardate;
                $result['ticketctdate'] = (string)$item->ticketctdate;
                $result['ticketoverdate'] = (string)$item->ticketoverdate;
                break;

            case '4':
                $result['prodid'] = (string)$item->prodid;
                $result['batchid'] = (string)$item->batchid;
                $result['ordernumber'] = (string)$item->ordernumber;
                $result['images'] = (string)$item->images;
                break;
        }

        return $result;
    }

    /**
     * 取得錯誤訊息
     *
     * @param string $code 錯誤代碼
     * @return string 錯誤訊息
     */
    private function getErrorMessage(string $code): string
    {
        return YoubonErrorCode::getDescription($code);
    }

    /**
     * 處理訂單
     *
     * @param int $delivery_id 出貨單號
     * @param array $orderData 訂單資料
     * @return array 處理結果
     */
    public function processOrder($delivery_id, array $orderData): array
    {
        // 資料驗證
        $validator = $this->validInputValue($orderData);
        if ($validator->fails()) {
            return [
                ResponseParam::status => ApiStatusMessage::Fail,
                ResponseParam::msg => implode('；', $validator->errors()->all()),
                ResponseParam::data   => [],
            ];
        }
        return [
            ResponseParam::status => ApiStatusMessage::Fail(),
            ResponseParam::msg    => '測試',
            ResponseParam::data   => [],
        ];

        try {
            // 送出訂單
            $result = $this->sendOrder($delivery_id, $orderData);

            // 檢查結果
            if ($result['statcode'] === YoubonErrorCode::SUCCESS) {
                return [
                    ResponseParam::status => ApiStatusMessage::Succeed,
                    ResponseParam::msg    => YoubonErrorCode::getDescription($result['statcode']),
                    ResponseParam::data   => ['billno' => $result['billno'], 'result' => $result],
                ];
            } elseif ($result['statcode'] === YoubonErrorCode::ORDER_DUPLICATE) {
                return [
                    ResponseParam::status => ApiStatusMessage::Succeed,
                    ResponseParam::msg    => YoubonErrorCode::getDescription($result['statcode']),
                    ResponseParam::data   => ['billno' => $result['billno'], 'result' => $result],
                ];
            } else {
                $errMsg = '錯誤：' . $result['error'] .
                    (isset($result['statcode']) ? ' ' . $result['statcode'] : '') .
                    (isset($result['statdesc']) ? ' ' . $result['statdesc'] : '');
                return [
                    ResponseParam::status => ApiStatusMessage::Fail,
                    ResponseParam::msg    => $errMsg,
                    ResponseParam::data   => $result,
                ];
            }
        } catch (\InvalidArgumentException $e) {
            return [
                ResponseParam::status => ApiStatusMessage::Fail,
                ResponseParam::msg    => '資料驗證錯誤：' . $e->getMessage(),
                ResponseParam::data   => [],
            ];
        } catch (\Exception $e) {
            return [
                ResponseParam::status => ApiStatusMessage::Fail,
                ResponseParam::msg    => '系統錯誤：' . $e->getMessage(),
                ResponseParam::data   => [],
            ];
        }
    }
}
