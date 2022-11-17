<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>電子發票證明聯</title>
    <style>
        * {
            font-family: "標楷體", "Noto Sans TC", sans-serif;
            position: relative;
            font-size: inherit;
        }
        @page {
            size: A4 portrait;
            /* A4 直向 */
            margin: 10mm auto;
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
            font-family: "Nunito", "Noto Sans TC", sans-serif;
        }

        .-page {
            page-break-inside: avoid;
            page-break-after: always;
        }

        .no-line > td {
            border-top: none;
            border-bottom: none;
        }

        .inside-table {
            display: table;
        }
        .inside-table > * {
            display: table-cell;
            vertical-align: middle;
        }
        .inside-table > *:not(:last-child) {
            border-right: 1px solid;
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
            <div style="line-height: 1.5rem;">{{ $invoice->seller_title }}</div>
            <div style="line-height: 1.5rem;font-weight: bold;">電子發票證明聯</div>
            <div style="line-height: 1.5rem;">{{ date('Y-m-d', strtotime($invoice->created_at)) }}</div>

            <div>
                @php
                    $item_name_arr = explode('|', $invoice->item_name);
                    $item_count_arr = explode('|', $invoice->item_count);
                    $item_price_arr = explode('|', $invoice->item_price);
                    $item_amt_arr = explode('|', $invoice->item_amt);
                    $item_tax_type_arr = explode('|', $invoice->item_tax_type);

                    $r_count = count($item_name_arr);
                    if($r_count < 11 ){
                        $total_page = 1;
                    } else {
                        $total_page = intval(ceil(($r_count - 10) / 14) + 1);
                    }
                @endphp

                <table width="710" style="font-size:12pt;text-align:left;border:0;margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td width="65%">發票號碼：{{ $invoice->invoice_number }}</td>
                            <td width="35%">格　　式：25</td>
                        </tr>
                        <tr>
                            <td>買　　方：{{ $invoice->buyer_name }}</td>
                            <td>隨 機 碼：{{ $invoice->random_number }}</td>
                        </tr>
                        <tr>
                            <td>統一編號：{{ $invoice->buyer_ubn }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div style="text-align: left;">地　　址：{{ $invoice->buyer_address }}</div>
                                <div style="text-align: end">第1頁 / 共{{ $total_page }}頁</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table width="710" cellpadding="2" cellspacing="0" border="1" bordercolor="#000000"
                    style="font-size:12pt;margin:20px auto 0;border-collapse:collapse;">
                    <thead style="text-align: center;">
                        <tr height="24">
                            <td scope="col" width="22%">品名</td>
                            <td scope="col" width="10%">數量</td>
                            <td scope="col" width="12%">單價</td>
                            <td scope="col" width="12%">金額</td>
                            <td scope="col" width="22%">備註</td>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- x10 --}}
                        @for ($i = 0; $i < 10; $i++)
                            <tr height="62" class="no-line">
                                <td style="text-align: left;">{{ array_key_exists($i, $item_name_arr) ? $item_name_arr[$i] : '' }}</td>
                                <td style="text-align: right;">{{ array_key_exists($i, $item_name_arr) ? number_format($item_count_arr[$i]) : '' }}</td>
                                <td style="text-align: right;">{{ array_key_exists($i, $item_name_arr) ? number_format($item_price_arr[$i]) : '' }}</td>
                                <td style="text-align: right;">{{ array_key_exists($i, $item_name_arr) ? number_format($item_amt_arr[$i]) : '' }}</td>
                                <td style="text-align: left;"></td>
                            </tr>
                        @endfor
                    </tbody>
                    <tfoot style="text-align: left;vertical-align:middle;">
                        <tr height="40">
                            <td colspan="3">銷售額合計</td>
                            <td style="text-align: right;">{{ number_format($invoice->amt) }}</td>
                            <td>
                                <div>營業人蓋統一發票專用章</div>
                                <div style="font-size: small;">（已條列營業人資料者得免蓋章）</div>
                            </td>
                        </tr>
                        <tr height="30">
                            <td colspan="3" style="padding: 0 2px;">
                                <div class="inside-table" style="width:100%;height:29px;text-align: center;">
                                    <div style="width:22%;text-align: left;">營業稅</div>
                                    <div style="width:13%;font-size:0.9rem;">應稅</div>
                                    <div style="width:13%;">{{ $invoice->tax_type === '1' ? 'V' : '' }}</div>
                                    <div style="width:13%;font-size:0.9rem;">零稅率</div>
                                    <div style="width:13%;"></div>
                                    <div style="width:13%;font-size:0.9rem;">免稅</div>
                                    <div style="width:13%;">{{ $invoice->tax_type !== '1' ? 'V' : '' }}</div>
                                </div>
                            </td>
                            <td style="text-align: right;">{{ number_format($invoice->tax_amt) }}</td>
                            <td rowspan="3" style="vertical-align:top;">
                                <div>賣　　方：{{ $invoice->seller_title }}</div>
                                <div>統一編號：{{ $invoice->seller_ubn }}</div>
                                <div>地　　址：{{ $invoice->seller_address }}</div>
                            </td>
                        </tr>
                        <tr height="30">
                            <td colspan="3">總計</td>
                            <td style="text-align: right;">{{ number_format($invoice->total_amt) }}</td>
                        </tr>
                        <tr height="62" style="vertical-align:top;">
                            <td colspan="4">
                                <div style="display:flex;">
                                    <div>
                                        <div>總計新臺幣</div>
                                        <div style="font-size: small;">（中文大寫）</div>
                                    </div>
                                    <div style="padding: 10px;flex: 1;">{{ $invoice->zh_total_amt }}</div>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>

                @for($p = 0; $p < ($total_page - 1); $p++)
                    {{-- 若還有次頁 --}}
                    <div style="width:710px; margin: 0 auto 20px;">
                        <div style="text-align:left;padding-left:10px;font-size:1.2rem;">（次頁或反面續）</div>
                    </div>

                    {{-- 第二頁+ --}}
                    <table width="710" class="-page" style="font-size:12pt;text-align:center;border:0;margin: 0 auto;">
                        <thead>
                            <tr>
                                <td>{{ $invoice->seller_title }}</td>
                            </tr>
                            <tr>
                                <td>{{ date('Y-m-d', strtotime($invoice->created_at)) }}</td>
                            </tr>
                            <tr>
                                <td>
                                    <div style="text-align: left;">發票號碼：{{ $invoice->invoice_number }}</div>
                                    <div style="text-align: end">第{{ $p + 2 }}頁 / 共{{ $total_page }}頁</div>
                                </td>
                            </tr>
                        </thead>
                    </table>
                    <table width="710" cellpadding="2" cellspacing="0" border="1" bordercolor="#000000"
                        style="font-size:12pt;margin:0 auto;border-collapse:collapse;">
                        <thead style="text-align: center;">
                            <tr height="24">
                                <td scope="col" width="22%">品名</td>
                                <td scope="col" width="10%">數量</td>
                                <td scope="col" width="12%">單價</td>
                                <td scope="col" width="12%">金額</td>
                                <td scope="col" width="22%">備註</td>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- x14 --}}
                            @for ($j = 0; $j < 14; $j++)
                                @php
                                    $j_key = $j + 10 + $p * 14;
                                @endphp
                                <tr height="62" class="no-line">
                                    <td style="text-align: left;">{{ array_key_exists($j_key, $item_name_arr) ? $item_name_arr[$j_key] : '' }}</td>
                                    <td style="text-align: right;">{{ array_key_exists($j_key, $item_name_arr) ? number_format($item_count_arr[$j_key]) : '' }}</td>
                                    <td style="text-align: right;">{{ array_key_exists($j_key, $item_name_arr) ? number_format($item_price_arr[$j_key]) : '' }}</td>
                                    <td style="text-align: right;">{{ array_key_exists($j_key, $item_name_arr) ? number_format($item_amt_arr[$j_key]) : '' }}</td>
                                    <td style="text-align: left;"></td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                @endfor

            </div>
            <div class="print">
                <button type="button" onclick="javascript:window.print();">我要列印</button>
                <button type="button" onclick="javascript:window.close();">關閉視窗</button>
            </div>
        </div>
    </div>
</body>

</html>
