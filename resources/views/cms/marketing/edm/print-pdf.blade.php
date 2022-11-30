<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@100;300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ Asset('css/edm.css') }}">

    <title>{{ $collection->name }}EDM_{{ $type === 'dealer' ? 'A' : 'B' }}</title>
    <style>
        @page {
            size: A4 portrait;
            /* A4(794×1123) 直向 */
            margin: 0mm;
            /* 邊界 */
        }
    </style>
</head>
@php
    $bg = isset($_GET['bg']) && $_GET['bg'] ? $_GET['bg'] : 'r';
    $qr = isset($_GET['qr']) && $_GET['qr'] ? $_GET['qr'] : 'y';
@endphp
<body style="margin-top: 0px;">
<div style="left: 0; top: 0; width:100%;">
    <div>
        <div class="print">
            <button type="button" onclick="javascript:window.print();">我要列印</button>
            {{-- <button type="button" onclick="">下載</button> --}}
            <button type="button" onclick="javascript:window.close();">關閉視窗</button>
        </div>

        <table width="794" cellpadding="10" cellspacing="0" class="T1">
            <thead>
                <tr height="115">
                    <th class="bg_{{ $bg }}">
                        <h1 style="margin: 5px auto;">{{ $collection->name }}</h1>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        @php
                            $n = 0;
                        @endphp
                        <table width="100%" cellpadding="10" cellspacing="0" border="0" class="T2">
                            @for ($i = 0; $i < (count($products) / 3); $i++, $n++)
                                <tr height="280" class="page">
                                    @for ($j = 0; $j < 3; $j++)
                                        @if (isset($products[$i * 3 + $j]))
                                            <td height="280" style="">
                                                <table width="100%" height="280" cellpadding="0" cellspacing="0" border="0" class="T3">
                                                    <tr height="180" style="vertical-align: top;">
                                                        <td height="180" style="padding: 0;">
                                                            <div style="overflow: hidden;width:100%;height:180px;display:flex;">
                                                                <img class="pImg" src="{{ $products[$i * 3 + $j]->img_url }}" 
                                                                    alt="{{ $products[$i * 3 + $j]->product_title }}">
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr style="vertical-align: top;">
                                                        <td>
                                                            <div class="pName">{{ $products[$i * 3 + $j]->product_title }}</div>
                                                        </td>
                                                    </tr>
                                                    {{-- <tr>
                                                        <td style="vertical-align: top;">
                                                            @if (count($products[$i * 3 + $j]->style) > 1)
                                                                @foreach ($products[$i * 3 + $j]->style as $style)
                                                                    <span class="style-pill">{{ $style->title }}</span>
                                                                @endforeach
                                                            @endif
                                                        </td>
                                                    </tr> --}}
                                                    <tr height="55">
                                                        <td style="text-align: center;">
                                                            <div style="display: flex;align-items: flex-end;height:100%;">
                                                                <div style="flex: 1;">
                                                                    @if ($products[$i * 3 + $j]->price < $products[$i * 3 + $j]->origin_price)
                                                                        <div class="origin-price">{{ $products[$i * 3 + $j]->origin_price }}</div>
                                                                    @endif
                                                                    <div class="price">
                                                                        {{ $products[$i * 3 + $j]->price }}
                                                                        @if (count($products[$i * 3 + $j]->style) > 1)
                                                                            <span style="font-size: 12px;font-weight: 500;margin-left: -5px;">起</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                @if ($qr === 'y')
                                                                    <div style="width: 55px">
                                                                        {{-- QR code --}}
                                                                        <div class="qrcode" data-url="https://www.bestselection.com.tw/product/{{ $products[$i * 3 + $j]->sku }}?mcode={{ $mcode }}"></div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        @else
                                            <td></td>
                                        @endif
                                    @endfor
                                </tr>
                            @endfor
                            @for ($i = 0; ($n % 3) > 0 && $i < 3-($n % 3); $i++)
                                <tr height="290">
                                    <td></td><td></td><td></td>
                                </tr>
                            @endfor
                        </table>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr height="70">
                    <th height="70" style="font-size: 1.5rem;font-weight: 500;letter-spacing: 2px;">
                        <div style="display: flex;align-items: center;">
                            <div style="border-right: 1px solid #4B4B4B;padding:0 20px;width:30%;">
                                <img src="{{ Asset('images/Bestselection-logo.png') }}" alt="喜鴻購物"
                                    style="display: block;">
                            </div>
                            <div style="padding:0 20px;">官網看更多商品</div>
                            <div>
                                <div class="qrcode" data-url="https://www.bestselection.com.tw?mcode={{ $mcode }}"></div>
                            </div>
                            <div style="flex: 1;text-align: right;padding-right: 20px;">請洽{{ $name }}</div>
                        </div>
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
</body>
</html>
{{-- {{ dd($products[0]) }} --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.qrcode/1.0/jquery.qrcode.min.js"></script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    // QR code
    // $('.qrcode').qrcode({
    //     width: 60,
    //     height: 60,
    //     text: `https://www.bestselection.com.tw/product/${sku}?mcode=${mcode}`
    // });
    $.when(
        $('.qrcode').each(function (index, element) {
            // element == this
            const url = $(element).data('url');
            new QRCode(element, {
                width: 55,
                height: 55,
                colorDark: '#000000',
                colorLight: '#FFFFFF',
                correctLevel: QRCode.CorrectLevel.M,
                text: url
            });
        })
    ).then((result) => {
        console.log('all done');
    }).catch((err) => {
        console.log(err);
    });
</script>