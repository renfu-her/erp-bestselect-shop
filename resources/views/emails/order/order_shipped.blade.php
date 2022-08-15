<pre>
{{ $order_name }}先生 / 小姐 您好
您所訂購的商品 已經出貨了：
@if(isset($order_items) && 0 < count($order_items))
    @foreach($order_items as $item)
    {{$item->qty}} x {{$item->product_title}}
    @endforeach
@endif

寄送到以下地址：
收件者資訊：{{$receive_name}}
{{$receive_address}} {{$receive_phone}}
</pre>
