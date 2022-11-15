<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>
        @switch($type)
            @case('sales')
                銷貨單明細
            @break

            @case('ship')
                出貨單明細
            @break

            @default
                明細列印
        @endswitch
    </title>
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

            <div style="font-size: x-large; font-family:標楷體">
                @switch($type)
                    @case('sales')
                        銷貨單明細
                    @break

                    @case('ship')
                        出貨單明細
                    @break
                @endswitch
            </div>
            <div>
                <table width="710" style="font-size:12pt;text-align:left;border:0;margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td width="40%">銷貨單號：{{ $subOrders->sn }}</td>
                            <td width="60%">發票類型：</td>
                        </tr>
                        <tr>
                            @if ($type === 'sales')
                                <td>訂購人：{{ $order->ord_name }} {{ $order->ord_phone }}</td>
                            @endif
                            <td>寄件人：{{ $order->sed_name }} {{ $order->sed_phone }}</td>
                        </tr>
                        <tr>
                            <td>收件人：{{ $order->rec_name }} {{ $order->rec_phone }}</td>
                            <td>送貨地址：{{ $order->rec_zipcode ? $order->rec_zipcode . ' ' : '' }}{{ $order->rec_address }}
                            </td>
                        </tr>
                        <tr>
                            <td>取貨方式：{{ $subOrders->ship_event }}</td>
                            <td>統一編號：</td>
                        </tr>
                    </tbody>
                </table>

                <table width="710" cellpadding="2" cellspacing="0" border="1" bordercolor="#000000"
                    style="font-size:12pt;margin:0 auto;border-collapse:collapse;">
                    <thead style="text-align: center;">
                        <tr height="24">
                            <th scope="col" width="7%">序號</th>
                            <th scope="col" width="{{ $type === 'sales' ? '40%' : '' }}">品名-規格</th>
                            <th scope="col" width="8%">數量</th>
                            @if ($type === 'sales')
                                <th scope="col" width="10%">單價</th>
                                <th scope="col" width="12%">金額</th>
                            @endif
                            <th scope="col" width="23%">說明</th>
                        </tr>
                    </thead>
                    <tbody style="text-align: left;">
                        @foreach ($subOrders->items as $key => $item)
                            <tr height="24">
                                <td style="text-align: center;" scope="row">{{ $key + 1 }}</td>
                                <td>{{ $item->product_title }}</td>
                                <td style="text-align: right;">{{ $item->qty }}</td>
                                @if ($type === 'sales')
                                    <td style="text-align: right;">{{ number_format($item->price) }}</td>
                                    <td style="text-align: right;">{{ number_format($item->total_price) }}</td>
                                @endif
                                <td></td>
                            </tr>
                        @endforeach

                        {{-- 最少 8 行 --}}
                        @for ($i = count($subOrders->items); $i < 8; $i++)
                            <tr height="24">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                @if ($type === 'sales')
                                    <td></td>
                                    <td></td>
                                @endif
                            </tr>
                        @endfor
                        <tr height="70">
                            <td style="vertical-align:top;" colspan="4">
                                備註：{{ $order->note }}
                                <div style="font-size: small;margin-top:5px;">{{ $user->name }}
                                    {{ date('Y/m/d H:i:s') }}</div>
                                @if ($type === 'sales')
                                    銷貨備註：{{ $subOrders->note }}
                                @endif
                            </td>
                            @if ($type === 'sales')
                                <td style="text-align: right;">總計 {{ number_format($subOrders->total_price) }}</td>
                                <td style="vertical-align:top;text-align: center;">客戶簽章</td>
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="print">
                <button type="button" onclick="printDoc('M1')">列印中一刀</button>
                <button type="button" onclick="printDoc('A4')">列印A4</button>
                <button type="button" onclick="javascript:window.close();">關閉視窗</button>
            </div>
        </div>
    </div>
</body>
<script>
    function printDoc(type) {
        switch (type) {
            case 'A4':
                document.head.insertAdjacentHTML('beforeend', `
                <style>
                    @page {
                        size: A4 portrait;
                        margin: 5mm auto;
                    }
                </style>
                `);
                break;
            case 'M1':
            default:
                document.head.insertAdjacentHTML('beforeend', `
                <style>
                    @page {
                        size: A5 landscape;
                        margin: 2mm 0 0;
                    }
                </style>
                `);
                break;
        }
        
        window.print();
    }
</script>
</html>
