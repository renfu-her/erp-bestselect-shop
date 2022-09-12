<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>{{ $type === 'deposit' ? '訂金' : '尾款'}}付款單</title>
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
            <div style="font-size: x-large; font-family:標楷體">{{ $appliedCompanyData->company }}</div>
            <div style="font-size: small; margin: 1px auto;">
                <span>地址：{{ $appliedCompanyData->address }}</span>
                <span style="margin-left: 1.5rem;">電話：{{ $appliedCompanyData->phone }}</span>
                <span style="margin-left: 1.5rem;">傳真：{{ $appliedCompanyData->fax }}</span>
            </div>

            <div style="font-size: x-large; font-family:標楷體">
                {{ $type === 'deposit' ? '訂金' : '尾款'}}付款單
            </div>
            <hr width="710" style="margin: .5rem auto;">
            <div>
                <table width="710" style="font-size:small;text-align:left;border:0;margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td width="50%">付款單號：{{ $payingOrderData->sn }}</td>
                            <td width="50%">製表日期：{{ date('Y-m-d', strtotime($payingOrderData->created_at)) }}</td>
                        </tr>
                        <tr>
                            <td>單據編號：{{ $purchaseData->purchase_sn }}</td>
                            <td>付款日期：{{ $pay_off ? $pay_off_date : '' }}</td>
                        </tr>
                        <tr>
                            <td>支付對象：{{ $payingOrderData->payee_name }}</td>
                            <td>承辦人：{{ $undertaker }}</td>
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
                        @if($type === 'deposit')
                            <tr>
                                <td>{{ $productGradeName . '-' . $depositPaymentData->summary }}</td>
                                <td style="text-align: right;">{{ number_format(1) }}</td>
                                <td style="text-align: right;">{{ number_format($depositPaymentData->price, 2) }}</td>
                                <td style="text-align: right;">{{ number_format($depositPaymentData->price) }}</td>
                                <td>{{ $depositPaymentData->memo }}</td>
                            </tr>
                        @elseif($type === 'final')
                            @foreach($purchaseItemData as $purchaseItem)
                                <tr>
                                    <td>{{ $productGradeName . '-' .$purchaseItem->title . '（負責人：' . $purchaseItem->name }}）</td>
                                    <td style="text-align: right;">{{ number_format($purchaseItem->num) }}</td>
                                    <td style="text-align: right;">{{ number_format($purchaseItem->total_price / $purchaseItem->num, 2) }}</td>
                                    <td style="text-align: right;">{{ number_format($purchaseItem->total_price) }}</td>
                                    <td>{{ $purchaseItem->memo }}</td>
                                </tr>
                            @endforeach
                            @if($logisticsPrice > 0)
                                <tr>
                                    <td>{{ $logisticsGradeName . '- 物流費用' }}</td>
                                    <td style="text-align: right;"></td>
                                    <td style="text-align: right;"></td>
                                    <td style="text-align: right;">{{ number_format($logisticsPrice) }}</td>
                                    <td>{{ $purchaseData->logistics_memo }}</td>
                                </tr>
                            @endif
                            @if(!is_null($depositPaymentData))
                                <tr>
                                    <td>{{ $productGradeName }}-訂金抵扣（訂金付款單號{{ $depositPaymentData->sn }}）</td>
                                    <td style="text-align: right;">1</td>
                                    <td style="text-align: right;">-{{ number_format($depositPaymentData->price, 2) }}</td>
                                    <td style="text-align: right;">-{{ number_format($depositPaymentData->price) }}</td>
                                    <td>{{$depositPaymentData->memo}}</td>
                                </tr>
                            @endif
                        @endif
                    </tbody>
                </table>
                <hr width="710" style="margin: .5rem auto;">
                <table width="710" cellpadding="1"
                    style="font-size:small;margin:0 auto;border-collapse:collapse;">
                    <thead>
                        <tr height="24">
                            <td width="20%">合　　計：</td>
                            <td width="36%" style="text-align: right;">（{{ $zh_price }}）</td>
                            <td width="10%" style="text-align: right;">
                                @if ($type === 'deposit')
                                    {{ number_format($depositPaymentData->price) }}
                                @elseif($type === 'final')
                                    {{ number_format($finalPaymentPrice) }}
                                @endif
                            </td>
                            <td width="34%"></td>
                        </tr>
                    </thead>
                    <tbody style="text-align: left;">
                    @foreach($payable_data as $value)
                        <tr height="22">
                            <td colspan="4">
                                {{ $value->account->code . ' ' . $value->account->name }}
                                {{ number_format($value->tw_price) }}
                                @if($value->acc_income_type_fk == 3)
                                    {{ '（' . $value->payable_method_name . ' - ' . $value->summary . '）' }}
                                @elseif($value->acc_income_type_fk == 2)
                                    {!! '（<a href="' . route('cms.note_payable.record', ['id'=>$value->payable_method_id]) . '">' . $value->payable_method_name . ' ' . $value->cheque_ticket_number . '（' . date('Y-m-d', strtotime($value->cheque_due_date)) . '）' . '</a>）' !!}
                                @else
                                    {{ '（' . $value->payable_method_name . ' - ' . $value->account->name . ' - ' . $value->summary . '）' }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <hr width="710" style="margin: .5rem auto;">
                <table width="710" style="font-size:small;text-align:left;border:0;margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td width="25%">財務主管：</td>
                            <td width="25%">會計：{{ $accountant ?? '' }}</td>
                            <td width="25%">商品主管：</td>
                            <td width="25%">商品負責人：{{ $chargemen }}</td>
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
