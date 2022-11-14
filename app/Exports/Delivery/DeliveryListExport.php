<?php

namespace App\Exports\Delivery;

use Maatwebsite\Excel\Concerns\FromArray;

/**
 * 匯出出貨管理
 */
class DeliveryListExport implements FromArray
{
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * use array to export
     */
    public function array(): array
    {
        return $this->data;
    }
}
