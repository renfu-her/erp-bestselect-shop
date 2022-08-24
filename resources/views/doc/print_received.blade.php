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
                收　款　單
            </div>
            <hr width="710" style="margin: .5rem auto;">
            <div>
                <table width="710" style="font-size:small;text-align:left;border:0;margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td width="50%">客戶：<span style="font-size:medium;">{{ $order_purchaser->name }}</span>　　台鑒</td>
                            <td width="50%">地址：{{ $order_purchaser->address }}</td>
                        </tr>
                        <tr>
                            <td>電話：{{ $order_purchaser->phone }}</td>
                            <td>傳真：{{ $order_purchaser->fax  }}</td>
                        </tr>
                    </tbody>
                </table>
                <hr width="710" style="margin: .5rem auto;">
                <table width="710" style="font-size:small;text-align:left;border:0;margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td width="50%">收款單號：{{ $received_order->sn }}</td>
                            <td width="50%">製表日期：{{ date('Y-m-d', strtotime($received_order->created_at)) }}</td>
                        </tr>
                        <tr>
                            <td>訂單流水號：{{ $order->sn }}</td>
                            @if($received_order->receipt_date)
                                <td>入帳日期：{{ date('Y-m-d', strtotime($received_order->receipt_date)) }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td>收款對象：</td>
                            <td>承辦人：{{ $undertaker ? $undertaker->name : '' }}</td>
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
                        @foreach($order_list_data as $value)
                            <tr>
                                <td>{{ $product_grade_name }} --- {{ $value->product_title }}{{'（' . ($value->del_even ?? null) . ' - ' . ($value->del_category_name ?? null) . '）'}}{{'（' . $value->product_price . ' * ' . $value->product_qty . '）'}}</td>
                                <td style="text-align: right;">{{ number_format($value->product_qty) }}</td>
                                <td style="text-align: right;">{{ number_format($value->product_price, 2) }}</td>
                                <td style="text-align: right;">{{ number_format($value->product_origin_price) }}</td>
                                <td>{{ $received_order->memo }} {{ $order->sn }} {{ $value->product_taxation == 1 ? '應稅' : '免稅' }} {{ $value->product_note ?? '' }}{{-- $order->note --}}</td>
                            </tr>
                        @endforeach
                        @if($order->dlv_fee > 0)
                            <tr>
                                <td>{{ $logistics_grade_name }}</td>
                                <td style="text-align: right;">1</td>
                                <td style="text-align: right;">{{ number_format($order->dlv_fee, 2) }}</td>
                                <td style="text-align: right;">{{ number_format($order->dlv_fee) }}</td>
                                <td>{{ $received_order->memo }} {{ $order->sn }} {{ $order->dlv_taxation == 1 ? '應稅' : '免稅' }}</td>
                            </tr>
                        @endif

                        @if($order->discount_value > 0)
                            @foreach($order_discount ?? [] as $d_value)
                                <tr>
                                    <td>{{ $d_value->account_code }} {{ $d_value->account_name }} - {{ $d_value->title }}</td>
                                    <td style="text-align: right;">1</td>
                                    <td style="text-align: right;">-{{ number_format($d_value->discount_value, 2) }}</td>
                                    <td style="text-align: right;">-{{ number_format($d_value->discount_value) }}</td>
                                    <td>{{ $received_order->memo }} {{ $order->sn }} {{ $d_value->discount_taxation == 1 ? '應稅' : '免稅' }}</td>
                                </tr>
                            @endforeach
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
                            <td width="10%" style="text-align: right;">{{ number_format($received_order->price) }}</td>
                            <td width="34%"></td>
                        </tr>
                    </thead>
                    <tbody style="text-align: left;">
                    @foreach($received_data as $value)
                        <tr height="22">
                            <td colspan="4">
                                {{ $value->account->code . ' ' . $value->account->name }}
                                {{ number_format($value->credit_card_price ?? $value->tw_price) }}
                                @if($value->received_method == 'credit_card')
                                    {{ '（' . $value->received_method_name . ' - ' . $value->credit_card_number . '（' . $value->credit_card_owner_name . '）' . '）' }}
                                @elseif($value->received_method == 'remit')
                                    {{ '（' . $value->received_method_name . ' - ' . $value->summary . '（' . $value->remit_memo . '）' . '）' }}
                                @elseif($value->received_method == 'cheque')
                                    {!! '（<a href="' . route('cms.note_receivable.record', ['id'=>$value->received_method_id]) . '">' . $value->received_method_name . ' - ' . $value->cheque_ticket_number . '（' . date('Y-m-d', strtotime($value->cheque_due_date)) . '）' . '</a>）' !!}
                                @else
                                    {{ '（' . $value->received_method_name . ' - ' . $value->account->name . ' - ' . $value->summary . '）' }}
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
