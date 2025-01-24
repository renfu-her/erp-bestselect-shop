<?php

namespace App\Services\ThirdPartyApis\Youbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use SimpleXMLElement;
use App\Models\TikYoubonApiLog;

class YoubonOrderService
{
    private const API_URL = 'https://b2b.youbon.com/api/orders.php';
    private $code = "5001star"; // API 交易密碼
    private $departId = "5001"; // 部門代碼

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

    // 錯誤代碼常數
    private const ERROR_CODES = [
        '0000' => '成功',
        '9900' => '訂單重複發送會回傳成功',
        '0001' => '出貨有狀況，請洽我方人員',
        '0002' => '此筆訂單已經出貨過，請勿重複送資料',
        '0003' => '查無此訂單',
        '0004' => '付款方式錯誤',
        '0005' => '部門編號錯誤',
        '0006' => '售出金額有誤，請洽我方人員',
        '0007' => '額度不足無法出貨，請補充額度後再送資料出貨',
        '0008' => '此訂單已退貨',
        '0009' => '交易類型錯誤',
        '0010' => '網購平臺編號錯誤',
        '0011' => '操作人員編號錯誤',
        '0012' => '操作人員姓名錯誤',
        '0013' => '訂單編號空白',
        '0014' => '有商品編號查詢不到資料',
        '0015' => 'E-mail空白',
        '9999' => '訂單異常'
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

    /**
     * 驗證必填欄位
     *
     * @param array $data 訂單資料
     * @throws \InvalidArgumentException
     */
    private function validateRequiredFields(array $data): void
    {
        $requiredFields = [
           'custbillno', 'fullname', 'telephone', 'email', 'items'
        ];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }
    }

    public function validInputValue(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
        $xml->addChild('userid', '627');
        $xml->addChild('username', '張若心');
        $xml->addChild('saletype', 'B2B2C');

        // $xml->addChild('departid', $data['departid']);
        // $xml->addChild('userid', $data['userid']);
        // $xml->addChild('username', $data['username']);
        // $xml->addChild('saletype', $data['saletype']);

        $xml->addChild('custbillno', $data['custbillno']);
        $xml->addChild('fullname', $data['fullname']);
        $xml->addChild('telephone', $data['telephone']);
        $xml->addChild('email', $data['email']);

        $xml->addChild('paymenttype', '003');
        $xml->addChild('listnumbertype', '1');

        // 加入商品資料
        $items = $xml->addChild('items');
        foreach ($data['items'] as $item) {
            $itemNode = $items->addChild('item');
            $itemNode->addChild('productnumber', $item['productnumber']);
            $itemNode->addChild('quantity', $item['quantity']);
            $itemNode->addChild('price', $item['price']);
        }
        // 移除 XML 宣告
        $xmlString = $xml->asXML();
        return preg_replace('/<\?xml.*\?>\n*/', '', $xmlString);
    }

    /**
     * 解析API回應
     *
     * @param string $response XML回應內容
     * @return array 解析結果
     */
    private function parseResponse(string $response): array
    {
        $xml = simplexml_load_string(urldecode($response));
        $result = [];

        // 基本資料
        $result['statcode'] = (string)$xml->statcode;
        $result['statdesc'] = (string)$xml->statdesc;

        // 如果成功才解析其他資料
        if ($result['statcode'] === '0000') {
            $result['billno'] = (string)$xml->billno;
            $result['borrowno'] = (string)$xml->borrowno;
            $result['billdate'] = (string)$xml->billdate;
            $result['weburl'] = (string)$xml->weburl;

            // 解析商品資料
            $result['items'] = [];
            if (isset($xml->items)) {
                foreach ($xml->items->item as $item) {
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
        switch ($item->listnumbertype) {
            case '1':
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
        return self::ERROR_CODES[$code] ?? '未知錯誤';
    }
}
