<?php

namespace App\Exports\Delivery;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * 匯出出貨商品查詢
 */
class DeliveryProductListExport implements FromArray, WithStyles
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
        $sheet->getStyle('C')->getAlignment()->setWrapText(true);
        $sheet->getStyle('F')->getAlignment()->setWrapText(true);
        $sheet->getStyle('G')->getAlignment()->setWrapText(true);
        $sheet->getStyle('H')->getAlignment()->setWrapText(true);
        $sheet->getStyle('I')->getAlignment()->setWrapText(true);
        $sheet->getStyle('K')->getAlignment()->setWrapText(true);
        $sheet->getStyle('L')->getAlignment()->setWrapText(true);
        $sheet->getStyle('O')->getAlignment()->setWrapText(true);
    }
}
