@extends('layouts.main')

@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.ar.receipt', ['id' => $received_order->order_id]) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>

    <form method="POST" action="{{ $form_action }}">
        @csrf
        <div class="card mb-4">
            <h2 class="mx-3 my-3">收款單入款審核</h2>
            <div class="card-body">
                <div class="col-12 mb-3">
                    <label class="form-label">收款單號：</label>
                    {{$received_order->sn}}
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">承辦者：</label>
                    {{ $undertaker ? $undertaker->name : '' }}
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label">訂單編號：</label>
                    {{ $order ? $order->sn : '' }}
                </div>

                <div class="col-3 mb-3">
                    <div class="input-group">
                        <div><span class="text-danger">*</span>審核日期：</div>
                        <input type="date" class="form-control @error('receipt_date') is-invalid @enderror" name="receipt_date" value="{{ old('receipt_date', $received_order->receipt_date ?? date('Y-m-d', strtotime( date('Y-m-d'))) ) }}" aria-label="付款日">
                        <div class="invalid-feedback">
                            @error('receipt_date')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="col-3 mb-3">
                    <div class="input-group">
                        <div><span class="text-danger">*</span>發票號碼：</div>
                        <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" name="invoice_number" value="{{ old('invoice_number', (isset($received_order)?($received_order->invoice_number ? explode(' ', $received_order->invoice_number)[0] : ''): '')) }}" aria-label="付款日"/>
                        <div class="invalid-feedback">
                            @error('invoice_number')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive tableoverbox">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th scope="col"></th>
                        <th scope="col">借</th>
                        <th scope="col">借方金額</th>
                        <th scope="col">貸</th>
                        <th scope="col">貸方金額</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th scope="row"></th>
                        {{-- 借方 --}}
                        {{--
                        <td>
                            @foreach($received_data as $value)
                            {{ $value->received_method_name }} {{ $value->note }}{{ '（' . $value->account->code . ' - ' . $value->account->name . '）'}}
                            <br>
                            @endforeach
                        </td>
                        <td>
                            @foreach($received_data as $value)
                            {{ number_format($value->tw_price)}}
                            <br>
                            @endforeach
                        </td>
                        --}}
                        <td>
                            @foreach($debit as $value)
                            {{ $value->name }}
                            <br>
                            @endforeach
                        </td>
                        <td>
                            @php
                            $total_debit_price = 0;
                            foreach($debit as $value){
                                echo number_format($value->price) . "<br>";
                                $total_debit_price += $value->price;
                            }
                            @endphp
                        </td>

                        {{-- 貸方 --}}
                        {{--
                        <td>
                            商品
                            @foreach($order_list_data as $value)
                                {{ $product_grade_name }} --- {{ $value->product_title }}{{'（' . $value->del_even . ' - ' . $value->del_category_name . '）'}}{{'（' . $value->product_price . ' * ' . $value->product_qty . '）'}}
                                <br>
                            @endforeach

                            物流
                            @if($order->dlv_fee > 0)
                                {{ $logistics_grade_name }} --- 物流費用
                                <br>
                            @endif

                            折扣
                            @if($order->discount_value > 0)
                                折扣
                                <br>
                            @endif
                        </td>
                        <td>
                            商品
                            @foreach($order_list_data as $value)
                                {{ number_format($value->product_origin_price)}}
                                <br>
                            @endforeach

                            物流
                            @if($order->dlv_fee > 0)
                                {{ number_format($order->dlv_fee) }}
                                <br>
                            @endif

                            折扣
                            @if($order->discount_value > 0)
                                -{{ number_format($order->discount_value) }}
                                <br>
                            @endif
                        </td>
                        --}}
                        <td>
                            @foreach($credit as $value)
                            {{ $value->name }}
                            <br>
                            @endforeach
                        </td>
                        <td>
                            @php
                            $total_credit_price = 0;
                            foreach($credit as $value){
                                echo number_format($value->price) . "<br>";
                                $total_credit_price += $value->price;
                            }
                            @endphp
                        </td>
                    </tr>

                    <tr class="table-light">
                        <td>合計：</td>
                        <td></td>
                        <td>{{ number_format($total_debit_price) }}{{-- number_format($received_order->price) --}}</td>
                        <td></td>
                        <td>{{ number_format($total_credit_price) }}{{-- number_format($received_order->price) --}}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="px-0">
            <button type="submit" class="btn btn-primary px-4">確認</button>
            {{-- <a onclick="history.back()" class="btn btn-outline-primary px-4" role="button">取消</a> --}}
        </div>
    </form>
@endsection

@once
@endonce
