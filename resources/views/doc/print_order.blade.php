<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>@switch($type)
        @case('sales')
            銷貨單明細
            @break
        @case('ship')
            出貨單明細
            @break
        @default
            明細列印
    @endswitch</title>
    <style>
        .print {
            margin-top: 2em; 
            font-size: 2em;
        }
        body {
            background-color: bisque;
        }

        @media print {
            .print {
                display: none;
            }
        }
    </style>
</head>
<body style="margin-top: 0px; position: relative;">
    <div style="position: absolute; left: 0; top: 0; width:100%">
        {{ dd($order, $subOrders) }}
    </div>
</body>
</html>
