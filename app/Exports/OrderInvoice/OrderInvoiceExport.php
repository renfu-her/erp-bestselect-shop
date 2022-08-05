<?php

namespace App\Exports\OrderInvoice;

use App\Models\OrderInvoice;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;

use Maatwebsite\Excel\Concerns\Exportable;


class OrderInvoiceExport implements FromArray
{
    use Exportable;

    public function __construct($cond)
    {
        $this->cond = $cond;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function array(): array
    {
        $re = OrderInvoice::getData($this->cond)
            ->select('ord_invoice.invoice_number'
                , DB::raw('DATE_FORMAT((ifnull(ord_invoice.create_status_time, ord_invoice.created_at)),"%Y-%m-%d") as invoice_date')
                , 'ord_invoice.buyer_name'
                , 'ord_invoice.merchant_order_no'
                , 'ord_invoice.buyer_ubn'
                , DB::raw('(substring_index(ord_invoice.item_name, "|", 1)) as item_1_name')
                , 'ord_invoice.amt'
                , 'ord_invoice.tax_amt'
                , 'ord_invoice.total_amt')
            ->get()->toArray();

        $title = [
            'invoice_number' => '發票號碼',
            'invoice_date' => '發票日期',
            'buyer_name' => '買受人',
            'merchant_order_no' => '訂購單號',
            'buyer_ubn' => '統一編號',
            'item_name' => '摘要',
            'amt' => '未稅金額',
            'tax_amt' => '稅金',
            'total_amt' => '含稅金額'
        ];

        $re = json_decode(json_encode($re), true);
        array_unshift($re, (array) $title);

        return  $re;
    }
}
