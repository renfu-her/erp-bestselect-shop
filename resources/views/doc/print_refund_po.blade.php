<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>退出付款單</title>
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
            退出付款單
        </div>
        <hr width="710" style="margin: .5rem auto;">
        <div>
            <table width="710" style="font-size:small;text-align:left;border:0;margin: 0 auto;">
                <tbody>
                <tr>
                    <td width="50%">付款單號：{{ $paying_order->sn }}</td>
                    <td>製表日期：{{ date('Y-m-d', strtotime($paying_order->created_at)) }}</td>
                </tr>
                <tr>
                    <td width="50%">單據編號：
                        @php
                            $i = 1;
                            $count = count($parent_source);
                        @endphp
                        @foreach($parent_source as $p_value)
                        {{ $p_value['sn'] }}{{ $count != $i ? ' / ' : '' }}
                        @php
                            $i++;
                        @endphp
                        @endforeach
                    </td>
                    <td>付款日期：{{ $paying_order->payment_date ? date('Y-m-d', strtotime($paying_order->payment_date)) : '' }}</td>
                </tr>
                <tr>
                    <td width="50%">支付對象：{{ $paying_order->payee_name }}</td>
                    <td>承辦人：{{ $undertaker ? $undertaker->name : '' }}</td>
                </tr>
                <tr>
                    <td>電話：{{ $paying_order->payee_phone }}</td>
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
                @foreach($target_items as $t_value)
                    <tr>
                        <td>{{ $t_value->refund_grade_code }} {{ $t_value->refund_grade_name }} - {{ $t_value->refund_title . ' ' . $t_value->refund_summary}}</td>
                        <td style="text-align: right;">{{ number_format($t_value->refund_qty) }}</td>
                        <td style="text-align: right;">${{ number_format($t_value->refund_price, 2) }}</td>
                        <td style="text-align: right;">${{ number_format($t_value->refund_total_price) }}</td>
                        <td>{!! nl2br($t_value->refund_note) !!} {!! nl2br($t_value->refund_po_note) !!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <hr width="710" style="margin: .5rem auto;">
            <table width="710" cellpadding="1"
                   style="font-size:small;margin:0 auto;border-collapse:collapse;">
                <thead>
                <tr height="24">
                    <td width="20%">合　　計：</td>
                    <td width="36%" style="text-align: right;">（{{ $zh_price }}）</td>
                    <td width="10%" style="text-align: right;">{{ number_format($paying_order->price) }}</td>
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
                    <td width="25%">會計：{{ $accountant }}</td>
                    <td width="25%">商品主管：</td>
                    <td width="25%">商品負責人：</td>
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
