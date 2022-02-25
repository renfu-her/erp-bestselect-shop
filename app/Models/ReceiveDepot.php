<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReceiveDepot extends Model
{
    use HasFactory;

    protected $table = 'dlv_receive_depot';
    public $timestamps = false;
    protected $guarded = [];

    public static function setData($id = null, $delivery_id, $freebies, $inbound_id, $depot_id, $depot_name, $product_style_id, $sku, $product_title, $qty, $expiry_date)
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
                'freebies' => $freebies,
                'inbound_id' => $inbound_id,
                'depot_id' => $depot_id,
                'depot_name' => $depot_name,
                'product_style_id' => $product_style_id,
                'sku' => $sku,
                'product_title' => $product_title,
                'qty' => $qty,
                'expiry_date' => $expiry_date,
            ])->id;
        } else {
            $result = DB::transaction(function () use ($data, $dataGet, $freebies, $inbound_id, $depot_id, $depot_name, $product_style_id, $sku, $product_title, $qty, $expiry_date
            ) {
                $data->update([
                    'freebies' => $freebies,
                    'inbound_id' => $inbound_id,
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
}
