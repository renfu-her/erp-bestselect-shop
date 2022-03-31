<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\DB;

class DepotProduct extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'prd_product_depot_select';

    protected $grard = [];


    public static function product_list($depot_id = null, $keyword = null, $type = null)
    {
        $re = DB::table('prd_product_depot_select as select_list')
            ->leftJoin('depot', 'select_list.depot_id', '=', 'depot.id')
            ->leftJoin('prd_products as product', 'select_list.product_id', '=', 'product.id')
            ->leftJoin('prd_product_styles as style', 'select_list.product_style_id', '=', 'style.id')
            ->leftJoin('usr_users as user', 'select_list.updated_users_id', '=', 'user.id')

            ->leftJoin('prd_salechannel_style_price as p', 'style.id', '=', 'p.style_id')

            ->where(function ($q) use ($keyword) {
                if ($keyword) {
                    $q->where('product.title', 'like', "%$keyword%");
                    $q->orWhere('style.title', 'like', "%$keyword%");
                    $q->orWhere('style.sku', 'like', "%$keyword%");
                }
            })
            ->whereNotNull('style.sku')
            ->whereNull('style.deleted_at')
            ->whereNull('select_list.deleted_at')
            ->where('p.sale_channel_id', 1)

            ->select(
                'select_list.id as select_id',
                'select_list.depot_id',
                'select_list.depot_product_no',
                'select_list.ost_price',
                'select_list.depot_price',
                'style.id',
                'style.sku',
                'product.title as product_title',
                'product.id as product_id',
                'style.title as spec',
                'style.safety_stock',
                'style.total_inbound',
                'p.sale_channel_id',
                'p.dealer_price',
                'p.origin_price',
                'p.price',
                'p.bonus',
                'p.dividend',
            );

            // ->select('select_list.*', 'depot.*', 'product.*', 'style.*');

        if ($depot_id) {
            $re->where('select_list.depot_id', $depot_id);
        }

        if ($type && $type != 'all') {
            $re->where('style.type', $type);
        }

        return $re;
    }
}
