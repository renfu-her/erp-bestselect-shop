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
        img {
            width: 100%;
            height: auto;
        }
        .pImg {
            width: auto;
            max-width: 100%;
            max-height: 195px;
        }
        .style-pill {
            /* border: 1px solid #008BC6; */
            border-radius: 4px;
            /* padding-right: 2px; */
            color: #008BC6;
            font-size: 12px;
            line-height: 20px;
        }
        .style-pill:not(:last-child)::after {
            content: ' /'
        }
        .origin-price {
            color: #6c757d;
            text-decoration: line-through;
            margin-right: 10px;
            position: relative;
        }
        .del-line::after {
            content: '';
            display: block;
            position: absolute;
            /* right: -3px; */
            bottom: 12px;
            width: 110%;
            border-bottom: 2px solid #6c757d;
        }
        .price {
            font-weight: bold;
            font-size: 14pt;
            color: #F00;
            line-height: 1;
        }
        .origin-price::before,
        .price::before {
            content: "$";
        }
        .qrcode img {
            width: auto;
            margin-left: auto;
        }
        .page {
            /* page-break-inside: avoid; */
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
            <button type="button" onclick="screenshot()">下載</button>
            <button type="button" onclick="javascript:window.close();">關閉視窗</button>
        </div>
        {{-- <div id="Img"></div> --}}

        <div id="Content">
            
        @for ($p = 0; $p < (count($products) / 9); $p++)
            <table width="710" cellpadding="5" cellspacing="0" border="0" bordercolor="#000000" class="page"
                style="font-size:11pt;text-align:left;margin:0 auto;border-collapse:collapse;">
                <thead style="text-align: center;">
                    <tr height="">
                        <th colspan="3">
                            <h1 style="margin: 5px auto;">{{ $collection->name }}</h1>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $n = 0;
                    @endphp
                    @for ($i = $p * 3; $i < $p * 3 + 3; $i++, $n++)
                        <tr height="315">
                            @for ($j = 0; $j < 3; $j++)
                                @if (isset($products[$i * 3 + $j]))
                                    <td width="33.33%" height="315" style="padding: 5px 10px;">
                                        <table width="100%" height="315" cellpadding="0" cellspacing="0" border="0">
                                            <tr height="199" style="vertical-align: top;">
                                                <td colspan="2" style="text-align: center;padding: 0 5px;">
                                                    <img class="pImg" src="{{ $products[$i * 3 + $j]->img_url }}" alt="{{ $products[$i * 3 + $j]->product_title }}">
                                                </td>
                                            </tr>
                                            <tr style="vertical-align: top;">
                                                <td colspan="2">
                                                    {{ $products[$i * 3 + $j]->product_title }}
                                                </td>
                                            </tr>
                                            <tr height="48">
                                                <td style="vertical-align: top;">
                                                    @if (count($products[$i * 3 + $j]->style) > 1)
                                                        @foreach ($products[$i * 3 + $j]->style as $style)
                                                            <span class="style-pill">{{ $style->title }}</span>
                                                        @endforeach
                                                    @endif
                                                </td>
                                                <td rowspan="2" width="75" style="vertical-align: bottom;">
                                                {{-- QR code --}}
                                                    <div class="qrcode" data-sku="{{ $products[$i * 3 + $j]->sku }}"></div>
                                                </td>
                                            </tr>
                                            <tr height="27" style="vertical-align: bottom;">
                                                <td style="text-align: right;vertical-align: bottom;">
                                                    @if ($products[$i * 3 + $j]->price < $products[$i * 3 + $j]->origin_price)
                                                        <span class="origin-price">{{ number_format($products[$i * 3 + $j]->origin_price) }}</span>
                                                        <span class="price">{{ number_format($products[$i * 3 + $j]->price) }}</span>
                                                    @else
                                                        <span class="price">{{ number_format($products[$i * 3 + $j]->price) }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                @else
                                    <td width="33.33%"></td>
                                @endif
                            @endfor
                        </tr>
                    @endfor
                    @for ($i = 0; ($n % 3) > 0 && $i < 3-($n % 3); $i++)
                        <tr height="315">
                            <td></td><td></td><td></td>
                        </tr>
                    @endfor
                </tbody>
                <tfoot>
                    <tr height="40">
                        <th colspan="3">電話諮詢請洽：02-12345678</th>
                    </tr>
                </tfoot>
            </table>
        @endfor

        </div>
    </div>
</div>
</body>
</html>
{{-- {{ dd($products[0]) }} --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.qrcode/1.0/jquery.qrcode.min.js"></script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    const title = @json($collection->name);
    const mcode = @json($mcode);
    // QR code
    // $('.qrcode').qrcode({
    //     width: 70,
    //     height: 70,
    //     text: `https://www.bestselection.com.tw/product/P220512314?mcode=${mcode}`
    // });
    $('.qrcode').each(function (index, element) {
        // element == this
        const sku = $(element).data('sku');
        new QRCode(element, {
            width: 70,
            height: 70,
            correctLevel: QRCode.CorrectLevel.M,
            text: `https://www.bestselection.com.tw/product/${sku}?mcode=${mcode}`
        });
    });
    
    // 圖片下載
    function screenshot() {
        $('.origin-price').addClass('del-line');

        // window.open(`https://dhtml2pdf.herokuapp.com/api.php?url=${window.location.href}&result_type=show`, '_blank');

        html2pdf()
        .set({
            margin: 2,
            filename: 'EDM_' + title + '.png',
            image: { type: 'png', quality: 0.98 },
            pagebreak: { mode: 'avoid-all', after: '.page' },
            html2canvas: { useCORS: true, logging: true, }
        })
        .from(document.getElementById('Content'))
        .to('img')
        .output('img')
        .outputImg('datauristring')
        // .save()
        .then((result) => {
            $('.origin-price').removeClass('del-line');
            const a = document.createElement('a');
            a.href = result;
            a.download = 'EDM_' + title + '.png';
            a.click();
        });

        // html2canvas(document.getElementById('Content'), {
        //     // useCORS: true,
        //     // allowTaint: true,
        //     imageTimeout: 0,
        //     logging: true,
        // }).then((canvas) => {
        //     // document.body.appendChild(canvas);
        //     // const a = document.createElement('a');
        //     // a.href = canvas.toDataURL('image/png').replace('image/png', 'image/octet-stream');
        //     // a.download = 'EDM_' + title + '.png';
        //     // a.click();
        // });

        // $('.origin-price').removeClass('del-line');
    }
</script>