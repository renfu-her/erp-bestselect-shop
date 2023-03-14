<?php

namespace App\Models;

use App\Enums\Delivery\Event;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PcsScrapItem extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'pcs_scrap_item';
    protected $guarded = [];

    public static function getProductItemList($searchParam)
    {
        $concatString = concatStr([
            'id' => DB::raw('ifnull(scrapitem.id, "")'),
            'inbound_id' => DB::raw('ifnull(scrapitem.inbound_id, "")'),
            'product_style_id' => DB::raw('ifnull(scrapitem.product_style_id, "")'),
            'sku' => DB::raw('ifnull(scrapitem.sku, "")'),
            'product_title' => DB::raw('ifnull(scrapitem.product_title, "")'),
            'qty' => DB::raw('ifnull(scrapitem.qty, 0)'),
            'memo' => DB::raw('ifnull(scrapitem.memo, "")'),
            'expiry_date' => DB::raw('ifnull(DATE_FORMAT(inbound.expiry_date,"%Y-%m-%d %H:%i:%s"), "")'),
        ]);

        $query = DB::table(app(PcsScraps::class)->getTable() . ' as scraps')
            ->leftJoin(app(PcsScrapItem::class)->getTable() . ' as scrapitem', 'scrapitem.scrap_id', '=', 'scraps.id')
            ->leftJoin(app(PurchaseInbound::class)->getTable() . ' as inbound', 'inbound.id', '=', 'scrapitem.inbound_id')
            ->where('scraps.deleted_at', null)
            ->where('scraps.type', 'scrap')
            ->where('scrapitem.deleted_at', null)
            ->groupBy('scraps.id')
            ->select(
                'scraps.id',
                'scraps.sn',
                'scraps.user_name',
                'scraps.audit_user_name',
                'scraps.memo',
                'scraps.audit_status',
                DB::raw('DATE_FORMAT(scraps.created_at,"%Y-%m-%d %H:%i:%s") as created_at'),
                DB::raw($concatString . ' as groupConcat')
            );

        if ($searchParam['scrap_sn']) {
            $query->where('scraps.sn', 'like', "%{$searchParam['scrap_sn']}%");
        }
        return $query;
    }

    public static function getDataWithInboundQtyList($searchParam)
    {

        $query = DB::table(app(PcsScrapItem::class)->getTable() . ' as scrap_items')
            ->leftJoin(app(PurchaseInbound::class)->getTable() . ' as inbound', 'scrap_items.inbound_id', '=', 'inbound.id')
            ->leftJoin(app(ProductStyle::class)->getTable() . ' as style', 'scrap_items.product_style_id', '=', 'style.id')

            ->leftJoin(app(Purchase::class)->getTable() . ' as pcs', function ($join) {
                $join->on('pcs.id', '=', 'inbound.event_id')
                    ->where('inbound.event', '=', Event::purchase()->value);
            })
            ->leftJoin(app(Consignment::class)->getTable() . ' as csn', function ($join) {
                $join->on('csn.id', '=', 'inbound.event_id')
                    ->where('inbound.event', '=', Event::consignment()->value);
            })
            ->select(
                'scrap_items.id as item_id',
                'scrap_items.inbound_id',
                'scrap_items.product_style_id',
                'scrap_items.product_title',
                'scrap_items.sku',
                'scrap_items.qty as to_scrap_qty',
                'scrap_items.memo',
                DB::raw('case when "'. Event::purchase()->value. '" = inbound.event then "'. Event::purchase()->description. '"'
                    . ' when "'. Event::consignment()->value. '" = inbound.event then "'. Event::consignment()->description. '"'
                    . ' else null end as event_name'),
                DB::raw('case when "'. Event::purchase()->value. '" = inbound.event then pcs.sn'
                    . ' when "'. Event::consignment()->value. '" = inbound.event then csn.sn'
                    . ' else null end as event_sn'),
                DB::raw('(inbound.inbound_num - inbound.sale_num - inbound.csn_num - inbound.consume_num - inbound.back_num - inbound.scrap_num) as remaining_qty'), //庫存剩餘數量

                'inbound.depot_name',
                DB::raw('DATE_FORMAT(inbound.expiry_date,"%Y-%m-%d") as expiry_date'),
                'style.in_stock',
            )
            ->where('scrap_items.type', '=', 0)
            ->whereNull('scrap_items.deleted_at');

        if (isset($searchParam['scrap_id'])) {
            $query->where('scrap_items.scrap_id', $searchParam['scrap_id']);
        }
        if (isset($searchParam['inbound_ids'])) {
            $query->whereIn('inbound.id', $searchParam['inbound_ids']);
        }
        return $query;
    }



}
