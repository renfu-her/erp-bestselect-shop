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
    </style>
</head>
<body style="margin-top: 0px;">
<div style="left: 0; top: 0; width:100%;">
    <div>
        <table width="710" cellpadding="3" cellspacing="0" border="1" bordercolor="#000000"
            style="font-size:11pt;text-align:left;margin:0 auto;border-collapse:collapse;">
            <thead style="text-align: center;">
                <tr>
                    <th colspan="3">{{ $collection->name }}</th>
                </tr>
            </thead>
            <tbody>
                <tr></tr>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3">電話諮詢請洽：02-12345678</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
</body>
</html>
{{-- {{ dd($type, $mcode, $products, $collection->name) }} --}}
