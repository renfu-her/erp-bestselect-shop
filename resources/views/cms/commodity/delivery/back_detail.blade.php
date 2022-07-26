@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">#{{ $breadcrumb_data['sn'] }} 銷貨退回明細</h2>
    @if ($event === 'consignment')
        <x-b-consign-navi :id="$delivery->event_id"></x-b-consign-navi>
    @endif
    @if ($event === 'csn_order')
        <x-b-csnorder-navi :id="$delivery->event_id"></x-b-csnorder-navi>
    @endif
    @error('error_msg')
    <div class="alert alert-danger" role="alert">
        {{ $message }}
    </div>
    @enderror
    @error('item_error')
    <div class="alert alert-danger" role="alert">
        {{ $message }}
    </div>
    @enderror

    <div class="col-12 d-flex justify-content mt-2 flex-wrap">
        @if($delivery->event == App\Enums\Delivery\Event::order()->value)
            <a class="btn btn-sm btn-success -in-header mb-1"
               href="{{ Route('cms.delivery.back_detail', ['event' => \App\Enums\Delivery\Event::order()->value, 'eventId' => $delivery->event_id], true) }}">新增退貨付款單</a>
        @endif
    </div>
    <div class="card shadow p-4 mb-4">
        <h6>銷貨退回明細</h6>
        <dl class="row">
            <div class="col">
                <dt>銷貨單號</dt>
                <dd>{{$delivery->back_sn ?? ''}}</dd>
            </div>
            <div class="col">
                <dt>狀態</dt>
                <dd>{{ \App\Enums\Delivery\BackStatus::getDescription($delivery->back_status ?? '')}}</dd>
            </div>
            <div class="col">
                <dt>入庫日期</dt>
                <dd>{{ $delivery->back_inbound_date ? date('Y/m/d', strtotime($delivery->back_inbound_date)) : '' }}</dd>
            </div>
        </dl>
        <dl class="row">
            <div class="col">
                <dt>代墊單</dt>
                <dd></dd>
            </div>
            <div class="col">
                <dt>物流類型</dt>
                <dd>{{$logistic->group_name ?? ''}}</dd>
            </div>
            <div class="col">
                <dt>運費</dt>
                <dd>${{ (isset($logistic->cost)) ? number_format($logistic->cost) : '' }}</dd>
            </div>
        </dl>
        <dl class="row">
            <div class="col">
                <dt>客戶</dt>
                <dd>{{$order->ord_name ?? ''}}</dd>
            </div>
            <div class="col">
                <dt>客戶電話</dt>
                <dd>{{$order->ord_phone ?? ''}}</dd>
            </div>
            <div class="col">
                <dt>新增者</dt>
                <dd>{{$delivery->back_user_name ?? ''}}</dd>
            </div>
        </dl>
        <dl class="row">
            <div class="col">
                <dt>發票號碼</dt>
                <dd>{{$order->invoice_number ?? ''}}</dd>
            </div>
            <div class="col">
                <dt>發票日期</dt>
                <dd>{{ (isset($orderInvoice) && $orderInvoice->created_at) ? date('Y/m/d', strtotime($orderInvoice->created_at)) : '' }}</dd>
            </div>
            <div class="col">
                <dt>課稅別</dt>
                <dd></dd>
            </div>
        </dl>
        <dl class="row">
            <div class="col">
                <dt>進貨地址</dt>
                <dd>{{$order->ord_address ?? ''}}</dd>
            </div>
        </dl>
        <dl class="row">
            <div class="col">
                <dt>物流說明</dt>
                <dd>{{ (isset($logistic->memo)) ? $logistic->memo : '' }}</dd>
            </div>
            <div class="col">
                <dt>入庫者</dt>
                <dd>{{$delivery->back_inbound_user_name ?? ''}}</dd>
            </div>
        </dl>
        <dl class="row">
            <div class="col">
                <dt>預計進貨日期</dt>
                <dd></dd>
            </div>
            <div class="col">
                <dt>採購備註</dt>
                <dd>{{$delivery->back_memo ?? ''}}</dd>
            </div>
            <div class="col">
                <dt>訂貨單號</dt>
                <dd>{{$order->sn ?? ''}}</dd>
            </div>
        </dl>
    </div>

    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox mb-3">
            <table class="table tableList table-striped mb-1">
                <thead>
                    <tr>
                        <th scope="col" class="text-center" style="width:10%">#</th>
                        <th scope="col">品名規格</th>
                        <th scope="col" class="text-end">退款金額</th>
{{--                        <th scope="col" class="text-end">經銷價</th>--}}
                        <th scope="col" class="text-end">扣除獎金</th>
                        <th scope="col">退回數量</th>
                        <th scope="col" class="text-end">小計</th>
                        <th scope="col">說明</th>
                    </tr>
                </thead>
                @php
                    $total = 0;
                @endphp
                <tbody>
                     @foreach ($dlvBack as $key => $item)
                        @php
                            $subtotal = $item->price * 1;    // 退款金額 * 退回數量
                            $total += $subtotal;
                        @endphp
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>{{ $item->product_title ?? '' }}</td>
                            <td class="text-end">${{ number_format($item->price) }}</td>
{{--                            <td class="text-end">${{ number_format(450) }}</td>--}}
                            <td class="text-end">${{ number_format($item->bonus) }}</td>
                            <td>{{ number_format($item->qty) }}</td>
                            <td class="text-end">${{ number_format($subtotal) }}</td>
                            <td>{{ $item->memo ?? '' }}</td>
                        </tr>
                     @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex fw-bold border">
            <div class="col p-2 border-end bg-light">小計合計</div>
            <div class="col p-2 text-end">${{ number_format($total) }}</div>
        </div>
    </div>

    <div class="col-auto">
        @if($delivery->event == App\Enums\Delivery\Event::order()->value)
            <a href="{{ Route('cms.order.detail', ['id' => $order->id, 'subOrderId' => $delivery->event_id ]) }}" class="btn btn-outline-primary px-4" role="button">返回明細</a>
        @elseif($delivery->event == App\Enums\Delivery\Event::consignment()->value)
            <a href="{{ Route('cms.consignment.edit', ['id' => $delivery->event_id ]) }}" class="btn btn-outline-primary px-4" role="button">返回明細</a>
        @elseif($delivery->event == App\Enums\Delivery\Event::csn_order()->value)
            <a href="{{ Route('cms.consignment-order.edit', ['id' => $delivery->event_id ]) }}" class="btn btn-outline-primary px-4" role="button">返回明細</a>
        @endif
    </div>

@endsection
@once
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endonce
