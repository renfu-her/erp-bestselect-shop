<?php

namespace App\Exports\Product;

use Maatwebsite\Excel\Concerns\FromArray;

/**
 * 匯出產品資訊
 */
class ProductInforExport implements FromArray
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
