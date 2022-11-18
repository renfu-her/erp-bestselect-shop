<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@100;300;400;500;700&display=swap" rel="stylesheet">
    <title>電子發票證明聯</title>
    <style>
        * {
            font-family: "Noto Sans TC", sans-serif;
            position: relative;
            font-size: inherit;
        }
        @page {
            size: A4 portrait;
            /* A4 直向 */
            margin: 8mm auto;
            /* 邊界 */
        }

        .font {
            display: inline-block;
        }

        .print {
            margin-top: 2em;
            text-align: center;
        }

        .print button {
            font-size: 1.5rem;
            margin: 0 10px;
            font-family: "Nunito", "Noto Sans TC", sans-serif;
        }

        /* 強制換頁 */
        .-page {
            /* page-break-after: always; */
            margin: 0.3cm 0.2cm;
        }
        /* Firefox */
        @-moz-document url-prefix() {
            @page {
                margin: 12.7mm auto;
            }
            /* .-page {
                page-break-after: auto;
            } */
        }
        @supports (-moz-appearance:none) {
            @page {
                margin: 12.7mm auto;
            }
            /* .-page {
                page-break-after: auto;
            } */
        }

        .no-line > td {
            border-top: none;
            border-bottom: none;
        }

        .e-inv {
            width: 30%;
            min-width: 5.7cm;
            min-height: 9cm;
            border: 1px solid #000000;
        }
        .e-inv.main {
            margin-bottom: 0.2cm;
        }
        .e-inv > table {
            width: 5.7cm;
        }
        .e-inv.main > table:first-child {
            height: 9cm;
            text-align: center;
        }
        .e-inv.main > table:first-child > tbody > tr > td {
            padding-left: 0.5cm;
            padding-right: 0.5cm;
        }

        .-ff, .-ff * {
            font-family: '標楷體';
            font-size: 12px;
        }

        table.-detail td {
            padding-left: 8px;
            padding-right: 8px;
        }

        #code39-bar {
            width: 100%;
            margin-top: -5px;
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
        <div style="width:710px;margin:0 auto;">
            <div style="display:flex;flex-wrap:wrap;">
                @php
                    $item_name_arr = explode('|', $invoice->item_name);
                    $item_count_arr = explode('|', $invoice->item_count);
                    $item_price_arr = explode('|', $invoice->item_price);
                    $item_amt_arr = explode('|', $invoice->item_amt);
                    $item_tax_type_arr = explode('|', $invoice->item_tax_type);

                    $r_count = count($item_name_arr);
                    if($r_count < 9 ){
                        $total_page = 1;
                    } else {
                        $total_page = intval(ceil(($r_count - 8) / 15) + 1);
                    }
                @endphp
                {{-- 第一張 --}}
                <div class="-page">
                <div class="e-inv main">
                    {{-- 主內容 --}}
                    <table cellpadding="2" cellspacing="0">
                        <tbody>
                            <tr style="line-height:1;text-align:left;" class="-ff">
                                <td style="padding-top:0.5cm;vertical-align:bottom;font-size:0.8rem;">{{ $invoice->seller_title }}</td>
                            </tr>
                            <tr height="26" style="font-size:1.4rem;font-weight:500;line-height:1;">
                                <td>電子發票證明聯</td>
                            </tr>
                            <tr height="30" style="font-size:1.6rem;font-weight:500;line-height:1;">
                                <td>{{ $invoice->zh_period }}</td>
                            </tr>
                            <tr height="30" style="font-size:1.6rem;font-weight:600;line-height:1;">
                                <td>{{ substr($invoice->invoice_number, 0, 2) . '-' . substr($invoice->invoice_number, 2)}}</td>
                            </tr>
                            <tr height="50" style="text-align: left;" class="-ff">
                                <td>
                                    <div style="display:flex;justify-content:space-between;">
                                        <div>{{ date('Y-m-d H:i:s', strtotime($invoice->created_at)) }}</div>
                                        @if($invoice->category == 'B2B')
                                        <div>格式25</div>
                                        @endif
                                    </div>
                                    <div style="display:flex;">
                                        <div style="flex-basis: 50%;">隨機碼:{{ $invoice->random_number }}</div>
                                        <div>總計:{{ number_format($invoice->total_amt) }}</div>
                                    </div>
                                    <div style="display:flex;">
                                        <div style="flex-basis: 50%;">賣方:{{ $invoice->seller_ubn }}</div>
                                        @if($invoice->category == 'B2B')
                                        <div>買方:{{ $invoice->buyer_ubn }}</div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            <tr height="35">
                                <td>
                                    <div style="overflow-y:hidden;height:35px;">
                                        <svg id="code39-bar"></svg>
                                    </div>
                                </td>
                            </tr>
                            <tr height="75">
                                <td style="padding-bottom:0.5cm;">
                                    <div style="display:flex;justify-content:space-between;">
                                        <div id="qrcode-l" class="qrcode"></div>
                                        <div id="qrcode-r" class="qrcode"></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    {{-- 交易明細 --}}
                    <table cellpadding="2" cellspacing="0" class="-ff -detail">
                        <caption style="border-top: 1px dashed;padding:2px;">[交易明細]</caption>
                        <thead>
                            <tr>
                                <td width="40%">[品名/單價]</td>
                                <td width="30%">[數量]</td>
                                <td width="30%">[金額]</td>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- max: x10 --}}
                            @for ($i = 0; $i < min([10, $r_count]); $i++)
                                <tr height="38">
                                    <td colspan="3" style="padding-right: 30px;padding-left: 4px;">
                                        {{ $item_name_arr[$i] }}
                                    </td>
                                </tr>
                                <tr height="24" style="white-space: nowrap;">
                                    <td style="text-align:right;padding-right:25px;">{{ number_format($item_price_arr[$i]) }}</td>
                                    <td>{{ number_format($item_count_arr[$i]) }}</td>
                                    <td style="text-align:right;">{{ number_format($item_amt_arr[$i]) }}{{ $item_tax_type_arr[$i] == 1 ? ' TX' : '' }}</td>
                                </tr>
                            @endfor
                        </tbody>
                        <tfoot>
                            @if($total_page == 1)
                            <tr>
                                <td colspan="3" style="border-bottom: 1px dashed;padding:10px 8px;">
                                    @if($invoice->category == 'B2B')
                                    <div style="line-height: 1.6;">銷售額 ({{ $invoice->tax_type == 1 ? '應稅' : '免稅' }})：{{ number_format($invoice->amt) }}</div>
                                    <div style="line-height: 1.6;">稅　額：{{ number_format($invoice->tax_amt) }}</div>
                                    @endif
                                    <div style="line-height: 1.6;">總　計：{{ number_format($invoice->total_amt) }}</div>
                                    <div style="line-height: 1.6;">備　註：{{ $invoice->comment }}</div>
                                </td>
                            </tr>
                            @endif
                            <tr height="24" style="text-align:center;">
                                <td colspan="3">第1頁 / 總頁數{{ $total_page }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                </div>

                @for($p = 0; $p < ($total_page - 1); $p++)
                    {{-- 第二張+：交易明細 --}}
                    <div class="-page">
                    <div class="e-inv">
                        <table cellpadding="2" cellspacing="0" class="-ff -detail">
                            <caption style="padding:5px;">交易明細(續)</caption>
                            <thead>
                                <tr>
                                    <td colspan="3">{{ date('Y-m-d H:i:s', strtotime($invoice->created_at)) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <div style="display:flex;justify-content:space-between;">
                                            <div style="flex-basis: 50%;">賣方：{{ $invoice->seller_ubn }}</div>
                                            @if($invoice->category == 'B2B')
                                            <div>買方：{{ $invoice->buyer_ubn }}</div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="40%">[品名/單價]</td>
                                    <td width="30%">[數量]</td>
                                    <td width="30%">[金額]</td>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- max: x15 --}}
                                @for ($j = 0; $j < min([15, $r_count - (10 + $p * 15)]); $j++)
                                    @php
                                        $j_key = $j + 10 + $p * 15;
                                    @endphp
                                    <tr height="38">
                                        <td colspan="3" style="padding-right: 30px;padding-left: 4px;">
                                            {{ $item_name_arr[$j_key] }}
                                        </td>
                                    </tr>
                                    <tr height="24" style="white-space: nowrap;">
                                        <td style="text-align:right;padding-right:25px;">{{ number_format($item_price_arr[$j_key]) }}</td>
                                        <td>{{ number_format($item_count_arr[$j_key]) }}</td>
                                        <td style="text-align:right;">{{ number_format($item_amt_arr[$j_key]) }}{{ $item_tax_type_arr[$j_key] == 1 ? ' TX' : '' }}</td>
                                    </tr>
                                @endfor
                            </tbody>
                            <tfoot>
                                {{-- 最後一張 --}}
                                @if($p + 2 == $total_page)
                                <tr>
                                    <td colspan="3" style="border-bottom: 1px dashed;padding:10px 8px;">
                                        @if($invoice->category == 'B2B')
                                        <div style="line-height: 1.6;">銷售額 ({{ $invoice->tax_type == 1 ? '應稅' : '免稅' }})：{{ number_format($invoice->amt) }}</div>
                                        <div style="line-height: 1.6;">稅　額：{{ number_format($invoice->tax_amt) }}</div>
                                        @endif
                                        <div style="line-height: 1.6;">總　計：{{ number_format($invoice->total_amt) }}</div>
                                        <div style="line-height: 1.6;">備　註：{{ $invoice->comment }}</div>
                                    </td>
                                </tr>
                                @endif
                                <tr height="24" style="text-align:center;vertical-align:bottom;">
                                    <td colspan="3">第{{ $p + 2 }}頁 / 總頁數{{ $total_page }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    </div>
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

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/barcodes/JsBarcode.code39.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
<script>
    const barcode = '{!! $invoice->bar_code !!}';
    const qrcodeL = '{!! $invoice->qr_code_l !!}';
    const qrcodeR = '{!! $invoice->qr_code_r !!}';

    // 二維條碼
    JsBarcode('#code39-bar', barcode, {
        format: 'CODE39',
        width: 1,
        height: 40,
        margin: 0,
        displayValue: false
    });

    // QR CODE opt
    const qr_opt = {
        width: 75,
        height: 75
    };
    // QR CODE 左
    $('#qrcode-l').qrcode({
        ...qr_opt,
        text: qrcodeL
    });
    // QR CODE 右
    $('#qrcode-r').qrcode({
        ...qr_opt,
        text: qrcodeR
    });
</script>
