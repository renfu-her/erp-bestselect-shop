@extends('layouts.main')
@section('sub-content')
    <div>
        <x-b-customer-navi :customer="$customer"></x-b-customer-navi>
    </div>
    <div class="card shadow p-4 mb-4">
        <h6>獲得紀錄</h6>
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
                <tbody>
                    @foreach ($dataList as $key => $item)
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
                </tbody>
            </table>
        </div>
    </div>

@endsection
