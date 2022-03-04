<?php

namespace App\Models;

use App\Enums\Globals\ResponseParam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReceiveDepot extends Model
{
    use HasFactory;

    protected $table = 'dlv_receive_depot';
    public $timestamps = false;
    protected $guarded = [];

    public static function setData($id = null, $delivery_id, $event_item_id = null, $freebies, $inbound_id, $inbound_sn, $depot_id, $depot_name, $product_style_id, $sku, $product_title, $qty, $expiry_date)
    {
        $data = null;
        $dataGet = null;
        if (null != $id) {
            $data = ReceiveDepot::where('id', $id);
            $dataGet = $data->get()->first();
        }
        $result = null;
        if (null == $dataGet) {
            $result = ReceiveDepot::create([
                'delivery_id' => $delivery_id,
                'event_item_id' => $event_item_id,
                'freebies' => $freebies,
                'inbound_id' => $inbound_id,
                'inbound_sn' => $inbound_sn,
                'depot_id' => $depot_id,
                'depot_name' => $depot_name,
                'product_style_id' => $product_style_id,
                'sku' => $sku,
                'product_title' => $product_title,
                'qty' => $qty,
                'expiry_date' => $expiry_date,
            ])->id;
        } else {
            $result = DB::transaction(function () use ($data, $dataGet, $freebies, $inbound_id, $inbound_sn, $depot_id, $depot_name, $product_style_id, $sku, $product_title, $qty, $expiry_date
            ) {
                $data->update([
                    'freebies' => $freebies,
                    'inbound_id' => $inbound_id,
                    'inbound_sn' => $inbound_sn,
                    'depot_id' => $depot_id,
                    'depot_name' => $depot_name,
                    'product_style_id' => $product_style_id,
                    'sku' => $sku,
                    'product_title' => $product_title,
                    'qty' => $qty,
                    'expiry_date' => $expiry_date,
                ]);
                return $dataGet->id;
            });
        }
        return $result;
    }

    /**
     * 新增對應的入庫商品款式
     * @param $input_arr inbound_id:入庫單ID ; qty:數量
     * @param $delivery_id 出貨單ID
     * @param $itemId 子訂單對應商品 ord_items id
     * @return array|mixed|void
     */
    public static function setDatasWithDeliveryIdWithItemId($input_arr, $delivery_id, $itemId) {
        if (null != $input_arr['qty'] && 0 < count($input_arr['qty'])) {
            try {
                return DB::transaction(function () use ($delivery_id, $itemId, $input_arr
                ) {
                    foreach($input_arr['qty'] as $key => $val) {
                        $inbound = PurchaseInbound::getSelectInboundList(['inbound_id' => $input_arr['inbound_id'][$key]])->get()->first();
                        if (null != $inbound) {
                            if (0 > $inbound->qty - $val) {
                                throw new \Exception('庫存數量不足');
                            }
                            ReceiveDepot::setData(
                                null,
                                $delivery_id, //出貨單ID
                                $itemId, //子訂單商品ID
                                $input['freebies'][$key] ?? 0, //是否為贈品 0:否
                                $inbound->inbound_id,
                                $inbound->inbound_sn,
                                $inbound->depot_id,
                                $inbound->depot_name,
                                $inbound->product_style_id,
                                $inbound->style_sku,
                                $inbound->product_title. '-'. $inbound->style_title,
                                $val, //數量
                                $inbound->expiry_date);
                        }
                    }
                });
            } catch (\Exception $e) {
                return [ResponseParam::status()->key => 1, ResponseParam::msg()->key => $e->getMessage()];
            }
        }
    }

    //將收貨資料變更為成立
    public static function setUpData($id) {
        $dataGet = null;
        if (null != $id) {
            $data = ReceiveDepot::where('delivery_id', $id);
            $dataGet = $data->get();
        }
        $result = null;
        if (null != $dataGet && 0 < count($dataGet)) {
            $result = DB::transaction(function () use ($data, $dataGet, $id
            ) {
                $data->update([
                    'is_setup' => 1,
                ]);

                //扣除入庫單庫存
                foreach ($dataGet as $item) {
                    PurchaseInbound::shippingInbound($item->inbound_id, $item->qty);
                }
            });
        }
        return $result;
    }

    public static function getDataListWithOrder($order_id = null, $sub_order_id = null) {
        $query = DB::table('receive_depot as rcv_depot')
            ->select('rcv_depot.id as id'
                , 'rcv_depot.order_id as order_id'
                , 'rcv_depot.sub_order_id as sub_order_id'
                , 'rcv_depot.inbound_id as inbound_id'
                , 'rcv_depot.depot_id as depot_id'
                , 'rcv_depot.depot_name as depot_name'
                , 'rcv_depot.product_style_id as product_style_id'
                , 'rcv_depot.qty as qty'
                , 'rcv_depot.expiry_date as expiry_date'
            );

        //訂單單號
        if (null != $order_id) {
            $query->where('rcv_depot.order_id', '=', $order_id);
        }
        //出貨單號
        if (null != $sub_order_id) {
            $query->where('rcv_depot.sub_order_id', '=', $sub_order_id);
        }
    }

    //取得出貨列表
    public static function getDeliveryWithReceiveDepotList($event, $event_id, $delivery_id)
    {
        $data = Delivery::getData($event, $event_id);
        $dataGet = null;
        if (null != $data) {
            $dataGet = $data->get()->first();
        }
        $result = null;
        if (null != $dataGet) {
            $result = DB::table('dlv_delivery as delivery')
                ->leftJoin('dlv_receive_depot as rcv_depot', 'rcv_depot.delivery_id', '=', 'delivery.id')
                ->select('delivery.sn as delivery_sn'
                    , 'rcv_depot.delivery_id as delivery_id'
                    , 'rcv_depot.id as id'
                    , 'rcv_depot.event_item_id as event_item_id'
                    , 'rcv_depot.freebies as freebies'
                    , 'rcv_depot.inbound_id as inbound_id'
                    , 'rcv_depot.inbound_sn as inbound_sn'
                    , 'rcv_depot.depot_id as depot_id'
                    , 'rcv_depot.depot_name as depot_name'
                    , 'rcv_depot.product_style_id as product_style_id'
                    , 'rcv_depot.sku as sku'
                    , 'rcv_depot.product_title as product_title'
                    , 'rcv_depot.qty as qty'
                    , 'rcv_depot.expiry_date as expiry_date'
                    , 'rcv_depot.is_setup as is_setup'
                )
                ->where('delivery.event', $event)
                ->where('delivery.event_id', $event_id)
                ->where('rcv_depot.delivery_id', $delivery_id)
                ->whereNull('rcv_depot.deleted_at')
                ->orderBy('rcv_depot.id');
        }
        return $result;
    }

}
