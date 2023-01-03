<?php

namespace App\Exports\Delivery;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * 匯出出貨管理
 */
class DeliveryListExport implements FromArray, WithStyles
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
    public function styles(Worksheet $sheet)
    {
        //換行用
        $sheet->getStyle('N')->getAlignment()->setWrapText(true);
        $sheet->getStyle('O')->getAlignment()->setWrapText(true);
    }
}
