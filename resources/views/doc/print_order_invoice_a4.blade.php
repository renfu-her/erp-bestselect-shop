<dd>賣方公司名稱:{{ $invoice->seller_title }}</dd>
<dd>賣方公司統一編號:{{ $invoice->seller_ubn }}</dd>
<dd>賣方公司地址:{{ $invoice->seller_address }}</dd>

<dd>發票期別:{{ $invoice->zh_period }}</dd>
<dd>發票號碼:{{ substr($invoice->invoice_number, 0, 2) . '-' . substr($invoice->invoice_number, 2)}}</dd>
<dd>開立日期:{{ date('Y-m-d H:i:s', strtotime($invoice->created_at)) }}</dd>

<dd>買受人:{{ $invoice->buyer_name }}</dd>
<dd>買受人統一編號:{{ $invoice->buyer_ubn }}</dd>
<dd>買受人地址:{{ $invoice->buyer_address }}</dd>
<dd>隨機碼:{{ $invoice->random_number }}</dd>
<dd>總計金額（含稅）:{{ number_format($invoice->total_amt) }}</dd>

<dd>二維條碼:{{ $invoice->bar_code }}</dd>
<dd>QR CODE 左:{{ $invoice->qr_code_l }}</dd>
<dd>QR CODE 右:{{ $invoice->qr_code_r }}</dd>

<table class="table table-bordered text-center align-middle d-sm-table d-none text-nowrap">
    <tbody class="border-top-0">
        <tr class="table-light">
            <td class="col-2">品名</td>
            <td class="col-2">數量</td>
            <td class="col-2">單價</td>
            <td class="col-2">金額</td>
        </tr>
        @php
            $item_name_arr = explode('|', $invoice->item_name);
            $item_count_arr = explode('|', $invoice->item_count);
            $item_price_arr = explode('|', $invoice->item_price);
            $item_amt_arr = explode('|', $invoice->item_amt);
            $item_tax_type_arr = explode('|', $invoice->item_tax_type);
        @endphp
        @foreach($item_name_arr as $key => $value)
        <tr>
            <td>{{ $value }}</td>
            <td>{{ $item_count_arr[$key] }}</td>
            <td>{{ number_format($item_price_arr[$key]) }}</td>
            <td>{{ number_format($item_amt_arr[$key]) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<dd>銷售金額:{{ number_format($invoice->amt) }}</dd>
<dd>營業稅:{{ number_format($invoice->tax_amt) }}</dd>
<dd>發票稅別:{{ $invoice->tax_type == 1 ? '應稅' : '免稅' }}</dd>

<dd>發票備註:{{ $invoice->comment }}</dd>


