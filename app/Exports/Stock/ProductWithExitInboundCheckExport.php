<?php

namespace App\Exports\Stock;

use App\Models\PurchaseInbound;
use Illuminate\Support\Facades\DB;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

//匯出盤點明細EXCEL
class ProductWithExitInboundCheckExport implements FromQuery, WithHeadings
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
            , '負責人'
            , '倉庫'
            , '理貨倉庫存'
            , '寄倉庫存'
        ];
    }

    public function query()
    {
        $products = DB::query()->fromSub(PurchaseInbound::productStyleListWithExistInbound($this->depot_id, $this->searchParam), 'tb_tslwei')
            ->select('tb_tslwei.type_title'
                ,'tb_tslwei.product_title'
                , 'tb_tslwei.spec'
                , 'tb_tslwei.sku'
                , 'tb_tslwei.suppliers_name'
                , 'tb_tslwei.depot_name'
                , 'tb_tslwei.total_in_stock_num'
                , 'tb_tslwei.total_in_stock_num_csn'
            )
            ->orderBy('tb_tslwei.product_id')
            ->orderBy('tb_tslwei.id')
        ;

        return  $products;
    }
}
