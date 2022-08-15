<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>列印支票本</title>
    <style>

    </style>
</head>
<body style="margin-top: 0px;">
@php
$page_count = ceil(count($data_list) / $item_per_page);
@endphp

@for ($i = 0; $i <= $page_count; $i++)
    <div style="position: absolute; left: 0; top: 0; width:100%; height:{{ $page_height }}px">
        <div style="text-align: center;">
            <div style="font-size: x-large; font-family:標楷體">喜鴻國際企業股份有限公司</div>
            <div>
                <span style="font-size: large;font-family:標楷體">應付票據簽收本</span>
                <span style="font-size: small;margin-left: 1.5rem;">列印日期：{{ date("Y/m/d") }}</span>
                <span style="font-size: small;margin-left: 1.5rem;">({{ $printer }})</span>
            </div>

            <div>
                <table width="710" style="font-size:small;text-align:left;border:0;margin: 0 auto;">
                    <thead>
                        <tr>
                            <th scope="col">票據號碼/金額</th>
                            <th scope="col">到期日/兌現日/狀態</th>
                            <th scope="col">廠商簽名</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data_list->skip($i * $item_per_page) as $value)
                        <tr style="height:{{ $row_height }}px">
                            <td>
                                <span>票號：{{ $value->cheque_ticket_number }}</span>
                                <span>金額：{{ $value->tw_price }}</span>
                                <span>團號：</span>
                                <span>付款單號：{{ $value->po_sn }}</span>
                            </td>
                            <td>
                                <span>開票：{{ $value->payment_date ? date('Y-m-d', strtotime($value->payment_date)) : '' }}</span>
                                <span>到期：{{ $value->cheque_due_date ? date('Y-m-d', strtotime($value->cheque_due_date)) : '' }}</span>
                                <span>兌現：{{ $value->cheque_cashing_date ? date('Y-m-d', strtotime($value->cheque_cashing_date)) : '' }}</span>
                                <span>狀態：{{ $value->cheque_status }}</span>
                            </td>
                            <td>{{ $value->po_target_name }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endfor
</body>
</html>
