<?php

namespace App\Exports\Report;

use Maatwebsite\Excel\Concerns\FromArray;

/**
 * 匯出售價利潤報表
 */
class ProductProfitExport implements FromArray
{
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    function array(): array
    {
        return $this->data;
    }
}
