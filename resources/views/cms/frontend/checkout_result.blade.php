@extends('layouts.layout')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12 mt-5">
                <div class="card">
                    <div class="card-header">線上刷卡結果</div>

                    <div class="card-body">
                        <div class="table-responsive tableOverBox">
                            <table class="table table-hover table-bordered tableList mb-0">
                                <tbody class="product_list">
                                    <tr>
                                        <th>公司</th>
                                        <td>{{ $order->sale_title }}</td>
                                    </tr>
                                    <tr>
                                        <th>服務專線</th>
                                        <td>02-25637600</td>
                                    </tr>
                                    <tr>
                                        <th>刷卡狀態</th>
                                        <td>{{ $order->log_status == 0 ? '刷卡成功。' : '刷卡失敗: ' . $order->log_errdesc }}</td>
                                    </tr>
                                    <tr>
                                        <th>刷卡金額</th>
                                        <td>{{ number_format($order->log_authamt) }}</td>
                                    </tr>
                                    <tr>
                                        <th>刷卡摘要</th>
                                        <td>{{ $order->log_authcode ? '授權碼:' . $order->log_authcode . ' ' : '' }}{{ $order->log_cardnumber ? '卡號:' . $order->log_cardnumber : '' }}</td>
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
