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

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
            @if(! $back_item->po_sn && $po_check)
                <a class="btn btn-primary btn-sm my-1 ms-1"
                    href="{{ Route('cms.delivery.return-pay-order', ['id' => $delivery->id]) }}">新增退貨付款單</a>
            @endif
            @if (false == $has_payable_data_back)
                <a class="btn btn-primary btn-sm my-1 ms-1"
                   href="{{ Route('cms.delivery.back_edit', ['event' => $delivery->event, 'eventId' => $delivery->event_id]) }}">編輯退貨</a>
            @endif
            <a target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm my-1 ms-1"
               href="{{ Route('cms.delivery.print_back', ['event' => $delivery->event, 'eventId' => $delivery->event_id]) }}">
                列印退貨單
            </a>
        </div>
    </nav>

    <div class="card shadow p-4 mb-4">
        <h6>銷貨退回明細</h6>
        <dl class="row">
            <div class="col">
                <dt>銷貨退回單號</dt>
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
                <dt>入庫者</dt>
                <dd>{{$delivery->back_inbound_user_name ?? ''}}</dd>
            </div>
        </dl>
        <dl class="row">
{{--            <div class="col">--}}
{{--                <dt>預計進貨日期</dt>--}}
{{--                <dd></dd>--}}
{{--            </div>--}}
            <div class="col">
                <dt>退貨單備註</dt>
                <dd>{{$delivery->back_memo ?? ''}}</dd>
            </div>
            <div class="col">
                <dt>訂貨單號</dt>
                <dd>{{$order->sn ?? ''}}</dd>
            </div>
        </dl>
        @if($back_item->po_sn)
        <dl class="row">
            <div class="col">
                <dt>退貨付款單號</dt>
                <dd><a href="{{ route('cms.delivery.return-pay-order', ['id' => $back_item->delivery_id]) }}" class="-text">{{ $back_item->po_sn }}</a></dd>
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
                        <th scope="col" class="text-center">退回<br class="d-block d-lg-none">數量</th>
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

    @if (null != $ord_items_arr && 0 < count($ord_items_arr))
    <div class="card shadow p-4 mb-4">
        <h6>退回入庫清單</h6>
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th style="width:3rem;">#</th>
                        <th>商品名稱</th>
                        <th>SKU</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ord_items_arr as $key => $ord)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>
                                @if ($ord->combo_product_title)
                                    <span class="badge rounded-pill bg-warning text-dark">組合包</span> [
                                @else
                                    <span class="badge rounded-pill bg-success">一般</span>
                                @endif
                                {{ $ord->product_title }} @if($ord->combo_product_title) ] {{$ord->combo_product_title}} @endif
                            </td>
                            <td>{{ $ord->sku }}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td colspan="2" class="pt-0 ps-0">
                                <table class="table mb-0 table-sm table-hover border-start border-end">
                                    <thead>
                                    <tr class="border-top-0" style="border-bottom-color:var(--bs-secondary);">
                                        <td>入庫單</td>
                                        <td>倉庫</td>
                                        <td>效期</td>
                                        <td class="text-center" style="width: 10%">退回數量</td>
                                        <td>入庫說明</td>
                                    </tr>
                                    </thead>
                                    <tbody class="border-top-0 -appendClone --selectedIB">
                                    @foreach ($ord->receive_depot as $rec)
                                        @if(0 < $rec->back_qty)
                                            <tr class="-cloneElem --selectedIB">
                                                <input type="hidden" name="id[]" value="{{ $rec->id }}">
                                                <input type="hidden" value="{{$ord->total_to_back_qty}}" name="total_to_back_qty[]" class="form-control form-control-sm text-center" readonly>
                                                <td data-td="sn">{{ $rec->inbound_sn }}</td>
                                                <td data-td="depot">{{ $rec->depot_name }}</td>
                                                <td data-td="expiry">{{ date('Y/m/d', strtotime($rec->expiry_date)) }}</td>
                                                <td class="text-center">{{ $rec->back_qty }}</td>
                                                <td>{{ $rec->memo ?? '' }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
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
