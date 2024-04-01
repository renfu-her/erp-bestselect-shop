<?php

namespace App\Exports\Report;

use App\Exports\Sheets\SupplierSheet;
use App\Models\CustomerDividend;
use App\Models\RptReport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromArray;

class RemainDividend implements FromArray
{
    public $year;

    public function __construct()
    {
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function array(): array
    {


        $re = CustomerDividend::getRemainList();
       

        $output = [['類型', '點數', '到期時間']];

        foreach ($re as $v) {
            $output[] = [$v['category_ch'], $v['dividend'], $v['active_edate']];
        }

        /*
        $output = [];

        foreach($re as $v){

           $output[] = new SupplierSheet($v->quarter,$v->supplier_name);
        }*/

        return $output;
    }
}
