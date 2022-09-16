<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>寄倉出貨單明細列印</title>
    <style>
        * {
            font-family: "Nunito", "Noto Sans TC", sans-serif;
            position: relative;
        }
        @page {
            size: 214.9mm 140mm;
            margin: 0;
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
            <div style="font-size: x-large; font-family:標楷體">喜鴻國際企業股份有限公司</div>
            <div style="font-size: small;">
                <span>地址：台北市中山區松江路148號6樓之2</span>
                <span style="margin-left: 1.5rem;">電話：02-25637600</span>
                <span style="margin-left: 1.5rem;">傳真：02-25711377</span>
            </div>

            <div style="font-size: x-large; font-family:標楷體">
                寄倉出貨明細
            </div>
            <div>
                <table width="710" style="font-size:12pt;text-align:left;border:0;margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td width="40%">銷貨單號：</td>
                            <td width="60%">寄倉單位：</td>
                        </tr>
                        <tr>
                            <td>收件人：</td>
                            <td>送貨地址：</td>
                        </tr>
                        <tr>
                            <td>出貨日期：{{ date('Y-m-d', strtotime()) }}</td>
                            <td>列印日期：{{ date('Y-m-d') }} {{ $user->name }}</td>
                        </tr>
                    </tbody>
                </table>

                <table width="710" cellpadding="2" cellspacing="0" border="1" bordercolor="#000000"
                    style="font-size:12pt;margin:0 auto;border-collapse:collapse;">
                    <thead style="text-align: center;">
                        <tr height="24">
                            <th scope="col" width="7%">序號</th>
                            <th scope="col" width="40%">品名規格</th>
                            <th scope="col" width="8%">數量</th>
                            <th scope="col" width="23%">說明</th>
                        </tr>
                    </thead>
                    <tbody style="text-align: left;">
                        @foreach ($array as $key => $item)
                            <tr height="24">
                                <td style="text-align: center;" scope="row">{{ $key + 1 }}</td>
                                <td></td>
                                <td style="text-align: right;"></td>
                                <td></td>
                            </tr>
                        @endforeach
                        
                        {{-- 最少 8 行 --}}
                        @for ($i = count($array); $i < 8; $i++)
                            <tr height="24">
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
