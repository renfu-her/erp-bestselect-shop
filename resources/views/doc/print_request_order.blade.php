<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>請款單</title>
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
            <div style="font-size: x-large; font-family:標楷體">{{ $applied_company->company }}</div>
            <div style="font-size: small; margin: 1px auto;">
                <span>地址：{{ $applied_company->address }}</span>
                <span style="margin-left: 1.5rem;">電話：{{ $applied_company->phone }}</span>
                <span style="margin-left: 1.5rem;">傳真：{{ $applied_company->fax }}</span>
            </div>

            <div style="font-size: x-large; font-family:標楷體">
                請　款　單
            </div>
            <hr width="710" style="margin: .5rem auto;">
            <div>
                <table width="710" style="font-size:small;text-align:left;border:0;margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td width="50%">客戶：<span style="font-size:medium;">{{ $request_order->client_name }}</span>　　台鑒</td>
                            <td width="50%">地址：{{ $request_order->client_address }}</td>
                        </tr>
                        <tr>
                            <td>電話：{{ $request_order->client_phone }}</td>
                            <td>傳真：</td>
                        </tr>
                    </tbody>
                </table>
                <hr width="710" style="margin: .5rem auto;">
                <table width="710" style="font-size:small;text-align:left;border:0;margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td width="50%">請款單號：{{ $request_order->sn }}</td>
                            <td width="50%">日期：{{ date('Y/m/d', strtotime($request_order->created_at)) }}</td>
                        </tr>
                        <tr>
                            <td>{{-- 訂單流水號： --}}</td>
                            <td>入帳日期：{{ $request_order->posting_date ? date('Y/m/d', strtotime($request_order->posting_date)) : '' }}</td>
                        </tr>
                    </tbody>
                </table>
                <hr width="710" style="margin: .5rem auto;">
                <table width="710" cellpadding="5"
                    style="font-size:small;margin:0 auto;border-collapse:collapse;">
                    <thead style="text-align: left;">
                        <tr height="24">
                            <th scope="col" width="40%" style="padding-bottom:7px;">費用說明</th>
                            <th scope="col" width="8%" style="padding-bottom:7px;text-align: right;">數量</th>
                            <th scope="col" width="8%" style="padding-bottom:7px;text-align: right;">單價</th>
                            <th scope="col" width="10%" style="padding-bottom:7px;text-align: right;">金額</th>
                            <th scope="col" width="34%" style="padding-bottom:7px;">備註</th>
                        </tr>
                    </thead>
                    <tbody style="text-align: left;">
                        <tr>
                            <td>{{ $request_grade->code . ' ' . $request_grade->name . ' ' . $request_order->summary }}</td>
                            <td style="text-align: right;">{{ $request_order->qty }}</td>
                            <td style="text-align: right;">{{ number_format($request_order->price, 2) }}</td>
                            <td style="text-align: right;">{{ number_format($request_order->total_price) }}</td>
                            <td>{{ $request_order->taxation == 1 ? '應稅' : '免稅' }} {{ $request_order->memo }}</td>
                        </tr>
                    </tbody>
                </table>
                <hr width="710" style="margin: .5rem auto;">
                <table width="710" cellpadding="1"
                    style="font-size:small;margin:0 auto;border-collapse:collapse;">
                    <thead>
                        <tr height="24">
                            <td width="20%">合　　計：</td>
                            <td width="36%" style="text-align: right;">（{{ $zh_price }}）</td>
                            <td width="10%" style="text-align: right;">{{ number_format($request_order->total_price) }}</td>
                            <td width="34%"></td>
                        </tr>
                    </thead>
                </table>
                <hr width="710" style="margin: .5rem auto;">

                <div class="mb-3">
                    <dl class="row">
                        <div class="col">□支票</div>
                        <div class="col">□匯款</div>
                        <div class="col">□信用卡</div>
                        <div class="col">□現金</div>
                    </dl>
                    <dl class="row">
                        <div class="col-auto">
                            匯款帳號：合作金庫(006) 長春分行 0844-871-001158
                        </div>
                        <div class="col-auto">戶名：喜鴻國際企業股份有限公司</div>
                    </dl>
                    <dl class="row">
                        <div class="col small">
                            <dd class="mb-0">備註：</dd>
                            <dd>
                                <ol>
                                    <li>匯款戶名、支票抬頭請開：喜鴻國際企業股份有限公司</li>
                                    <li>客戶應如期給付團費，如有違反或票據到期未兌現，願負法律責任，並放棄訴抗辯權。</li>
                                </ol>
                            </dd>
                        </div>
                    </dl>
                </div>

                <hr width="710" style="margin: .5rem auto;">
                <table width="710" style="font-size:small;text-align:left;border:0;margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td width="20%">財務主管：</td>
                            <td width="20%">會計：{{ $accountant ? $accountant->name : '' }}</td>
                            <td width="20%">部門主管：</td>
                            <td width="20%">承辦人：</td>
                            <td width="20%">業務員：{{ $sales ? $sales->name : '' }}</td>
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
