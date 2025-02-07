<?php

namespace App\Services\ThirdPartyApis\Youbon;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReceiveDepot;
use App\Models\SubOrders;
use App\Models\TikYoubonItem;
use App\Models\TikYoubonOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $decodedResponse = urldecode($response);
        $xml = simplexml_load_string($decodedResponse);

        if ($xml === false) {
            return [
                'error' => '無效的 XML 內容'
            ];
        }

        $result = [];
        // 基本資料
        $result['statcode'] = isset($xml->statcode) ? (string)$xml->statcode : '';
        $result['statdesc'] = isset($xml->statdesc) ? (string)$xml->statdesc : '';

        if ($result['statcode'] === YoubonErrorCode::SUCCESS || $result['statcode'] === YoubonErrorCode::ORDER_DUPLICATE) {
            // 解析主要資料
            $result['custbillno'] = isset($xml->custbillno) ? (string)$xml->custbillno : '';
            $result['billno']     = isset($xml->billno) ? (string)$xml->billno : '';
            $result['borrowno']   = isset($xml->borrowno) ? (string)$xml->borrowno : '';
            $result['billdate']   = isset($xml->billdate) ? (string)$xml->billdate : '';
            $result['weburl']     = isset($xml->weburl) ? (string)$xml->weburl : '';

            // 解析商品資料
            $result['items'] = [];
            if (isset($xml->item)) {
                foreach ($xml->item as $item) {
                    $result['items'][] = $this->parseItemByListNumberType($item);
                }
            }
        } else {
            // 處理錯誤
            $result['error'] = $this->getErrorMessage($result['statcode']);

            $errorFields = ['departid', 'userid', 'username', 'saletype', 'custbillno', 'paymenttype', 'statdesc'];
            foreach ($errorFields as $field) {
                if (isset($xml->$field) && strlen(trim((string)$xml->$field)) > 0) {
                    $result[$field] = (string)$xml->$field;
                }
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
    public function processOrder($delivery_id, array $orderData, array $ship_items): array
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

        try {
            // 送出訂單
            $result = $this->sendOrder($delivery_id, $orderData);

            // 檢查結果
            if ($result['statcode'] === YoubonErrorCode::SUCCESS || $result['statcode'] === YoubonErrorCode::ORDER_DUPLICATE) {
                TikYoubonOrder::createData($delivery_id, $result['custbillno'], $result['billno'], $result['borrowno'], $result['billdate'], $result['statcode'], $result['weburl']);
                // 判斷是否有商品資料
                if (isset($result['items'])) {
                    foreach ($result['items'] as $item) {
                        // 使用 ticket_number 找到對應 $ship_items 的 event_item_id、depot_id
                        $ship_item = collect($ship_items)->where('ticket_number', $item['productnumber'])->first();
                        $event_item_id = $ship_item->event_item_id;
                        $depot_id = $ship_item->depot_id;
                        TikYoubonItem::createData($delivery_id
                            , $event_item_id, $depot_id
                            , $item['productnumber'], $item['prodid'], $item['batchid'], $item['ordernumber'], $item['price']);
                    }
                }
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

    public function isETicketOrder($delivery_id)
    {
        $delivery = Delivery::find($delivery_id);
        if (null != $delivery && 'order' == $delivery->event) {
            $sub_order = SubOrders::where('id', '=', $delivery->event_id)->first();
            if ('eTicket' == $sub_order->ship_category) {
                return true;
            }
        }
        return false;
    }

    /**
     * 取得訂單資料
     *
     * @param int $delivery_id 出貨單號
     * @param int $order_id 訂單編號
     * @return array 訂單資料
     */
    public function getOrderData($delivery_id, $order_id): array
    {
        $orderQuery = DB::table('ord_orders as order')
            ->leftJoin('usr_customers as buyer', 'buyer.email', '=', 'order.email')
            ->where('order.id', '=', $order_id)
            ->select('order.id as order_id', 'order.sn as order_sn', 'buyer.email as buyer_email', 'buyer.phone as buyer_phone');
        Order::orderAddress($orderQuery);
        $order = $orderQuery->first();

        $ship_items = ReceiveDepot::getDataListForYoubonOrder($delivery_id, 'eYoubon')->get()->toArray();

        $ord_items = [];
        foreach ($ship_items as $item) {
            $ord_items[] = [
                'productnumber' => $item->ticket_number,
                'price' => $item->estimated_cost,
                'quantity' => (string)$item->qty,
            ];
        }

        $orderData = [
            'custbillno' => $order->order_sn,
            'fullname'    => $order->ord_name,
            'telephone'   => $order->buyer_phone,
            'email'       => $order->buyer_email,
            'items'       => $ord_items,
        ];

        return [
            'orderData' => $orderData,
            'ship_items' => $ship_items,
        ];
    }
}
