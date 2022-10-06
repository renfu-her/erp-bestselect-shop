<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>轉帳傳票</title>
    <style>
        * {
            font-family: "Nunito", "Noto Sans TC", sans-serif;
            position: relative;
        }
        @page {
            margin: 5mm 0 0;
            /* 邊界 */
        }

        .font {
            display: inline-block;
        }
        .print {
            margin-top: 2em;
        }
        .print button {
            font-size: 1.5rem;
            margin: 0 10px;
        }
        @media print {
            * {
                font-weight: lighter;
            }
            a,
            a:active,
            a:visited {
                color: #000000;
                text-decoration: none;
            }
            .print {
                display: none;
            }
        }
    </style>
</head>
<body style="margin-top: 0px;">
    <div style="position: absolute; left: 0; top: 0; width:100%">
        <div style="text-align: center;">
            <div style="font-size: x-large; font-family:標楷體">{{ $voucher->company_name }}</div>
            <div style="font-size: x-large; font-family:標楷體">轉帳傳票</div>
            <div style="font-size: x-large; font-family:標楷體">中華民國 {{ date('Y', strtotime($voucher->tv_voucher_date)) - 1911 }} 年 {{ date('m', strtotime($voucher->tv_voucher_date)) }} 月 {{ date('d', strtotime($voucher->tv_voucher_date)) }} 日</div>

            <div>
                <table width="710" style="font-size:small;text-align:left;border:0;margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td width="50%">傳票編號：{{ $day_emd_item ? $day_emd_item->sn : '' }}</td>
                            <td width="50%" style="text-align: right;">單號：{{ $voucher->tv_sn }}</td>
                        </tr>
                    </tbody>
                </table>

                <hr width="710" style="margin: .5rem auto;">
                <table width="710" cellpadding="5" style="font-size:small;margin:0 auto;border-collapse:collapse;">
                    <thead style="text-align: left;">
                        <tr height="24">
                            <th scope="col" width="32%" style="padding-bottom:7px;">會計科目</th>
                            <th scope="col" width="32%" style="padding-bottom:7px;">摘要</th>
                            <th scope="col" width="8%" style="padding-bottom:7px;">幣別</th>
                            <th scope="col" width="8%" style="padding-bottom:7px;text-align: right;">匯率</th>
                            <th scope="col" width="10%" style="padding-bottom:7px;text-align: right;">借方</th>
                            <th scope="col" width="10%" style="padding-bottom:7px;text-align: right;">貸方</th>
                        </tr>
                    </thead>
                    <tbody style="text-align: left;">
                        @if($voucher->tv_items)
                        @foreach(json_decode($voucher->tv_items) as $value)
                        <tr>
                            <td>{{ $value->grade_code . ' ' . $value->grade_name }}</td>
                            <td>{{ $value->summary }}</td>
                            <td>{{ $value->currency_name }}</td>
                            <td style="text-align: right;">{{ $value->rate }}</td>
                            <td style="text-align: right;">{{ $value->debit_credit_code == 'debit' ? number_format($value->final_price, 2) : '' }}</td>
                            <td style="text-align: right;">{{ $value->debit_credit_code == 'credit' ? number_format($value->final_price, 2) : '' }}</td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
                <hr width="710" style="margin: .5rem auto;">
                <table width="710" cellpadding="1"
                    style="font-size:small;margin:0 auto;border-collapse:collapse;">
                    <thead>
                        <tr height="24">
                            <td width="32%">合計</td>
                            <td width="32%" style="text-align: right;">{{ $voucher->tv_debit_price != $voucher->tv_credit_price ? '（借貸不平）' : '' }}</td>
                            <td width="8%"></td>
                            <td width="8%"></td>
                            <td width="10%" style="text-align: right;">{{ number_format($voucher->tv_debit_price) }}</td>
                            <td width="10%" style="text-align: right;">{{ number_format($voucher->tv_credit_price) }}</td>
                        </tr>
                    </thead>
                    <tbody style="text-align: left;"></tbody>
                </table>
                <hr width="710" style="margin: .5rem auto;">
                <table width="710" style="font-size:small;text-align:left;border:0;margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td width="25%">主管：</td>
                            <td width="25%">會計：{{ $voucher->accountant_name }}</td>
                            <td width="25%">承辦人：{{ $voucher->creator_name }}</td>
                        </tr>
                  </tbody>
                </table>
            </div>
            <div class="print">
                <button type="button" onclick="javascript:window.print();">我要列印</button>
                <button type="button" onclick="javascript:window.close();">關閉視窗</button>
            </div>
        </div>
    </div>
</body>
</html>
