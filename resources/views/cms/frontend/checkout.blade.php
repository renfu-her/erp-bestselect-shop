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
                                        <th>公司</th>
                                        <td>{{ $order->sale_title}}</td>
                                    </tr>
                                    <tr>
                                        <th>客戶</th>
                                        <td>{{ $order->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>請款單號</th>
                                        <td>{{ $order->received_sn }}</td>
                                    </tr>
                                    <tr>
                                        <th>應付金額</th>
                                        <td>{{ number_format($order->total_price) }}</td>
                                    </tr>
                                    <tr>
                                        <th>付款摘要</th>
                                        <td>{{ $order->note }}</td>
                                    </tr>
                                    <tr>
                                        <th>付款方式</th>
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
