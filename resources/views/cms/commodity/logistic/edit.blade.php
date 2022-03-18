@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">#{{ $delivery->sn }} 實際物流設定</h2>
    @error('error_msg')
    <div class="alert alert-danger" role="alert">
        {{ $message }}
    </div>
    @enderror

    <div class="card shadow p-4 mb-4">
        <h6>出貨商品列表</h6>
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th>商品名稱</th>
                        <th>類型</th>
                        <th>單價</th>
                        <th>數量</th>
                        <th>小計</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($deliveryList as $prod)
                        @php
                            $combo = $prod->product_title !== $prod->rec_product_title
                        @endphp
                        <tr>
                            <td>
                                @if ($combo)
                                    <span class="badge rounded-pill bg-warning text-dark">組合包</span> [
                                @else
                                    <span class="badge rounded-pill bg-success">一般</span>
                                @endif
                                {{ $prod->product_title }} @if($combo) ] {{$prod->rec_product_title}} @endif
                            </td>
                            <td>商品</td>
                            <td>${{ number_format($prod->price) }}</td>
                            <td>{{ number_format($prod->send_qty) }}</td>
                            <td>${{ number_format($prod->price * $prod->send_qty) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <form action="{{ $formAction }}" method="post">
        <div class="card shadow p-4 mb-4">
            <h6>物流基本資料</h6>
            <div class="row">
                <input type="hidden" name="logistic_id" value="{{ $logistic->id }}">
                <div class="col-12 mb-3">
                    <label class="form-label">物流</label>
                    <select name="actual_ship_group_id" class="-select2 -single form-select" data-placeholder="請單選">
                        <option value="" selected disabled>請選擇</option>
                        @foreach ($shipmentGroup as $ship)
                            <option value="{{ $ship->id }}">{{ $ship->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </form>
@endsection
@once
    @push('sub-scripts')
    @endpush
@endonce
