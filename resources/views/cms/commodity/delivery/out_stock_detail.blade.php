@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">#{{ $breadcrumb_data['sn'] }} 缺貨明細</h2>
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

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
            @if(! $items->po_sn && $po_check)
                <a class="btn btn-primary btn-sm my-1 ms-1"
                    href="{{ Route('cms.delivery.roe-po', ['id' => $delivery->id, 'behavior' => 'out']) }}">新增缺貨付款單</a>
            @endif
            @if (false == $has_payable_data_back)
{{--                <a class="btn btn-primary btn-sm my-1 ms-1"--}}
{{--                   href="{{ Route('cms.delivery.out_stock_edit', ['event' => $delivery->event, 'eventId' => $delivery->event_id]) }}">編輯缺貨</a>--}}
            @endif
            <a target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm my-1 ms-1"
               href="{{ Route('cms.delivery.print_out_stock', ['event' => $delivery->event, 'eventId' => $delivery->event_id]) }}">
                列印缺貨單
            </a>
        </div>
    </nav>

    <div class="card shadow p-4 mb-4">
        <h6>缺貨明細</h6>
        <dl class="row">
            <div class="col">
                <dt>缺貨單號</dt>
                <dd>{{$delivery->out_sn ?? ''}}</dd>
            </div>
            <div class="col">
                <dt>出貨日期</dt>
                <dd>{{ $delivery->audit_date ? date('Y/m/d', strtotime($delivery->audit_date)) : '' }}</dd>
            </div>
        </dl>
        <dl class="row">
{{--            <div class="col">--}}
{{--                <dt>代墊單</dt>--}}
{{--                <dd></dd>--}}
{{--            </div>--}}
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
                <dd>{{$delivery->out_user_name ?? ''}}</dd>
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
{{--            <div class="col">--}}
{{--                <dt>課稅別</dt>--}}
{{--                <dd></dd>--}}
{{--            </div>--}}
        </dl>
        <dl class="row">
            <div class="col">
                <dt>進貨地址</dt>
                <dd>{{$order->ord_address ?? ''}}</dd>
            </div>
        </dl>
        <dl class="row">
{{--            <div class="col-8">--}}
{{--                <dt>物流說明</dt>--}}
{{--                <dd>{{ (isset($logistic->memo)) ? $logistic->memo : '' }}</dd>--}}
{{--            </div>--}}
            <div class="col">
                <dt>出貨者</dt>
                <dd>{{$delivery->audit_user_name ?? ''}}</dd>
            </div>
        </dl>
        <dl class="row">
{{--            <div class="col">--}}
{{--                <dt>預計進貨日期</dt>--}}
{{--                <dd></dd>--}}
{{--            </div>--}}
            <div class="col">
                <dt>缺貨單備註</dt>
                <dd>{{$delivery->out_memo ?? ''}}</dd>
            </div>
            <div class="col">
                <dt>訂貨單號</dt>
                <dd>{{$order->sn ?? ''}}</dd>
            </div>
        </dl>
        @if($items->po_sn)
        <dl class="row">
            <div class="col">
                <dt>缺貨付款單號</dt>
                <dd><a href="{{ route('cms.delivery.roe-po', ['id' => $items->delivery_id, 'behavior' => 'out']) }}" class="-text">{{ $items->po_sn }}</a></dd>
            </div>
        </dl>
        @endif
    </div>

    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox mb-3">
            <table class="table tableList table-striped mb-1">
                <thead class="small align-middle">
                    <tr>
                        <th scope="col" class="text-center" style="width:40px">#</th>
                        <th scope="col">品名規格</th>
                        <th scope="col" class="text-end">退款<br class="d-block d-lg-none">金額</th>
{{--                        <th scope="col" class="text-end">經銷價</th>--}}
                        <th scope="col" class="text-end">扣除<br class="d-block d-lg-none">獎金</th>
                        <th scope="col" class="text-center">缺貨<br class="d-block d-lg-none">數量</th>
                        <th scope="col" class="text-end">小計</th>
                        <th scope="col">說明</th>
                    </tr>
                </thead>
                @php
                    $total = 0;
                @endphp
                <tbody>
                     @foreach ($dlvBack as $key => $item)
                         @if(1 == ($item->show?? null))
                        @php
                            $subtotal = $item->price * $item->back_qty;    // 退款金額 * 退回數量
                            $total += $subtotal;
                        @endphp
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td class="wrap lh-sm">{{ $item->product_title ?? '' }}</td>
                            <td class="text-end">${{ number_format($item->price, 2) }}</td>
{{--                            <td class="text-end">${{ number_format(450) }}</td>--}}
                            <td class="text-end">${{ number_format($item->bonus, 2) }}</td>
                            <td class="text-center">{{ number_format($item->back_qty) }}</td>
                            <td class="text-end">${{ number_format($subtotal) }}</td>
                            <td>{{ $item->memo ?? '' }}</td>
                        </tr>
                        @endif
                     @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5">合計</th>
                        <td class="text-end">${{ number_format($total) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @if (null != $dlv_other_items && 0 < count($dlv_other_items))
        <div class="card shadow p-4 mb-4">
            <h8>其他項目</h8>
            <div class="table-responsive tableOverBox mb-3">
                <table class="table tableList table-striped mb-1">
                    <thead class="small align-middle">
                    <tr>
                        <th scope="col" class="text-center" style="width:40px">#</th>
                        <th scope="col">會計科目</th>
                        <th scope="col">項目</th>
                        <th scope="col" class="text-end">金額（單價）</th>
                        <th scope="col" class="text-center">數量</th>
                        <th scope="col">備註</th>
                    </tr>
                    </thead>
                    @php
                        $doi_total = 0;
                    @endphp
                    <tbody>
                    @foreach ($dlv_other_items as $key => $val_dli)
                        @php
                            $doi_subtotal = $val_dli->price * $val_dli->qty;    // 退款金額 * 退回數量
                            $doi_total += $doi_subtotal;
                        @endphp
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td class="wrap lh-sm">{{ $val_dli->grade_code }} {{ $val_dli->grade_name }}</td>
                            <td class="wrap lh-sm">{{ $val_dli->product_title }}</td>
                            <td class="text-end">{{ $val_dli->price }}</td>
                            <td class="text-center">{{ $val_dli->qty }}</td>
                            <td>{{ $val_dli->memo ?? '' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="3">合計</th>
                        <td class="text-end">${{ number_format($doi_total) }}</td>
                        <td></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

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
    @push('sub-styles')
        <style>
            .tableList > thead > * > * {
                line-height: initial;
            }
        </style>
    @endpush
@endonce
