<?php

namespace App\Exports\Report;

use App\Models\RptUserPerformanceReport;
use Maatwebsite\Excel\Concerns\FromArray;

class UserPerformanceExport implements FromArray
{
    public function __construct($sdate, $edate, $options)
    {
        $this->sdate = $sdate;
        $this->edate = $edate;
        $this->options = $options;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    function array(): array
    {
        //

        $data = RptUserPerformanceReport::dataList($this->sdate, $this->edate, $this->options)->get()->toArray();

        array_unshift($data, ['姓名', '營業額', '部門', '組別']);

        return $data;
    }
}
