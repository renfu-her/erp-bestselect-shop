@extends('layouts.main')
@section('sub-content')
    <div>
        <x-b-customer-navi :customer="$customer"></x-b-customer-navi>
    </div>
    <div class="card shadow p-4 mb-4">
        <h6>獲得紀錄</h6>
        <form id="form2" action="" method="GET">
            <div class="d-flex justify-content-end align-items-center mb-3">
                <div class="col-auto me-1">
                    <select class="form-select form-select-sm" name="year" aria-label="年度">
                        <option value="" disabled>選擇年度</option>
                        @foreach ($year as $value)
                            <option value="{{ $value }}" @if ($value == $cond['year']) selected @endif>
                                {{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto me-1">
                    <select class="form-select form-select-sm" name="month" aria-label="月份">
                        <option value="" disabled>選擇月份</option>
                        @for ($i = 1; $i < 13; $i++)
                            <option value="{{ $i }}" @if ($i == $cond['month']) selected @endif>
                                {{ $i }}月</option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">
                        搜尋
                        <div class="spinner-border spinner-border-sm" hidden role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </button>
                </div>
            </div>
        </form>
        <div class="table-responsive tableOverBox">
            <table class="table tableList table-striped mb-1">
                <thead>
                    <tr>
                        <th scope="col" style="width:40px">#</th>
                        <th scope="col">子訂單</th>
                        <th scope="col">品名規格</th>
                        <th scope="col" class="text-center px-3">金額</th>
                        <th scope="col" class="text-center px-3">數量</th>
                        <th scope="col" class="text-center px-3">小計</th>
                        <th scope="col" class="text-center px-3">獎金</th>
                        <th scope="col" class="text-center px-3">出庫數量</th>
                        <th scope="col">倉庫</th>
                        <th scope="col">產品人員</th>
                    </tr>
                </thead>
                @php
                    $bonus = 0;
                @endphp
                <tbody>
                    @foreach ($dataList as $key => $item)
                        @php
                            $bonus += $item->bonus;
                        @endphp
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>{{ $item->sub_order_sn }}</td>
                            <td>{{ $item->product_title }}</td>
                            <td class="text-center">$ {{ number_format($item->price) }}</td>
                            <td class="text-center">{{ number_format($item->qty) }}</td>
                            <td class="text-center">$ {{ number_format($item->origin_price) }}</td>
                            <td class="text-center">$ {{ number_format($item->bonus) }}</td>
                            <td class="text-center">{{ number_format(0) }}</td>
                            <td>-</td>
                            <td>{{ $item->product_user }}</td>
                        </tr>
                    @endforeach
                        <tr>
                            <td colspan="6"></td>
                            <td class="text-center">$ {{ $bonus }}</td>
                            <td colspan="3"></td>
                        </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
