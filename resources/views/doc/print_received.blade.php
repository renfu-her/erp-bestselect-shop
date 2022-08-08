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
            <div style="font-size: small;">
                <span>地址：台北市中山區松江路148號6樓之2</span>
                <span style="margin-left: 1.5rem;">電話：02-25637600</span>
                <span style="margin-left: 1.5rem;">傳真：02-25711377</span>
            </div>

            <div style="font-size: x-large; font-family:標楷體">
                收　款　單
            </div>
            <hr width="710" style="margin: .5rem auto;">
            <div>
                <table width="710" style="font-size:small;text-align:left;border:0;margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td width="50%">客戶：<span style="font-size:medium;">{{ '烏梅' }}</span>　　台鑒</td>
                            <td width="50%">地址：{{ '' }}</td>
                        </tr>
                        <tr>
                            <td>電話：{{ '0987654321' }}</td>
                            <td>傳真：{{ '' }}</td>
                        </tr>
                    </tbody>
                </table>
                <hr width="710" style="margin: .5rem auto;">
                <table width="710" style="font-size:small;text-align:left;border:0;margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td width="50%">收款單號：{{ 'MSG2208050001' }}</td>
                            <td width="50%">製表日期：{{ date('Y/m/d', strtotime('2022-08-05')) }}</td>
                        </tr>
                        <tr>
                            <td>訂單流水號：<a href="{{ Route('cms.order.detail', ['id' => '$order->id'], true) }}">{{ 'O202207190001' }}</a></td>
                            <td>入帳日期：{{ '' }}</td>
                        </tr>
                        <tr>
                            <td>收款對象：</td>
                            <td>承辦人：{{ '烏梅' }}</td>
                        </tr>
                    </tbody>
                </table>
                <hr width="710" style="margin: .5rem auto;">
                <table width="710" cellpadding="5"
                    style="font-size:small;margin:0 auto;border-collapse:collapse;">
                    <thead style="text-align: left;">
                        <tr height="24">
                            <th scope="col" width="40%" style="padding-bottom:7px;">收款項目</th>
                            <th scope="col" width="8%" style="padding-bottom:7px;text-align: right;">數量</th>
                            <th scope="col" width="8%" style="padding-bottom:7px;text-align: right;">單價</th>
                            <th scope="col" width="10%" style="padding-bottom:7px;text-align: right;">應收金額</th>
                            <th scope="col" width="34%" style="padding-bottom:7px;">備註</th>
                        </tr>
                    </thead>
                    <tbody style="text-align: left;">
                        {{-- @foreach ($order_list_data as $value) --}}
                            <tr height="24" style="border-bottom: 0px solid #dee2e6;">
                                <td>4101 銷貨收入 --- 組合包商品-三包組（咖啡．候機室-新竹2號店 - 自取）（230 * 1）</td>
                                <td style="text-align: right;">{{ number_format(1) }}</td>
                                <td style="text-align: right;">{{ number_format(230) }}</td>
                                <td style="text-align: right;">{{ number_format(230) }}</td>
                                <td><a href="{{ Route('cms.order.detail', ['id' => 1], true) }}">{{ 'O202207190001' }}</a> 應稅 test back</td>
                            </tr>
                        {{-- @endforeach --}}

                        <tr height="24" style="border-bottom: 0px solid #dee2e6;">
                            <td>4101 銷貨收入 --- Group-GGGG（集運本倉 - 自取）（0 * 1）</td>
                            <td style="text-align: right;">{{ number_format(1) }}</td>
                            <td style="text-align: right;">{{ number_format(0) }}</td>
                            <td style="text-align: right;">{{ number_format(0) }}</td>
                            <td><a href="{{ Route('cms.order.detail', ['id' => 1], true) }}">{{ 'O202207190001' }}</a> 應稅 test back</td>
                        </tr>
                        <tr height="24" style="border-bottom: 0px solid #dee2e6;">
                            <td>4107 - 全館活動折扣 - 全館85折</td>
                            <td style="text-align: right;">{{ number_format(1) }}</td>
                            <td style="text-align: right;">{{ number_format(-35) }}</td>
                            <td style="text-align: right;">{{ number_format(-35) }}</td>
                            <td><a href="{{ Route('cms.order.detail', ['id' => 1], true) }}">{{ 'O202207190001' }}</a> 應稅</td>
                        </tr>
                    </tbody>
                </table>
                <hr width="710" style="margin: .5rem auto;">
                <table width="710" cellpadding="1"
                    style="font-size:small;margin:0 auto;border-collapse:collapse;">
                    <thead>
                        <tr height="24">
                            <td width="20%">合　　計：</td>
                            <td width="36%" style="text-align: right;">（{{ '壹佰玖拾伍元整' }}）</td>
                            <td width="10%" style="text-align: right;">{{ number_format(195) }}</td>
                            <td width="34%"></td>
                        </tr>
                    </thead>
                    <tbody style="text-align: left;">
                        <tr height="22">
                            <td colspan="4">1101 現金 195 （現金 - 現金 - ）</td>
                        </tr>
                        <tr height="22">
                            <td colspan="4">應收帳款-LINE PAY-1,475(應收帳款-LINE PAY訂單編號:15835N15695R1)-已入款(MSG0027438)</td>
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
                            <td width="25%">商品負責：資訊部</td>
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
