<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@100;300;400;500;700&display=swap" rel="stylesheet">
    <title>{{ $collection->name }}EDM（{{ $type === 'dealer' ? '經銷版' : '直客版' }}）</title>
    <style>
        * {
            font-family: "Noto Sans TC", sans-serif;
            position: relative;
        }
        @page {
            size: A4 portrait;
            /* A4 直向 */
            margin: 5mm auto;
            /* 邊界 */
        }
        
        .print {
            margin: 1em auto;
            text-align: center;
        }

        .print button {
            font-size: 1.5rem;
            margin: 0 10px;
            font-family: "Nunito", "Noto Sans TC", sans-serif;
        }
        @media print {
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
<div style="left: 0; top: 0; width:100%;">
    <div>
        <div class="print">
            <button type="button" onclick="javascript:window.print();">我要列印</button>
            <button type="button" onclick="javascript:window.close();">關閉視窗</button>
        </div>
        <table width="710" cellpadding="3" cellspacing="0" border="1" bordercolor="#000000"
            style="font-size:11pt;text-align:left;margin:0 auto;border-collapse:collapse;">
            <thead style="text-align: center;">
                <tr height="">
                    <th colspan="3">
                        <h1>{{ $collection->name }}</h1>
                    </th>
                </tr>
            </thead>
            <tbody>
                @php
                    $n = 0;
                @endphp
                @for ($i = 0; $i < (count($products) / 3); $i++, $n++)
                    <tr height="235">
                        @for ($j = 0; $j < 3; $j++)
                            @if (isset($products[$i * 3 + $j]))
                                <td>
                                    {{ $products[$i * 3 + $j]->product_title }}
                                </td>
                            @else
                                <td></td>
                            @endif
                        @endfor
                    </tr>
                @endfor
                @for ($i = 0; $i < 4-($n % 4); $i++)
                    <tr height="235">
                        <td></td><td></td><td></td>
                    </tr>
                @endfor
            </tbody>
            <tfoot>
                <tr height="50">
                    <th colspan="3">電話諮詢請洽：02-12345678</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
</body>
</html>
{{-- {{ dd($type, $mcode, $products, $collection->name) }} --}}
