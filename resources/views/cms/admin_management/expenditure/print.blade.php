<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>列印支出憑單</title>
    <style>
        * {
            font-family: "Nunito", "Noto Sans TC", sans-serif;
            position: relative;
        }

        @page {
            size: A4 portrait;
            /* A4 直向 */
            margin: 5mm auto;
            /* 邊界 */
        }
    </style>
</head>

<body style="margin-top: 0px;">
    <div style="left: 0; top: 0; width:100%;">
        <div>
            <table width="650" cellpadding="3" cellspacing="0" border="1" bordercolor="#000000"
                style="font-size:11pt;text-align:left;margin:0 auto;border-collapse:collapse;">
                <thead style="text-align: center;">
                    <tr height="70">
                        <td colspan="3" style="border-color: #FFF #FFF #000;">
                            <div style="font-size:18pt;">喜鴻國際企業股份有限公司</div>
                            <div>
                                <span style="font-size: 16pt;">列印支出憑單</span>
                                <span style="margin-left: 1.5rem;">

                                </span>
                            </div>
                        </td>
                    </tr>
                </thead>
            </table>

            <table width="650" cellpadding="3" cellspacing="0" border="1" bordercolor="#000000"
                style="font-size:11pt;text-align:left;margin:0 auto;border-collapse:collapse;">
                <tbody>
                    <tr>
                        <th width="100">單號</th>
                        <td colspan="3">{{ $data->sn }}</td>
                    </tr>
                    <tr>
                        <th width="100">主旨</th>
                        <td colspan="3">{{ $data->title }}</td>
                    </tr>
                    <tr>
                        <th>申請人</th>
                        <td>{{ $data->user_name }}</td>
                        <th width="100">建立日期</th>
                        <td>{{ $data->created_at }}</td>
                    </tr>
                    <tr>
                        <th>支出科目</th>
                        <td>{{ $data->item_title }}</td>
                        <th>支出部門</th>
                        <td>{{ $data->department_title }}</td>
                    </tr>
                    <tr>
                        <th>金額</th>
                        <td>{{ $data->amount }}</td>
                        <th>預計支付方式</th>
                        <td>{{ $data->payment_title }}</td>
                    </tr>
                    <tr>
                        <th>內容</th>
                        <td colspan="3">{!! nl2br($data->content) !!}</td>
                    </tr>
                    <tr>
                        <th>相關單號</th>
                        <td colspan="3">
                            @foreach ($order as $key => $value)
                                <span>{{ $value->order_sn }} </span>
                            @endforeach
                            @if ($relation_order)
                                @foreach ($relation_order as $key => $value)
                                    <span>{{ $value->sn }} </span>
                                @endforeach
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
            <table width="650" cellpadding="3" cellspacing="0" border="1" bordercolor="#000000"
                style="font-size:11pt;text-align:left;margin:0 auto;border-collapse:collapse;">
                <thead style="text-align: center;">
                    <tr>
                        <th scope="col" width="35%">主管</th>
                        <th scope="col" width="30%">職稱</th>
                        <th scope="col" width="35%">簽核時間</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data->users as $value)
                        <tr style="page-break-inside: avoid;">
                            <td>
                                {{ $value->user_name }}
                            </td>
                            <td>
                                {{ $value->user_title }}
                            </td>
                            <td>
                                {{ $value->checked_at }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
