<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>採購進貨單</title>
    @switch($type)
        @case('A4')
            <style>
            @page {
                size: A4 portrait;
                /* A4 直向 */
                margin: 5mm auto;
                /* 邊界 */
            }
            </style>
            @break
        @case('M1')
        @default
            <style>
            @page {
                size: 214.9mm 140mm;
                margin: 0;
                /* 邊界 */
            }
            </style>
    @endswitch
    <style>
        * {
            font-family: "Nunito", "Noto Sans TC", sans-serif;
            position: relative;
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
            <div style="font-size: x-large; font-family:標楷體">喜鴻國際企業股份有限公司</div>
            <div style="font-size: small;">
                <span>地址：台北市中山區松江路148號6樓之2</span>
                <span style="margin-left: 1.5rem;">電話：02-25637600</span>
                <span style="margin-left: 1.5rem;">傳真：02-25711377</span>
            </div>

            <div style="font-size: x-large; font-family:標楷體">進貨單</div>
            <div>
                <table width="710" style="font-size:12pt;text-align:left;border:0;margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td width="50%">進貨單號：{{ $purchaseData->purchase_sn }}</td>
                            <td width="50%">進貨日期：{{ $purchaseData->scheduled_date }}</td>
                        </tr>
                        <tr>
                            <td>廠商名稱：{{ $purchaseData->supplier_name }}</td>
                            <td>採購人員：{{ $purchaseData->user_name }}</td>
                        </tr>
                        <tr>
                            <td>聯絡人員：</td>
                            <td>統一編號：</td>
                        </tr>
                        <tr>
                            <td>發票號碼：{{ $purchaseData->invoice_num }}</td>
                            <td>發票日期：{{ $purchaseData->invoice_date }}</td>
                        </tr>
                        <tr>
                            <td>進貨地址：</td>
                            <td>課稅別　：{{ $purchaseData->has_tax === 1 ? '應稅' : '免稅' }}</td>
                        </tr>
                        <tr>
                            <td>審核人員：{{ $purchaseData->audit_user_name }}</td>
                        </tr>
                    </tbody>
                </table>

                <table width="710" cellpadding="2" cellspacing="0" border="1" bordercolor="#000000"
                    style="font-size:12pt;margin:0 auto;border-collapse:collapse;">
                    <thead style="text-align: center;">
                        <tr height="24">
                            <th scope="col" width="7%">序號</th>
                            <th scope="col" width="40%">品名-規格</th>
                            <th scope="col" width="7%">數量</th>
                            <th scope="col" width="10%">單價</th>
                            <th scope="col" width="16%">金額</th>
                            <th scope="col" width="20%">說明</th>
                        </tr>
                    </thead>
                    <tbody style="text-align: left;">
                        @php
                            $total = 0;
                        @endphp
                        @foreach ($purchaseItemData as $key => $item)
                            <tr height="24">
                                <td style="text-align: center;" scope="row">{{ $key + 1 }}</td>
                                <td>{{ $item->title }}</td>
                                <td style="text-align: right;">{{ $item->num }}</td>
                                <td style="text-align: right;">{{ number_format($item->single_price) }}</td>
                                <td style="text-align: right;">{{ number_format($item->price) }}</td>
                                <td>{{ $item->memo ?? '' }}</td>
                            </tr>
                            @php
                                $total += (int)$item->price;
                            @endphp
                        @endforeach
                        
                        {{-- 最少 7 行 --}}
                        @for ($i = count($purchaseItemData); $i < 7; $i++)
                            <tr height="24">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endfor
                        <tr height="70">
                            <td style="vertical-align:top;" colspan="4">
                                備註：
                            </td>
                            <td>
                                <div>合計：{{ number_format($total) }}</div>
                                <div>稅金：0</div>
                                <div>總計：{{ number_format($total) }}</div>
                            </td>
                            <td></td>
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
