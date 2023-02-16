<?php

namespace App\Exports\Stock;

use App\Models\Category;
use App\Models\ProductStyle;
use App\Models\PurchaseInbound;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

//匯出庫存明細EXCEL
class ProductWithExitInboundDetailExport implements FromQuery, WithHeadings
{
    use Exportable;

    protected $depot_id;
    protected $searchParam;

    public function __construct($depot_id, $searchParam)
    {
        $this->depot_id = $depot_id;
        $this->searchParam = $searchParam;
    }

    public function headings(): array
    {
        return [
            '#'
            ,'商品名稱'
            , '款式'
            , '款式SKU'
            , '官網售價'
            , '經銷價'
            , '參考成本'
            , '負責人'
            , '倉庫'
            , '理貨倉庫存'
            , '寄倉庫存'
            , '官網可售數量(超賣)'
            , '廠商名稱'
            , '公開否'
            , '線上是否開啟'
            , '線下是否開啟'
            , '類別'
            , '可售數量'
        ];
    }

    public function query()
    {
        $products = DB::query()->fromSub(PurchaseInbound::productStyleListWithExistInbound($this->depot_id, $this->searchParam), 'tb_tslwei')
            ->leftJoinSub(ProductStyle::getChannelSubList(), 'channelSub', function($join) {
                $join->on('channelSub.style_id', 'tb_tslwei.id');
            })
            ->leftJoin(app(Category::class)->getTable() . ' as category', 'category.id', '=', 'tb_tslwei.category_id')
            ->select('tb_tslwei.type_title'
                ,'tb_tslwei.product_title'
                , 'tb_tslwei.spec'
                , 'tb_tslwei.sku'
                , 'channelSub.price'
                , 'channelSub.dealer_price'
                , 'tb_tslwei.estimated_cost'
                , 'tb_tslwei.user_name'
                , 'tb_tslwei.depot_name'
                , 'tb_tslwei.total_in_stock_num'
                , 'tb_tslwei.total_in_stock_num_csn'
                , DB::raw('CONCAT(ifnull(tb_tslwei.in_stock, 0), "(", ifnull(tb_tslwei.overbought, 0), ")") as in_stock_overbought')
                , 'tb_tslwei.suppliers_name'
                , DB::raw('IF(tb_tslwei.public = 1, "是", "否") as public')
                , DB::raw('IF(tb_tslwei.online = 1, "是", "否") as online')
                , DB::raw('IF(tb_tslwei.offline = 1, "是", "否") as offline')
                , 'category.category'
                , 'tb_tslwei.in_stock_original'
            )
            ->orderBy('tb_tslwei.product_id')
            ->orderBy('tb_tslwei.id')
        ;

        return  $products;
    }
}
