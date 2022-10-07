<?php

namespace App\Exports\Stock;

use App\Models\PurchaseInbound;
use Illuminate\Support\Arr;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

//匯出找舊系統沒有庫存，新系統卻是有庫存的 EXCEL
class OldNewStockDiffExport implements FromArray, WithHeadings
{
    use Exportable;

    protected $prdStyle;

    public function __construct($prdStyle)
    {
        $this->prdStyle = $prdStyle;
    }

    public function headings(): array
    {
        return [
            '#'
            ,'商品名稱'
            , '款式'
            , '款式SKU'
            , '實際庫存'
            , '負責人'
        ];
    }

    public function array(): array
    {
        $query = [];
        $searchParam = [];
        $searchParam['keyword'] = Arr::get($query, 'keyword');
        $searchParam['type'] = Arr::get($query, 'type');
        $searchParam['consume'] = Arr::get($query, 'consume', '0');
        $searchParam['user'] = Arr::get($query, 'user');
        $searchParam['supplier'] = Arr::get($query, 'supplier');
        $searchParam['stock'] = Arr::get($query, 'stock',[]);
        $searchParam['depot_id'] = [1]; //當初匯入的只有在新莊理貨倉
        $searchParam['type'] = 'all';

        $products = PurchaseInbound::productStyleListWithExistInbound([], $searchParam)
            ->orderBy('s.product_id')
            ->orderBy('s.id')
            ->get();

        $diffStock = [];
        if (0 < count($products) && 0 < count($this->prdStyle)) {
            foreach ($products as $key_prd => $val_prd) {
                foreach ($this->prdStyle as $key_style => $val_style) {
                    if ($val_prd->sku == $val_style['sku'] && 0 < $val_prd->total_in_stock_num) {
                        $diffStock[] = [
                            'no' => $val_style['no']
                            , 'type_title' => $val_prd->type_title
                            , 'product_title' => $val_prd->product_title
                            , 'spec' => $val_prd->spec
                            , 'sku' => $val_prd->sku
                            , 'total_in_stock_num' => $val_prd->total_in_stock_num
                            , 'user_name' => $val_prd->user_name
                        ];
                    }
                }
            }
        }
        return $diffStock;
    }
}
