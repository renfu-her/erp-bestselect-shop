<?php

namespace App\Models;

use App\Enums\Delivery\Event;
use App\Enums\DlvOutStock\DlvOutStockType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DlvOutStock extends Model
{
    use HasFactory;
    protected $table = 'dlv_out_stock';
    protected $guarded = [];
    public $timestamps = true;

    public static function getDataWithDeliveryID($delivery_id) {
        $result = DB::table(app(DlvOutStock::class)->getTable(). ' as dlv_back')
            ->where('dlv_back.delivery_id', $delivery_id)
            ->where('dlv_back.type', DlvOutStockType::product()->value)
            ->select(
                'dlv_back.id'
                , 'dlv_back.event_item_id'
                , 'dlv_back.product_style_id'
                , 'dlv_back.sku'
                , 'dlv_back.product_title'
                , 'dlv_back.price'
                , 'dlv_back.origin_qty'
                , 'dlv_back.qty as back_qty'
                , DB::raw('ifnull(dlv_back.bonus, "") as bonus')
                , 'dlv_back.dividend'
                , 'dlv_back.memo'
                , 'dlv_back.show'
            );
        return $result;
    }

    public static function getOtherDataWithDeliveryID($delivery_id) {
        $result = DB::table(app(DlvOutStock::class)->getTable(). ' as dlv_backoth')
            ->leftJoinSub(GeneralLedger::getAllGrade(), 'l_grade', function($join) {
                $join->on('l_grade.primary_id', 'dlv_backoth.grade_id');
            })
            ->where('dlv_backoth.type', '<>', DlvOutStockType::product()->value)
            ->where('dlv_backoth.delivery_id', '=', $delivery_id)
            ->select('dlv_backoth.id'
                , 'l_grade.code as grade_code'
                , 'l_grade.name as grade_name'
                , 'dlv_backoth.grade_id'
                , 'dlv_backoth.type'
                , 'dlv_backoth.product_title'
                , 'dlv_backoth.price'
                , 'dlv_backoth.qty'
                , 'dlv_backoth.memo'
            );
        return $result;
    }

    //取得訂單款式出貨數量
    public static function getOrderToDlvQty($delivery_id, $sub_order_id) {
        $suborder = SubOrders::where('id', '=', $sub_order_id)->first();
        $orditems = DB::table(app(OrderItem::class)->getTable(). ' as items')
            ->leftJoin(app(DlvOutStock::class)->getTable(). ' as outs', function ($join) use($delivery_id) {
                $join->on('outs.delivery_id', '=', DB::raw($delivery_id));
                $join->on('outs.event_item_id', '=', 'items.id');
                $join->where('outs.type', '=', DB::raw(DlvOutStockType::product()->value));
            })
            ->where('items.sub_order_id', '=', $suborder->id)
            ->where('items.order_id', '=', $suborder->order_id)
            ->whereNotNull('items.product_style_id')
            ->select('items.product_style_id'
                , 'items.qty'
                , 'outs.qty as out_qty'
                , DB::raw('items.qty - ifnull(outs.qty, 0) as stock_qty')
            );
        return $orditems;
    }

    //取得寄倉款式出貨數量
    public static function getCsnToDlvQty($delivery_id, $csn_id) {
        $orditems = DB::table(app(ConsignmentItem::class)->getTable(). ' as items')
            ->leftJoin(app(DlvOutStock::class)->getTable(). ' as outs', function ($join) use($delivery_id) {
                $join->on('outs.delivery_id', '=', DB::raw($delivery_id));
                $join->on('outs.event_item_id', '=', 'items.id');
                $join->where('outs.type', '=', DB::raw(DlvOutStockType::product()->value));
            })
            ->where('items.consignment_id', '=', $csn_id)
            ->whereNotNull('items.product_style_id')
            ->select('items.product_style_id'
                , 'items.num as qty'
                , 'outs.qty as out_qty'
                , DB::raw('items.num - ifnull(outs.qty, 0) as stock_qty')
            );
        return $orditems;
    }

    public static function getAllOrderToDlvQty($product_style_id = null) {
        $re = DB::table(app(Delivery::class)->getTable(). ' as dlv')
            ->leftJoin(app(SubOrders::class)->getTable(). ' as sub_order', function ($join) {
                $join->on('sub_order.id', '=', 'dlv.event_id');
                $join->where('dlv.event', '=', Event::order()->value);
            })
            ->leftJoin(app(Order::class)->getTable(). ' as order', 'order.id', '=', 'sub_order.order_id')
            ->leftJoin('ord_items as item', 'item.sub_order_id', '=', 'sub_order.id')
            ->leftJoin(app(DlvOutStock::class)->getTable(). ' as outs', function ($join) {
                $join->on('outs.delivery_id', '=', 'dlv.id');
                $join->on('outs.event_item_id', '=', 'item.id');
                $join->where('outs.type', '=', DB::raw(DlvOutStockType::product()->value));
            })
            ->select([
                'dlv.id as delivery_id', 'dlv.event', 'dlv.event_sn', 'dlv.event_id'
                , DB::raw('DATE_FORMAT(dlv.created_at,"%Y-%m-%d %H:%i:%s") as created_at')
                , 'item.product_style_id', 'item.sku', 'item.qty', DB::raw('ifnull(item.qty, 0) - ifnull(outs.qty, 0) as stock_qty')
            ])
            ->whereIn('order.status_code', ['Add', 'Paided', 'Unpaid', 'Unbalance', 'Received'])
            ->whereNotNull('item.product_style_id')
            ->whereNull('dlv.audit_date')
            ->whereNull('dlv.deleted_at');

        if (isset($product_style_id)) {
            $re->where('item.product_style_id', $product_style_id);
        }
        return $re;
    }

    public static function getAllCsnToDlvQty($product_style_id = null) {
        $re_csn = DB::table(app(Delivery::class)->getTable(). ' as dlv')
            ->leftJoin(app(Consignment::class)->getTable(). ' as csn', function ($join) {
                $join->on('csn.id', '=', 'dlv.event_id');
                $join->where('dlv.event', '=', Event::consignment()->value);
            })
            ->leftJoin(app(ConsignmentItem::class)->getTable(). ' as csnitem', function ($join) {
                $join->on('csnitem.consignment_id', '=', 'csn.id');
            })
            ->leftJoin(app(DlvOutStock::class)->getTable(). ' as outs', function ($join) {
                $join->on('outs.delivery_id', '=', 'dlv.id');
                $join->on('outs.event_item_id', '=', 'csnitem.id');
                $join->where('outs.type', '=', DB::raw(DlvOutStockType::product()->value));
            })
            ->select([
                'dlv.id as delivery_id', 'dlv.event', 'dlv.event_sn', 'dlv.event_id'
                , DB::raw('DATE_FORMAT(dlv.created_at,"%Y-%m-%d %H:%i:%s") as created_at')
                , 'csnitem.product_style_id', 'csnitem.sku', 'csnitem.num as qty', DB::raw('ifnull(csnitem.num, 0) - ifnull(outs.qty, 0) as stock_qty')
            ])
            ->whereNotNull('csnitem.product_style_id')
            ->whereNotNull('csn.audit_date')
            ->whereNull('dlv.audit_date')
            ->whereNull('dlv.deleted_at');
        if (isset($product_style_id)) {
            $re_csn->where('csnitem.product_style_id', $product_style_id);
        }
        return $re_csn;
    }
}
