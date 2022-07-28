<?php

namespace App\Imports\PurchaseInbound;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class InboundImport implements ToCollection
{
    public $data;
    public function collection(Collection $collection)
    {
        $purchase = [];
        foreach ($collection as $key => $row) {
            //標頭不解析
            //剩餘數量0不寫入
            if (0 == $key || 0 == $row[13]) {
                continue;
            }
            $data = [
                'purchase_sn' => $row[1]
                , 'sku' => $row[2]
                , 'title' => $row[3]
                , 'user_name' => $row[4]
                , 'user_code' => $row[5]
                , 'supplier_name' => $row[6]
                , 'supplier_vat_no' => $row[7]
                , 'inbound_date' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[8])->format('Y-m-d')
                , 'remaining_qty' => $row[13]
                , 'unit_cost' => $row[10]
                , 'expiry_date' =>  \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[11])->format('Y-m-d')
            ];
            if(0 == count($purchase) || (0 < count($purchase) && $row[1] != $purchase[ count($purchase) - 1 ]['purchase_sn'])) {
                $purchase[] = [
                    'purchase_sn' => $row[1]
                    , 'supplier_name' => [$row[6]]
                    , 'supplier_vat_no' => [$row[7]]
                    , 'purchase_user_name' => $row[4]
                    , 'purchase_user_code' => $row[5]
                    , 'data' => [$data]
                ];
            }
            //判斷採購單耗若相同 則指新增後面商品
            //否則再新增一筆採購單
            else if ((0 < count($purchase) && $row[1] == $purchase[ count($purchase) - 1 ]['purchase_sn'])) {
                $purchase[ count($purchase) - 1 ]['supplier_name'][] = $row[6];
                array_push($purchase[ count($purchase) - 1 ]['data'], $data);
            }
        }
        if (0 < count($purchase)) {
            foreach ($purchase as $key_pcs => $key_val) {
                $purchase[$key_pcs]['supplier_name'] = array_unique($purchase[$key_pcs]['supplier_name'], SORT_STRING);
            }
        }
        $this->data = $purchase;
    }
}
