<?php

namespace App\Exports\Stock;

use App\Models\PcsErrStock0917;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

//匯出找舊系統沒有庫存，新系統卻是有庫存的 EXCEL
class OldNewStockDiffOnlyExport implements FromArray, WithHeadings
{
    use Exportable;

    public function __construct()
    {
    }

    public function headings(): array
    {
        return [
            '#'
            , '類別'
            , '商品名稱'
            , '款式'
            , '款式SKU'
            , '實際庫存'
            , '負責人'
        ];
    }

    public function array(): array
    {
        $query = DB::table(app(PcsErrStock0917::class)->getTable(). ' as es0917')
            ->select('es0917.no'
                , 'es0917.type_title'
                , 'es0917.product_title'
                , 'es0917.spec'
                , 'es0917.sku'
                , 'es0917.total_in_stock_num'
                , 'es0917.user_name'
            )
            ->orderBy('es0917.id')
            ->get()->toArray();
        ;
        return $query;
    }
}
