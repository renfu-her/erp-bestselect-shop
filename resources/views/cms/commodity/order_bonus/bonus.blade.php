@extends('layouts.main')
@section('sub-content')
    <div class="card shadow p-4 mb-4">
        <div class="col">
            <a href="{{ route('cms.order-bonus.detail', ['id' => $month_report->id]) }}" class="btn btn-primary">
                返回上一頁
            </a>
        </div>
        <h6>{{ $month_report->title }}</h6>
        <div class="table-responsive tableOverBox">
            <table class="table tableList table-striped mb-1">
                <tbody>
                    <tr>
                        <td><b>姓名</b></td>
                        <td>{{ $customer->name }}</td>
                        <td><b>mcode</b></td>
                        <td>{{ $customer->mcode }}</td>
                    </tr>
                    <tr>
                        <td><b>銀行名稱</b></td>
                        <td>{{ $customer->bank_title }}</td>
                        <td><b>帳號</b></td>
                        <td>{{ $customer->bank_account }}</td>
                    </tr>
                    <tr>
                        <td><b>帳號名稱</b></td>
                        <td>{{ $customer->bank_account_name }}</td>
                        <td><b>身分證字號</b></td>
                        <td>{{ $customer->identity_sn }}</td>
                    </tr>
                    <tr>
                        <td><b>總金額</b></td>
                        <td colspan="3">{{ $customer->bonus }}</td>
                    </tr>
                    <tr>
                        <td><b>報表建立日期</b></td>
                        <td colspan="3">{{ $month_report->created_at }}</td>
                    </tr>
                </tbody>

            </table>
        </div>
    </div>
    <div class="card shadow p-4 mb-4">

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
