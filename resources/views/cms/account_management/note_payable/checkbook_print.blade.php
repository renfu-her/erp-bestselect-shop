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
<div style="left: 0; top: 0; width:100%;">
    <div>
        <table width="650" cellpadding="3" cellspacing="0" border="1" bordercolor="#000000"
            style="font-size:11pt;text-align:left;margin:0 auto;border-collapse:collapse;">
            <thead style="text-align: center;">
                <tr height="70">
                    <td colspan="3" style="border-color: #FFF #FFF #000;">
                        <div style="font-size:18pt;">喜鴻國際企業股份有限公司</div>
                        <div>
                            <span style="font-size: 16pt;">應付票據簽收本</span>
                            <span style="margin-left: 1.5rem;">
                                列印日期：{{ date("Y/m/d") }} {{ $printer ? '('.$printer.')' : '　　　' }}
                            </span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="col" width="35%">票據號碼/金額</th>
                    <th scope="col" width="30%">到期日/兌現日/狀態</th>
                    <th scope="col" width="35%">廠商簽名</th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 0; $i < 30; $i++)
                    <tr height="120" style="page-break-inside: avoid;">
                        <td>
                            <div>票號：YA7626803</div>
                            <div>金額：8533.00</div>
                            <div>團號：</div>
                            <div>付款單號：ISG0000{{ $i }}</div>
                        </td>
                        <td>
                            <div>開票：2020-11-17</div>
                            <div>到期：2020-11-18</div>
                            <div>兌現：2020-11-18</div>
                            <div>狀態：</div>
                        </td>
                        <td style="vertical-align: top;">
                            楊勝彬會計事務所(佑誠會計事務所)
                        </td>
                    </tr>
                @endfor
                {{-- @foreach($data_list->paginate(8, ['*'], 'page', $i + 1) as $value)
                <tr>
                    <td>
                        <span>票號：{{ $value->cheque_ticket_number }}</span>
                        <br>
                        <span>金額：{{ $value->tw_price }}</span>
                        <br>
                        <span>團號：</span>
                        <br>
                        <span>付款單號：{{ $value->po_sn }}</span>
                    </td>
                    <td>
                        <span>開票：{{ $value->payment_date ? date('Y-m-d', strtotime($value->payment_date)) : '' }}</span>
                        <br>
                        <span>到期：{{ $value->cheque_due_date ? date('Y-m-d', strtotime($value->cheque_due_date)) : '' }}</span>
                        <br>
                        <span>兌現：{{ $value->cheque_cashing_date ? date('Y-m-d', strtotime($value->cheque_cashing_date)) : '' }}</span>
                        <br>
                        <span>狀態：{{ $value->cheque_status }}</span>
                    </td>
                    <td>{{ $value->po_target_name }}</td>
                </tr>
                @endforeach --}}
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
