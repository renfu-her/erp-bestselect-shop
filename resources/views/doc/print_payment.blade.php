<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>收款單</title>
    <style>
        * {
            font-family: "Nunito", "Noto Sans TC", sans-serif;
            position: relative;
        }
        @page {
            size: 214.9mm 140mm;
            /* A4 直向 */
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
            <div style="font-size: small; margin: 1px auto;">
                <span>地址：台北市中山區松江路148號6樓之2</span>
                <span style="margin-left: 1.5rem;">電話：02-25637600</span>
                <span style="margin-left: 1.5rem;">傳真：02-25711377</span>
            </div>

            <div style="font-size: x-large; font-family:標楷體">
                尾款付款單
            </div>
            <hr width="710" style="margin: .5rem auto;">
            <div>
                <table width="710" style="font-size:small;text-align:left;border:0;margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td width="50%">付款單號：{{ 'ISG2208160002' }}</td>
                            <td width="50%">製表日期：{{ date('Y/m/d', strtotime('2022-08-05')) }}</td>
                        </tr>
                        <tr>
                            <td>單據編號：{{ 'B2206220001' }}</td>
                            <td>
                                @if (false)
                                    付款日期：{{ '' }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>支付對象：{{ '無廠商' }}</td>
                            <td>承辦人：{{ '烏梅' }}</td>
                        </tr>
                    </tbody>
                </table>
                <hr width="710" style="margin: .5rem auto;">
                <table width="710" cellpadding="5"
                    style="font-size:small;margin:0 auto;border-collapse:collapse;">
                    <thead style="text-align: left;">
                        <tr height="24">
                            <th scope="col" width="40%" style="padding-bottom:7px;">付款項目</th>
                            <th scope="col" width="8%" style="padding-bottom:7px;text-align: right;">數量</th>
                            <th scope="col" width="8%" style="padding-bottom:7px;text-align: right;">單價</th>
                            <th scope="col" width="10%" style="padding-bottom:7px;text-align: right;">應付金額</th>
                            <th scope="col" width="34%" style="padding-bottom:7px;">備註</th>
                        </tr>
                    </thead>
                    <tbody style="text-align: left;">
                        {{-- @foreach ($order_list_data as $value) --}}
                            <tr height="24" style="border-bottom: 0px solid #dee2e6;">
                                <td>商品存貨-測試商品-L（負責人：Hans）</td>
                                <td style="text-align: right;">{{ number_format(10) }}</td>
                                <td style="text-align: right;">{{ number_format(10.00) }}</td>
                                <td style="text-align: right;">{{ number_format(100) }}</td>
                                <td>test memo</td>
                            </tr>
                        {{-- @endforeach --}}

                        <tr height="24" style="border-bottom: 0px solid #dee2e6;">
                            <td>商品存貨-測試商品-X（負責人：Hans）</td>
                            <td style="text-align: right;">{{ number_format(10) }}</td>
                            <td style="text-align: right;">{{ number_format(10.00) }}</td>
                            <td style="text-align: right;">{{ number_format(100) }}</td>
                            <td>test memo</td>
                        </tr>
                        <tr height="24" style="border-bottom: 0px solid #dee2e6;">
                            <td>5231 物流費用- 物流費用</td>
                            <td style="text-align: right;"></td>
                            <td style="text-align: right;"></td>
                            <td style="text-align: right;">{{ number_format(80) }}</td>
                            <td>物流備註666</td>
                        </tr>
                    </tbody>
                </table>
                <hr width="710" style="margin: .5rem auto;">
                <table width="710" cellpadding="1"
                    style="font-size:small;margin:0 auto;border-collapse:collapse;">
                    <thead>
                        <tr height="24">
                            <td width="20%">合　　計：</td>
                            <td width="36%" style="text-align: right;">（{{ '貳佰捌拾元整' }}）</td>
                            <td width="10%" style="text-align: right;">{{ number_format(280) }}</td>
                            <td width="34%"></td>
                        </tr>
                    </thead>
                    <tbody style="text-align: left;">
                        <tr height="22">
                            <td colspan="4">1101 現金 280 （現金 - 現金 - ）</td>
                        </tr>
                    </tbody>
                </table>
                <hr width="710" style="margin: .5rem auto;">
                <table width="710" style="font-size:small;text-align:left;border:0;margin: 0 auto;">                    
                    <tbody>
                        <tr>
                            <td width="25%">財務主管：</td>                     
                            <td width="25%">會計：</td>
                            <td width="25%">商品主管：</td>
                            <td width="25%">商品負責人：Hans</td>
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
