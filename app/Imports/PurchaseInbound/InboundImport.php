<?php

namespace App\Imports\PurchaseInbound;

use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;

class InboundImport implements OnEachRow
{
    public $purchase = [];

    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row = $row->toArray();
        if (1 < $rowIndex) {
            $data = [
                'purchase_sn' => $row[1]
                , 'sku' => $row[2]
                , 'title' => $row[3]
                , 'user_name' => $row[4]
                , 'user_code' => $row[5]
                , 'supplier_name' => $row[6]
                , 'supplier_vat_no' => $row[7]
                , 'inbound_date' => (isset($row[8])) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[8])->format('Y-m-d') : null
                , 'remaining_qty' => $row[13]
                , 'unit_cost' => $row[10]
                , 'expiry_date' => (isset($row[11])) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[11])->format('Y-m-d') : null
            ];
            if (0 == count($this->purchase) || (0 < count($this->purchase) && $row[1] != $this->purchase[count($this->purchase) - 1]['purchase_sn'])) {
                if (null != $row[1] && null != $row[6] && null != $row[7]) {
                    $this->purchase[] = [
                        'purchase_sn' => $row[1]
                        , 'supplier_name' => [$row[6]]
                        , 'supplier_vat_no' => [$row[7]]
                        , 'purchase_user_name' => $row[4] //採購人員使用同採購單 欄位負責人 第一位
                        , 'purchase_user_code' => str_pad($row[5], 5, '0', STR_PAD_LEFT)
                        , 'data' => [$data]
                    ];
                }
            }
            //判斷採購單耗若相同 則指新增後面商品
            //否則再新增一筆採購單
            else if ((0 < count($this->purchase) && $row[1] == $this->purchase[count($this->purchase) - 1]['purchase_sn'])) {
                $this->purchase[count($this->purchase) - 1]['supplier_name'][] = $row[6];
                array_push($this->purchase[count($this->purchase) - 1]['data'], $data);
            }
        }
        return;
    }
}
