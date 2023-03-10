<?php

namespace App\Models;

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

}
