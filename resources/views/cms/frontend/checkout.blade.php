@extends('layouts.layout')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12 mt-5">
                <div class="card">
                    <div class="card-header">線上刷卡</div>

                    <div class="card-body">
                        <div class="table-responsive tableOverBox">
                            <table class="table table-hover table-bordered tableList mb-0">
                                <tbody class="product_list">
                                    <tr>
                                        <td>公司</td>
                                        <td>{{ $order->sale_title}}</td>
                                    </tr>
                                    <tr>
                                        <td>客戶</td>
                                        <td>訂購人 {{ $order->ord_name }}</td>
                                    </tr>
                                    <tr>
                                        <td>請款單號</td>
                                        <td>{{ $order->received_sn }}</td>
                                    </tr>
                                    <tr>
                                        <td>應付金額</td>
                                        <td>{{ number_format($order->total_price) }}</td>
                                    </tr>
                                    <tr>
                                        <td>付款摘要</td>
                                        <td>
                                            <div class="table-responsive">
                                                <table class="table table-bordered text-center align-middle d-sm-table d-none text-nowrap">
                                                    <tbody class="border-top-0 m_row">
                                                    <tr class="table-light">
                                                        <td class="col-2">產品名稱</td>
                                                        <td class="col-2">價格(總價)</td>
                                                        <td class="col-2">數量</td>
                                                    </tr>
                                                    @foreach($sub_order as $s_value)
                                                        @foreach($s_value->items as $value)
                                                            <tr>
                                                                <td>{{ $value->product_title }}</td>
                                                                <td>{{ number_format($value->total_price) }}</td>
                                                                <td>{{ $value->qty }}</td>
                                                            </tr>
                                                        @endforeach
                                                    @endforeach

                                                    @if($order->dlv_fee > 0)
                                                        <tr>
                                                            <td>物流費用</td>
                                                            <td>{{ number_format($order->dlv_fee) }}</td>
                                                            <td>1</td>
                                                        </tr>
                                                    @endif

                                                    @if(count($order_discount) > 0)
                                                        @foreach($order_discount as $value)
                                                            <tr>
                                                                <td>{{ $value->title }}</td>
                                                                <td>-{{ number_format($value->discount_value) }}</td>
                                                                <td>1</td>
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>付款方式</td>
                                        <td>
                                            <form action="{{ $str_url }}" method="POST" style="display: inline-block;">
                                                <input type="hidden" name="MACString" value="{{ $str_mac_string }}">
                                                <input type="hidden" name="merID" value="{{ $str_mer_id }}">
                                                <input type="hidden" name="URLEnc" value="{{ $str_url_enc }}">
                                                <button type="submit" class="btn btn-primary">線上刷卡</button>
                                            </form>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
