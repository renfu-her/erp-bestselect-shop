<?php

namespace App\Imports\PurchaseInbound;

use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;

class CompareOldNonStock implements OnEachRow
{
    public $prdStyle = [];

    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row = $row->toArray();
//        dd('onRow', $row);
        if (1 < $rowIndex && '編號' != $row[0]) {
            $this->prdStyle[] = [
                'no' => $row[0]
                , 'sku' => $row[1]
                , 'title' => $row[2]
                , 'user_name' => $row[3]
                , 'remaining_qty' => $row[6]
                , 'depot_name' => $row[8]
            ];
        }
        return;
    }
}
