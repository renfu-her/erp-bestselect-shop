@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">獎金毛利</h2>

    <div class="card shadow p-4 mb-4">
        @if (count($discounts) > 0)
            <h6>折扣明細</h6>
            <div class="table-responsive">
                <table class="table table-sm text-right align-middle">
                    <tbody>
                        @foreach ($discounts as $key => $dis)
                            <tr>
                                @switch($dis->category_code)
                                    @case('code')
                                    @case('coupon')
                                        <td class="col-8">{{ $dis->category_title }}【{{ $dis->title }}】</td>
                                    @break

                                    @default
                                        <td class="col-8">{{ $dis->title }}</td>
                                @endswitch

                                @if ($dis->method_code == 'coupon')
                                    <td class="text-end pe-4">【{{ $dis->extra_title }}】</td>
                                @elseif (is_numeric($dis->discount_value))
                                    <td class="text-end pe-4 text-danger">-
                                        ${{ number_format($dis->discount_value) }}</td>
                                @else
                                    <td class="text-end pe-4">{{ $dis->discount_value || '' }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <h6>訂單總覽</h6>
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle d-sm-table d-none text-nowrap mb-0">
                @if (!$order->allotted_dividend)
                    <caption class="small text-end pb-0">鴻利預計發放時間：
                        @if (isset($order->dividend_active_at)) {{ date('Y/m/d H:i', strtotime($order->dividend_active_at)) }}
                        @else 未入款 @endif
                    </caption>
                @endif

                <tbody class="border-top-0">
                    <tr class="table-warning">
                        <td class="col-2">小計</td>
                        <td class="col-2">折扣</td>
                        <td class="col-2 lh-sm">折扣後 <br class="d-xxl-none">(不含運)</td>
                        <td class="col-2">運費</td>
                        <td class="col-2">總金額</td>
                        <td class="col-2 lh-sm">
                            @if ($order->allotted_dividend)
                                獲得<a href="{{ route('cms.sale_channel.index') }}" class="-text">鴻利</a>
                            @else
                                預計獲得<a href="{{ route('cms.sale_channel.index') }}" class="-text d-block d-xxl-inline">鴻利點數</a>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>${{ number_format($order->origin_price) }}</td>
                        <td class="text-danger">- ${{ number_format($order->discount_value) }}</td>
                        <td>${{ number_format($order->discounted_price) }}</td>
                        <td>${{ number_format($order->dlv_fee) }}</td>
                        <td class="fw-bold">${{ number_format($order->total_price) }}</td>
                        <td>{{ number_format($dividend) }}
                            @if ($order->allotted_dividend)
                                <span class="badge bg-success">已發</span>
                            @else
                                <span class="badge bg-secondary">未發</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="table table-bordered table-sm text-center align-middle d-table d-sm-none mb-0">
                @if (!$order->allotted_dividend)
                    <caption class="small text-end pb-0">鴻利預計發放時間：
                        @if (isset($order->dividend_active_at)) {{ date('Y/m/d H:i', strtotime($order->dividend_active_at)) }}
                        @else 未入款 @endif
                    </caption>
                @endif
                <tbody class="border-top-0">
                    <tr style="border-color: #dfe0e1;">
                        <td class="col-7 table-warning">小計</td>
                        <td class="text-end pe-4">${{ number_format($order->origin_price) }}</td>
                    </tr>
                    <tr>
                        <td class="col-7 table-warning">折扣 </td>
                        <td class="text-danger text-end pe-4">- ${{ number_format($order->discount_value) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="col-7 table-warning lh-sm">折扣後 (不含運)</td>
                        <td class="text-end pe-4">${{ number_format($order->discounted_price) }}</td>
                    </tr>
                    <tr>
                        <td class="col-7 table-warning">運費</td>
                        <td class="text-end pe-4">${{ number_format($order->dlv_fee) }}</td>
                    </tr>
                    <tr>
                        <td class="col-7 table-warning">總金額</td>
                        <td class="fw-bold text-end pe-4">${{ number_format($order->total_price) }}</td>
                    </tr>
                    <tr>
                        <td class="col-7 table-warning lh-sm">
                            @if ($order->allotted_dividend)
                                獲得<a href="{{ route('cms.sale_channel.index') }}" class="-text">鴻利</a>
                            @else
                                預計獲得<a href="{{ route('cms.sale_channel.index') }}" class="-text">鴻利點數</a>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            @if ($order->allotted_dividend)
                                <span class="badge bg-success">已發</span>
                            @else
                                <span class="badge bg-secondary">未發</span>
                            @endif
                            {{ number_format($dividend) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow p-4 mb-4">
        <h6>獎金毛利資訊</h6>
    </div>

    <div class="card shadow p-4 mb-4">
        <h6>獎金修改紀錄</h6>
    </div>

    <div>
        <a href="{{ Route('cms.order.detail', ['id' => $id]) }}" 
            class="btn btn-outline-primary px-4" role="button">返回明細</a>
    </div>
@endsection