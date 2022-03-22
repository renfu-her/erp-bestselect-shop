<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Consum extends Model
{
    use HasFactory;
    protected $table = 'dlv_consum';
//    public $timestamps = false;
    protected $guarded = [];

    public static function createData($logistic_id, $inbound_id, $inbound_sn, $depot_id, $depot_name, $product_style_id, $sku, $product_title, $qty, $expiry_date)
    {
        $result = Consum::create([
            'logistic_id' => $logistic_id,
            'inbound_id' => $inbound_id,
            'inbound_sn' => $inbound_sn,
            'depot_id' => $depot_id,
            'depot_name' => $depot_name,
            'product_style_id' => $product_style_id,
            'sku' => $sku,
            'product_title' => $product_title,
            'qty' => $qty,
        ])->id;
        return ['success' => 1, 'error_msg' => "", 'id' => $result];
    }

    /**
     * 新增對應的入庫商品款式
     * @param $input_arr inbound_id:入庫單ID ; qty:數量
     * @param $logistic_id 物流單ID
     * @return array|mixed|void
     */
    public static function setDatasWithLogisticId($input_arr, $logistic_id) {
        return DB::transaction(function () use ($logistic_id, $input_arr
        ) {
            if (null != $input_arr['qty'] && 0 < count($input_arr['qty'])) {
                $addIds = [];
                foreach($input_arr['qty'] as $key => $val) {
                    $inbound = PurchaseInbound::getSelectInboundList(['inbound_id' => $input_arr['inbound_id'][$key]])->get()->first();
                    if (null != $inbound) {
                        if (0 > $inbound->qty - $val) {
                            return ['success' => 0, 'error_msg' => "庫存數量不足"];
                        }
                        $reSD = Consum::createData(
                            $logistic_id, //出貨單ID
                            $inbound->inbound_id,
                            $inbound->inbound_sn,
                            $inbound->depot_id,
                            $inbound->depot_name,
                            $inbound->product_style_id,
                            $inbound->style_sku,
                            $inbound->product_title. '-'. $inbound->style_title,
                            $val, //數量
                            $inbound->expiry_date);
                        if ($reSD['success'] == 0) {
                            DB::rollBack();
                            return $reSD;
                        } else {
                            array_push($addIds, $reSD['id']);
                        }
                    }
                }
                return ['success' => 1, 'error_msg' => "", 'id' => $addIds];
            } else {
                return ['success' => 0, 'error_msg' => "未輸入數量"];
            }
        });
    }

    //將物流資料變更為成立
    public static function setUpLogisticData($logistic_id, $user_id, $user_name) {
        $logistic = Logistic::where('id', '=', $logistic_id)->get();

        $dataGet = null;
        if (null != $logistic_id) {
            $data = Consum::where('logistic_id', $logistic_id);
            $dataGet = $data->get();
        }
        $result = null;
        if (null != $logistic && null != $dataGet && 0 <= count($dataGet)) {
            $result = DB::transaction(function () use ($data, $dataGet, $logistic_id, $user_id, $user_name
            ) {
                //扣除入庫單庫存
                foreach ($dataGet as $item) {
                    $reShipIb = PurchaseInbound::shippingInbound($item->inbound_id, $item->qty);
                    if ($reShipIb['success'] == 0) {
                        DB::rollBack();
                        return $reShipIb;
                    }
                }
                $curr_date = date('Y-m-d H:i:s');
                Logistic::where('id', '=', $logistic_id)->update([
                    'audit_date' => $curr_date,
                    'audit_user_id' => $user_id,
                    'audit_user_name' => $user_name,
                ]);

                return ['success' => 1, 'error_msg' => ""];
            });
        } else {
            return ['success' => 0, 'error_msg' => "無此物流單"];
        }
        return $result;
    }

    public static function deleteById($id)
    {
        Consum::where('id', $id)->delete();
    }

    //取得耗材X入庫列表
    public static function getConsumWithInboundList($logistic_id = null, $product_style_id = null)
    {
        $concatString = concatStr([
            'consum_id' => 'consum.id',
            'inbound_id' => 'consum.inbound_id',
            'inbound_sn' => 'consum.inbound_sn',
            'depot_id' => 'consum.depot_id',
            'depot_name' => 'consum.depot_name',
            'qty' => 'consum.qty',
            'created_at' => 'consum.created_at',
            ]);

        $result = DB::table('dlv_logistic as logistic')
            ->leftJoin('dlv_consum as consum', 'consum.logistic_id', '=', 'logistic.id')
            ->select('logistic.sn as logistic_sn'
                , 'consum.logistic_id as logistic_id'
                , 'consum.product_style_id as product_style_id'
                , 'consum.sku as sku'
                , 'consum.product_title as product_title'
                , DB::raw('sum(consum.qty) as total_qty')

            )
            ->selectRaw(('('.$concatString. '   ) as groupconcat'))
            ->groupBy('logistic.sn')
            ->groupBy('consum.logistic_id')
            ->groupBy('consum.product_style_id')
            ->groupBy('consum.sku')
            ->groupBy('consum.product_title')
            ->orderBy('consum.product_style_id');

        if (null != $logistic_id) {
            $result->where('consum.logistic_id', $logistic_id);
        }
        if (null != $product_style_id) {
            $result->where('consum.product_style_id', $product_style_id);
        }

        return $result;
    }


}
