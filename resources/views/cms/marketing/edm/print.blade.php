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
            /* A4(794×1123px)(210mm×297mm) 直向 */
            margin: 0mm;
            /* 邊界 */
        }
    </style>
</head>
<body>
<div style="left: 0; top: 0; width:100%;">
    <div>
        @if ($btn === '1')
            <div class="print">
                <button type="button" onclick="javascript:window.print();">我要列印</button>
                <button type="button" onclick="javascript:window.close();">關閉視窗</button>
            </div>
        @endif

        <table width="{{ 794 * $x }}" cellpadding="10" cellspacing="0" border="0" class="T1">
            <thead>
                <tr height="{{ 115 * $x }}">
                    <th class="bg bg_{{ $bg }}">
                        <h1 style="letter-spacing: {{ 5*$x }}px;font-size: {{ 3*$x }}rem;-webkit-text-stroke-width:{{ 2*$x }}px;">
                            {{ $collection->name }}
                        </h1>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: {{ 15*$x }}px {{ 30*$x }}px {{ 10*$x }}px;">
                        @php
                            $n = 0;
                        @endphp
                        <table width="100%" cellpadding="{{ 9*$x }}" cellspacing="0" border="0" class="T2">
                            @for ($i = 0; $i < (count($products) / 3); $i++, $n++)
                                <tr height="{{ 280 * $x }}" class="page">
                                    @for ($j = 0; $j < 3; $j++)
                                        @if (isset($products[$i * 3 + $j]))
                                            <td height="{{ 280 * $x }}" style="">
                                                <table width="100%" height="{{ 280 * $x }}" cellpadding="0" cellspacing="0" border="0" class="T3">
                                                    <tr height="{{ 180 * $x }}" style="vertical-align: top;">
                                                        <td height="{{ 180 * $x }}" style="padding: 0;">
                                                            <div style="overflow: hidden;width:100%;height: {{ 180 * $x }}px;display:flex;">
                                                                <img class="pImg" src="{{ $products[$i * 3 + $j]->img_url ?? Asset('images/NoImg.png') }}" 
                                                                    alt="{{ $products[$i * 3 + $j]->product_title }}">
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr style="vertical-align: top;">
                                                        <td>
                                                            <div class="pName"
                                                                style="padding: {{ 2 * $x }}px 0 {{ 1 * $x }}px;font-size: {{ strlen($products[$i * 3 + $j]->product_title) > 72 ? $x : 1.1 * $x }}rem;">
                                                                {{ $products[$i * 3 + $j]->product_title }}
                                                            </div>
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
                                                    <tr height="{{ 58 * $x }}">
                                                        <td style="text-align: center;">
                                                            <div style="display: flex;align-items: center;height:100%;">
                                                                <div style="flex: 1;align-self: flex-end;">
                                                                    @if ($products[$i * 3 + $j]->price < $products[$i * 3 + $j]->origin_price)
                                                                        <div class="origin-price" style="font-size: {{ $x }}rem">{{ $products[$i * 3 + $j]->origin_price }}</div>
                                                                    @endif
                                                                    <div class="price" style="padding-bottom: {{ 3 * $x }}px;font-size: {{ 2 * $x }}rem;">
                                                                        {{ $products[$i * 3 + $j]->price }}
                                                                        @if (count($products[$i * 3 + $j]->style) > 1)
                                                                            <span style="font-size: {{ 12 * $x }}px;font-weight: 500;margin-left: {{ -5 * $x }}px;">起</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                @if ($qr === '1')
                                                                    <div style="width: {{ 58 * $x }}px">
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
                                <tr height="{{ 290 * $x }}">
                                    <td></td><td></td><td></td>
                                </tr>
                            @endfor
                        </table>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr height="{{ 70 * $x }}">
                    <th height="{{ 70 * $x }}" style="font-size: {{ 1.5 * $x }}rem;font-weight: 500;letter-spacing: {{ 2 * $x }}px;">
                        <div style="display: flex;align-items: center;">
                            <div style="border-right: {{ $x }}px solid #4B4B4B;padding:0 {{ 20 * $x }}px;width:30%;">
                                <img src="{{ Asset('images/Bestselection-logo.png') }}" alt="喜鴻購物"
                                    style="display: block;">
                            </div>
                            <div style="padding:0 {{ 10 * $x }}px 0 {{ 20 * $x }}px;">官網看更多商品</div>
                            <div>
                                <div class="qrcode" data-size="55" data-url="https://www.bestselection.com.tw?mcode={{ $mcode }}"></div>
                            </div>
                            <div style="flex: 1;text-align: right;padding-right: {{ 20 * $x }}px;">請洽{{ $name }}</div>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    const x = @json($x);
    // QR code
    $.when(
        $('.qrcode').each(function (index, element) {
            // element == this
            const url = $(element).data('url');
            const size = Number($(element).data('size')) || 50;
            new QRCode(element, {
                width: size * x,
                height: size * x,
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