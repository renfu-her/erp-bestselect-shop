<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>列印支票本</title>
    <style>
        * {
            font-family: "Nunito", "Noto Sans TC", sans-serif;
            position: relative;
        }
        @page {
            size: A4 portrait;
            /* A4 直向 */
            margin: 5mm auto;
            /* 邊界 */
        }
    </style>
</head>
<body style="margin-top: 0px;">
    @php
        $page_count = $data_list->count() ? ceil($data_list->count() / 8) : 1;
        $items = $data_list->get()->toArray();
    @endphp
    <div style="left: 0; top: 0; width:100%;">
        @for ($p = 0; $p < $page_count; $p++)
        <div style="page-break-inside: avoid;">
            <table width="650" cellpadding="3" cellspacing="0" border="0"
                style="font-size:11pt;text-align:left;margin:0 auto;border-collapse:collapse;">
                <thead style="text-align: center;">
                    <tr height="70">
                        <td colspan="3">
                            <div style="font-size:18pt;">喜鴻國際企業股份有限公司</div>
                            <div>
                                <span style="font-size: 16pt;">應付票據簽收本</span>
                                <span style="margin-left: 1.5rem;">
                                    列印日期：{{ date("Y/m/d") }} {{ $printer ? '('.$printer.')' : '　　　' }}
                                </span>
                            </div>
                        </td>
                    </tr>
                </thead>
            </table>
            <table width="650" cellpadding="3" cellspacing="0" border="1" bordercolor="#000000"
                style="font-size:11pt;text-align:left;margin:0 auto;border-collapse:collapse;">
                <thead style="text-align: center;">
                    <tr>
                        <th scope="col" width="35%">票據號碼/金額</th>
                        <th scope="col" width="30%">到期日/兌現日/狀態</th>
                        <th scope="col" width="35%">廠商簽名</th>
                    </tr>
                </thead>
                <tbody>
                    @for($i = 0; $i < 8; $i++)
                        @php
                            $i_key = $i + $p * 8;
                        @endphp
                        <tr height="120">
                            <td>
                                <div>票號：{{ array_key_exists($i_key, $items) ? $items[$i_key]->cheque_ticket_number : '' }}</div>
                                <div>金額：{{ array_key_exists($i_key, $items) ? $items[$i_key]->tw_price : '' }}</div>
                                <div>團號：</div>
                                <div>付款單號：{{ array_key_exists($i_key, $items) ? $items[$i_key]->po_sn : '' }}</div>
                            </td>
                            <td>
                                <div>開票：{{ array_key_exists($i_key, $items) ? ($items[$i_key]->payment_date ? date('Y-m-d', strtotime($items[$i_key]->payment_date)) : '') : '' }}</div>
                                <div>到期：{{ array_key_exists($i_key, $items) ? ($items[$i_key]->cheque_due_date ? date('Y-m-d', strtotime($items[$i_key]->cheque_due_date)) : '') : '' }}</div>
                                <div>兌現：{{ array_key_exists($i_key, $items) ? ($items[$i_key]->cheque_cashing_date ? date('Y-m-d', strtotime($items[$i_key]->cheque_cashing_date)) : '') : '' }}</div>
                                <div>狀態：{{ array_key_exists($i_key, $items) ? $items[$i_key]->cheque_status : '' }}</div>
                            </td>
                            <td style="vertical-align: top;">
                                {{ array_key_exists($i_key, $items) ? $items[$i_key]->po_target_name : '' }}
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
        @endfor
    </div>
</body>
</html>
