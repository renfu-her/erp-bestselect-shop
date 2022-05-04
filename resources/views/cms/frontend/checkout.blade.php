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
                                        <td>{{ $order->name }}</td>
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
                                        <td>{{ $order->note }}</td>
                                    </tr>
                                    <tr>
                                        <td>付款方式</td>
                                        <td>
                                            <form action="{{$str_url}}" method="POST" style="display: inline-block;">
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
