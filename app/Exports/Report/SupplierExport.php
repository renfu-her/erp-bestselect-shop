<?php

namespace App\Exports\Report;

use App\Exports\Sheets\SupplierSheet;
use App\Models\RptReport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromArray;

class SupplierExport implements FromArray
{
    public $year;

    public function __construct($year)
    {
        $this->year = $year;

    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function array(): array
    {

        $re = RptReport::atomic()
            ->whereRaw('YEAR(ro.receipt_date) = ' . $this->year)
            ->leftJoin('prd_products as product', 'product.id', '=', 'style.product_id')
            ->leftJoin('prd_product_supplier as ps', 'ps.product_id', '=', 'style.product_id')
            ->leftJoin('prd_suppliers as supplier', 'supplier.id', '=', 'ps.supplier_id')
          //  ->groupByRaw('QUARTER(ro.receipt_date)')
          //  ->selectRaw('GROUP_CONCAT(DISTINCT supplier.name) as supplier_name')
          //  ->selectRaw('QUARTER(ro.receipt_date) as quarter')
             ->select('supplier.name')
            ->distinct()
            ->get()
            ->toArray();
    
            $output = [['廠商名稱']];

            foreach($re as $v){
                $output[] = [$v->name];
            }

        /*
        $output = [];

        foreach($re as $v){

           $output[] = new SupplierSheet($v->quarter,$v->supplier_name);
        }*/

        return $output;

    }
}
