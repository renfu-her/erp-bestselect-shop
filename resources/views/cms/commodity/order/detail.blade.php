@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">#{{ $sn }} 訂單明細</h2>

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
            @if (!$receivable)
                <a href="{{ Route('cms.collection_received.create', ['id' => $order->id]) }}"
                    class="btn btn-primary btn-sm my-1 ms-1" role="button">新增收款單</a>
            @endif

            @if ($received_order_data || !in_array($order->status, ['建立']))
                @if (($receivable || in_array($order->status, ['已付款', '已入款', '結案'])) && $received_credit_card_log)
                    <a href="{{ Route('api.web.order.credit_card_checkout', ['id' => $order->id, 'unique_id' => $order->unique_id]) }}"
                        class="btn btn-primary btn-sm my-1 ms-1" role="button" target="_blank">線上刷卡連結</a>
                @else
                    <button type="button" class="btn btn-primary btn-sm my-1 ms-1" disabled>線上刷卡連結</button>
                @endif
            @else
                <a href="{{ Route('api.web.order.payment_credit_card', ['id' => $order->id, 'unique_id' => $order->unique_id]) }}"
                    class="btn btn-primary btn-sm" role="button" target="_blank">線上刷卡連結</a>
            @endif

            <a href="{{ Route('cms.order.bonus-gross', ['id' => $order->id]) }}" class="btn btn-warning btn-sm my-1 ms-1"
                role="button">獎金毛利</a>

            <a href="{{ Route('cms.order.personal-bonus', ['id' => $order->id]) }}"
                class="btn btn-warning btn-sm my-1 ms-1" role="button">個人獎金</a>

            @if ($received_order_data)
                @if (!in_array($order->status, ['已入款', '結案']))
                    <a href="javascript:void(0)" role="button" class="btn btn-outline-danger btn-sm my-1 ms-1"
                        data-bs-toggle="modal" data-bs-target="#confirm-delete"
                        data-href="{{ Route('cms.collection_received.delete', ['id' => $received_order_data->id], true) }}">刪除收款單</a>
                @else
                    <button type="button" class="btn btn-outline-danger btn-sm my-1 ms-1" disabled>刪除收款單</button>
                @endif
            @endif

            @if ($received_order_data && !$order->invoice_number)
                <a href="{{ Route('cms.order.create-invoice', ['id' => $order->id]) }}" role="button"
                    class="btn btn-success btn-sm my-1 ms-1">開立發票</a>
            @endif

            <a href="#" role="button" class="btn btn-outline-success btn-sm my-1 ms-1">訂單完成（暫放）</a>
            @if ($canCancel)
                <a href="{{ Route('cms.order.cancel-order', ['id' => $order->id]) }}" role="button" class="btn btn-outline-danger btn-sm my-1 ms-1">取消訂單</a>
            @endif

            <a href="{{ Route('cms.order.split-order', ['id' => $order->id]) }}" role="button" class="btn btn-outline-success btn-sm my-1 ms-1">分割訂單</a>


        </div>
    </nav>

    <form id="form1" method="post" action="">
        @method('POST')
        @csrf

        @error('id')
            <div class="alert alert-danger mt-3">{{ $message }}</div>
        @enderror

        <div class="card shadow p-4 mb-4">
            <h6>訂單明細</h6>
            <dl class="row">
                <div class="col">
                    <dt>訂單編號</dt>
                    <dd>{{ $order->sn }}</dd>
                </div>
                <div class="col">
                    <dt>訂購時間</dt>
                    <dd>{{ $order->created_at }}</dd>
                </div>
                <div class="col-sm-5">
                    <dt>E-mail</dt>
                    <dd>{{ $order->email }}</dd>
                </div>
            </dl>
            <dl class="row">
                <div class="col">
                    <dt>付款方式</dt>
                    <dd>{{ $order->payment_method_title }}</dd>
                </div>
                <div class="col">
                    <dt>訂單狀態</dt>
                    <dd>{{ $order->status }}</dd>
                </div>
                <div class="col-sm-5">
                    <dt>收款單號</dt>
                    <dd>
                        @if ($receivable)
                            <a href="{{ route('cms.collection_received.receipt', ['id' => $order->id]) }}"
                                class="-text">{{ $received_order_data ? $received_order_data->sn : '' }}</a>
                        @else
                            <span>尚未完成收款</span>
                        @endif
                    </dd>
                </div>
            </dl>
            <dl class="row">
                <div class="col">
                    <dt>購買人姓名</dt>
                    <dd>{{ $order->ord_name }}</dd>
                </div>
                <div class="col">
                    <dt>購買人電話</dt>
                    <dd>{{ $order->ord_phone }}</dd>
                </div>
                <div class="col-md-5">
                    <dt>購買人地址</dt>
                    <dd>{{ $order->ord_address }}</dd>
                </div>
            </dl>
            <dl class="row">
                <div class="col">
                    <dt>收件人姓名</dt>
                    <dd>{{ $order->rec_name }}</dd>
                </div>
                <div class="col">
                    <dt>收件人電話</dt>
                    <dd>{{ $order->rec_phone }}</dd>
                </div>
                <div class="col-md-5">
                    <dt>收件人地址</dt>
                    <dd>{{ $order->ord_address }}</dd>
                </div>
            </dl>
            <dl class="row">
                <div class="col">
                    <dt>發票類型</dt>
                    <dd>{{ $order->invoice_number ? $order->invoice_category : '尚未開立發票' }}</dd>
                </div>
                <div class="col">
                    <dt>發票號碼</dt>
                    <dd>
                        @if ($order->invoice_number)
                            <a href="{{ route('cms.order.show-invoice', ['id' => $order->id]) }}"
                                class="-text">{{ $order->invoice_number ? $order->invoice_number : '' }}</a>
                        @else
                            <span>尚未開立發票</span>
                        @endif
                    </dd>
                </div>
                <div class="col-md-5">
                    <dt>電子發票資訊</dt>
                    <dd>{{ $order->carrier_type ?? '' }} {{ $order->carrier_num ?? '' }}</dd>
                </div>
            </dl>
            <dl class="row">
                <div class="col">
                    <dt>推薦業務員</dt>
                    <dd>{{ $order->name_m ?? '' }} {{ $order->sn_m ?? '' }}

                        <a href="#" data-bs-toggle="modal" data-bs-target="#change-mcode" title="編輯"
                            class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                            <i class="bi bi-pencil-square"></i>
                        </a>

                    </dd>
                </div>
                <div class="col col-md-5">
                    <dt>統編</dt>
                    <dd>{{ $order->invoice_number ? $order->gui_number : '尚未開立發票' }}</dd>
                </div>
            </dl>
            <dl class="row">
                <div class="col">
                    <dt>寄件人</dt>
                    <dd>{{ $order->sed_name }}</dd>
                </div>
                <div class="col-md-5">
                    <dt>寄件人地址</dt>
                    <dd>{{ $order->sed_address }}</dd>
                </div>
            </dl>
            <dl class="row">
                <div class="col">
                    <dt>銷售通路</dt>
                    <dd>{{ $order->sale_title }}</dd>
                </div>
                <div class="col">
                    <dt>訂單備註</dt>
                    <dd>{{ $order->note }}</dd>
                </div>
                <div class="col-5">
                    <dt>付款狀態</dt>
                    <dd>{{ $order->payment_status_title }}</dd>
                </div>
            </dl>
            @if (isset($remit))
                <dl class="row">
                    <div class="col">
                        <dt>匯款人姓名</dt>
                        <dd>{{ $remit->name }}</dd>
                    </div>
                    <div class="col">
                        <dt>匯款金額</dt>
                        <dd>{{ number_format($remit->price) }}</dd>
                    </div>
                    <div class="col">
                        <dt>匯款日期</dt>
                        <dd>{{ $remit->remit_date }}</dd>
                    </div>
                    <div class="col">
                        <dt>帳號後五碼</dt>
                        <dd>{{ $remit->bank_code }}</dd>
                    </div>
                    <div class="col-sm-2">
                        <dt>上傳時間</dt>
                        <dd>{{ $remit->created_at }}</dd>
                    </div>
                </dl>
            @endif
        </div>
        @php
            $dlv_fee = 0;
            $price = 0;

        @endphp
        @foreach ($subOrders as $subOrder)
            @php
                $dlv_fee += $subOrder->dlv_fee;
                $price += $subOrder->total_price;
            @endphp
            {{-- 宅配 .-detail-primary / 自取 .-detail-warning / 超取 .-detail-success --}}
            @if (true == isset($subOrderId) && $subOrder->id != $subOrderId)
                @continue
            @endif
            <div @class([
                'card shadow mb-4 -detail',
                '-detail-primary' => $subOrder->ship_category === 'deliver',
                '-detail-warning' => $subOrder->ship_category === 'pickup',
            ])>
                <div class="card-header px-4 d-flex align-items-center bg-white flex-wrap justify-content-end">
                    <strong class="flex-grow-1 mb-0">{{ $subOrder->ship_event }}</strong>
                    <span class="badge -badge fs-6">{{ $subOrder->ship_category_name }}</span>
                    @if (true == isset($subOrderId))
                        <div class="col-12 d-flex justify-content-end mt-2 flex-wrap">
                            <a class="btn btn-sm btn-success -in-header mb-1"
                                href="{{ Route('cms.logistic.changeLogisticStatus', ['event' => \App\Enums\Delivery\Event::order()->value, 'eventId' => $subOrderId], true) }}">配送狀態</a>
                            <a class="btn btn-sm btn-success -in-header mb-1"
                                href="{{ Route('cms.logistic.create', ['event' => \App\Enums\Delivery\Event::order()->value, 'eventId' => $subOrderId], true) }}">物流設定</a>
                            <a class="btn btn-sm btn-success -in-header mb-1"
                                href="{{ Route('cms.delivery.create', ['event' => \App\Enums\Delivery\Event::order()->value, 'eventId' => $subOrderId], true) }}">出貨審核</a>
                            {{-- @if ('pickup' == $subOrder->ship_category) --}}
                            {{-- <a class="btn btn-sm btn-success -in-header" href="{{ Route('cms.order.inbound', ['subOrderId' => $subOrderId], true) }}">入庫審核</a> --}}
                            {{-- @endif --}}

                            @if(isset($delivery) && isset($delivery->back_date))
                                @if( false == isset($delivery->back_inbound_date))
                                    <button type="button"
                                            data-href="{{ Route('cms.delivery.back_delete', ['deliveryId' => $delivery->id], true) }}"
                                            data-bs-toggle="modal" data-bs-target="#confirm-delete-back"
                                            class="btn btn-sm btn-danger -in-header mb-1">
                                        刪除退貨
                                    </button>
                                @endif
                                <a class="btn btn-sm btn-success -in-header mb-1"
                                   href="{{ Route('cms.delivery.back_detail', ['event' => \App\Enums\Delivery\Event::order()->value, 'eventId' => $subOrderId], true) }}">銷貨退回明細</a>
{{--                                <a class="btn btn-sm btn-success -in-header mb-1"--}}
{{--                                   href="{{ Route('cms.delivery.back_inbound', ['event' => \App\Enums\Delivery\Event::order()->value, 'eventId' => $subOrderId], true) }}">退貨入庫審核</a>--}}
                            @else
                                <a class="btn btn-sm btn-success -in-header mb-1"
                                   href="{{ Route('cms.delivery.back', ['event' => \App\Enums\Delivery\Event::order()->value, 'eventId' => $subOrderId], true) }}">退貨</a>
                            @endif

                            <a target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary -in-header mb-1"
                                href="{{ Route('cms.order.print_order_sales', ['id' => $order->id, 'subOrderId' => $subOrderId]) }}">
                                列印銷貨單
                            </a>
                            <a target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary -in-header mb-1"
                                href="{{ Route('cms.order.print_order_ship', ['id' => $order->id, 'subOrderId' => $subOrderId]) }}">
                                列印出貨單
                            </a>
                        </div>
                    @endif
                </div>
                <div class="card-body px-4">
                    <dl class="row mb-0">
                        <div class="col-6 col-md-3">
                            <dt>溫層</dt>
                            <dd>{{ $subOrder->ship_temp ?? '-' }}</dd>
                        </div>
                        <div class="col-6 col-md-3">
                            <dt>訂單編號</dt>
                            <dd>{{ $subOrder->sn }}</dd>
                        </div>
                        <div class="col-6 col-md-3">
                            <dt>出貨單號</dt>
                            <dd>{{ $subOrder->delivery_sn ?? '(待處理)' }}</dd>
                        </div>
                        <div class="col-6 col-md-3">
                            <dt>消費者物流費用</dt>
                            <dd>${{ number_format($subOrder->dlv_fee) }}</dd>
                        </div>
                    </dl>
                </div>
                <div class="card-body px-4 py-0">
                    <div class="table-responsive tableOverBox">
                        <table class="table tableList table-sm mb-0">
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th scope="col">商品名稱</th>
                                    <th scope="col">SKU</th>
                                    <th scope="col">單價</th>
                                    <th scope="col">數量</th>
                                    <th scope="col">小計</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subOrder->items as $item)
                                    <tr>
                                        <td><a href="{{ Route('cms.product.edit', ['id' => $item->product_id], true) }}"
                                                class="-text">{{ $item->product_title }}</a></td>
                                        <td>{{ $item->sku }}</td>
                                        <td>${{ number_format($item->price) }}</td>
                                        <td>{{ $item->qty }}</td>
                                        <td>${{ number_format($item->total_price) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- <div class="card-body px-4 py-0" hidden>
                    <div class="table-responsive tableOverBox">
                        <table class="table tableList table-sm mb-0">
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th scope="col">優惠類型</th>
                                    <th scope="col">優惠名稱</th>
                                    <th scope="col">贈品</th>
                                    <th scope="col">金額</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>贈品</td>
                                    <td>-</td>
                                    <td>滑鼠墊</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>金額</td>
                                    <td>滿額贈</td>
                                    <td>-</td>
                                    <td class="text-danger">- ${{ number_format(50) }}</td>
                                </tr>
                                <tr>
                                    <td>優惠劵</td>
                                    <td>優惠劵序號</td>
                                    <td>-</td>
                                    <td class="text-danger">- ${{ number_format(60) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div> --}}

                <div class="card-header px-4 text-secondary border-top">物流資訊</div>
                <div class="card-body px-4 pb-4">
                    <dl class="row">
                        <div class="col">
                            <dt>物流付款單@if ($subOrder->payable_balance_date)
                                    <span class="text-danger">（已付款完成）</span>
                                @endif
                            </dt>
                            <dd>
                                @if ($subOrder->ship_group_name == '')
                                    尚未設定物流
                                @elseif(false == isset($subOrder->delivery_audit_date))
                                    尚未做出貨審核
                                @else
                                    @if ($subOrder->payable_sn)
                                        <a href="{{ Route('cms.order.pay-order', ['id' => $subOrder->order_id, 'sid' => $subOrder->id]) }}"
                                            class="text-decoration-none">付款單號-{{ $subOrder->payable_sn }}</a>
                                    @else
                                        <input type="hidden" class="form_url"
                                            value="{{ Route('cms.order.pay-order', ['id' => $subOrder->order_id, 'sid' => $subOrder->id]) }}">
                                        <button type="button"
                                            class="btn btn-link text-decoration-none p-0 m-0 submit_btn">新增付款單</button>
                                    @endif
                                @endif
                            </dd>
                        </div>
                        <div class="col">
                            <dt>客戶物流方式</dt>
                            <dd>{{ $subOrder->ship_event }}</dd>
                        </div>
                        <div class="col">
                            <dt>實際物流</dt>
                            <dd>{{ $subOrder->ship_group_name ?? '(待處理)' }}</dd>
                        </div>
                    </dl>
                    <dl class="row">
                        <div class="col">
                            <dt>包裹編號</dt>
                            <dd>
                                @if (false == empty($subOrder->projlgt_order_sn))
                                    <a href="{{ env('LOGISTIC_URL') . 'guest/order-flow/' . $subOrder->projlgt_order_sn }}"
                                        class="btn btn-link">
                                        {{ $subOrder->projlgt_order_sn }}
                                    </a>
                                @else
                                    {{ $subOrder->package_sn ?? '(待處理)' }}
                                @endif
                            </dd>
                        </div>
                        <div class="col">
                            <dt>物態</dt>
                            <dd>{{ $subOrder->logistic_status ?? '(待處理)' }}</dd>
                        </div>
                        <div class="col">
                            <dt>物流廠商</dt>
                            <dd>{{ $subOrder->supplier_name ?? '' }}</dd>
                        </div>
                    </dl>
                    <dl class="row">
                        <div class="col">
                            <dt>物流成本</dt>
                            <dd>{{ $subOrder->logistic_cost ?? '(待處理)' }}</dd>
                        </div>
                        <div class="col-8">
                            <dt>物流備註</dt>
                            <dd>{{ $subOrder->logistic_memo ?? '(待處理)' }}</dd>
                        </div>
                    </dl>
                </div>

                @if (true == isset($subOrder->consume_items) && 0 < count($subOrder->consume_items))
                    <div class="card-header px-4 text-secondary border-top">物流耗材清單</div>
                    <div class="card-body px-4 py-0">
                        <div class="table-responsive tableOverBox">
                            <table class="table tableList table-sm mb-0">
                                <thead class="table-light text-secondary">
                                    <tr>
                                        <th scope="col">耗材名稱</th>
                                        <th scope="col">SKU</th>
                                        <th scope="col">數量</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($subOrder->consume_items as $consume_key => $consume_item)
                                        <tr>
                                            <td><a href="#" class="-text">{{ $consume_item->product_title }}</a>
                                            </td>
                                            <td>{{ $consume_item->sku }}</td>
                                            <td>{{ $consume_item->qty }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach


        @if (false == isset($subOrderId))
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
                <div class="d-flex align-items-center mb-4 mt-3">
                    <h6 class="flex-grow-1 mb-0">訂單總覽</h6>

                    <div class="form-check form-check-inline form-switch form-switch-lg">

                        <label class="form-check-label">
                            <input class="form-check-input -auto-send" type="checkbox" name="" value=""
                                @if ($order->auto_dividend == '1') checked @endif
                                @if ($order->allotted_dividend) disabled @endif>
                            鴻利、優惠劵自動發放
                        </label>
                    </div>
                    @if ($order->allotted_dividend === 0)
                        <button type="button" class="btn btn-sm btn-success -in-header -active-send"
                            disabled>手動發放</button>
                    @endif
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle d-sm-table d-none text-nowrap">
                        @if (!$order->allotted_dividend)
                            <caption class="small text-end">鴻利預計發放時間：
                                @if (isset($order->dividend_active_at))
                                    {{ date('Y/m/d H:i', strtotime($order->dividend_active_at)) }}
                                @else
                                    未入款
                                @endif
                            </caption>
                        @endif

                        <tbody class="border-top-0">
                            <tr class="table-light">
                                <td class="col-2">小計</td>
                                <td class="col-2">折扣</td>
                                <td class="col-2 lh-sm">折扣後 <br class="d-xxl-none">(不含運)</td>
                                <td class="col-2">運費</td>
                                <td class="col-2">總金額</td>
                                <td class="col-2 lh-sm">
                                    @if ($order->allotted_dividend)
                                        獲得<a href="{{ route('cms.sale_channel.index') }}" class="-text">鴻利</a>
                                    @else
                                        預計獲得<a href="{{ route('cms.sale_channel.index') }}"
                                            class="-text d-block d-xxl-inline">鴻利點數</a>
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
                    <table class="table table-bordered table-sm text-center align-middle d-table d-sm-none">
                        @if (!$order->allotted_dividend)
                            <caption class="small text-end">鴻利預計發放時間：
                                @if (isset($order->dividend_active_at))
                                    {{ date('Y/m/d H:i', strtotime($order->dividend_active_at)) }}
                                @else
                                    未入款
                                @endif
                            </caption>
                        @endif
                        <tbody class="border-top-0">
                            <tr style="border-color: #dfe0e1;">
                                <td class="col-7 table-light">小計</td>
                                <td class="text-end pe-4">${{ number_format($order->origin_price) }}</td>
                            </tr>
                            <tr>
                                <td class="col-7 table-light">折扣 </td>
                                <td class="text-danger text-end pe-4">- ${{ number_format($order->discount_value) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="col-7 table-light lh-sm">折扣後 (不含運)</td>
                                <td class="text-end pe-4">${{ number_format($order->discounted_price) }}</td>
                            </tr>
                            <tr>
                                <td class="col-7 table-light">運費</td>
                                <td class="text-end pe-4">${{ number_format($order->dlv_fee) }}</td>
                            </tr>
                            <tr>
                                <td class="col-7 table-light">總金額</td>
                                <td class="fw-bold text-end pe-4">${{ number_format($order->total_price) }}</td>
                            </tr>
                            <tr>
                                <td class="col-7 table-light lh-sm">
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
        @endif

        <div id="submitDiv">
            <div class="col-auto">
                @if (false == isset($subOrderId))
                    <button type="submit" class="btn btn-primary px-4">列印整張訂購單</button>
                    <a href="{{ Route('cms.order.index') }}" class="btn btn-outline-primary px-4"
                        role="button">返回列表</a>
                @else
                    <a href="{{ Route('cms.delivery.index') }}" class="btn btn-outline-primary px-4"
                        role="button">返回列表</a>
                @endif
            </div>
        </div>
    </form>

    <!-- Modal -->
    <x-b-modal id="confirm-delete">
        <x-slot name="title">刪除確認</x-slot>
        <x-slot name="body">確認要刪除此收款單？</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
        </x-slot>
    </x-b-modal>
    <x-b-modal id="confirm-delete-back">
        <x-slot name="title">刪除確認</x-slot>
        <x-slot name="body">確認要刪除？</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
        </x-slot>
    </x-b-modal>

    <div class="modal fade" id="change-mcode" tabindex="-1" aria-labelledby="change-mcodeLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('cms.order.change-bonus-owner', ['id' => $order->id]) }}" method="post">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="change-mcodeLabel">更改推薦業務員</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="col-12 mb-3">
                            <label class="form-label">1. 請先搜尋</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control -search" placeholder="請輸入業務員姓名"
                                    aria-label="業務員姓名" aria-describedby="業務員姓名">
                                <button class="btn btn-outline-primary px-4 -search" type="button">搜尋</button>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">2. 選擇業務</label>
                            <select class="form-select" name="customer_id">
                                <option>請選擇</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                        <button type="submit" class="btn btn-primary px-4">確認</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@once
    @push('sub-styles')
        <link rel="stylesheet" href="{{ Asset('dist/css/order.css') }}">
        <style>
            .table.table-bordered:not(.table-sm) tr:not(.table-light) {
                height: 70px;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            $('.submit_btn').on('click', function(e) {
                e.preventDefault();
                let url = $(this).prev().val();
                $('#form1').attr('action', url).submit();
            });

            // Modal Control
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
            $('#confirm-delete-back').on('show.bs.modal', function (e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });

            // 更換推薦業務
            const getCustomersUrl = @json(route('api.cms.user.get-customers'));
            $('#change-mcode button.-search').off('click').on('click', function() {
                const keyword = $('#change-mcode input.-search').val();
                if (!keyword) {
                    return false;
                }
                const $select = $('#change-mcode select');
                $select.empty();

                axios.post(getCustomersUrl, {
                    profit: 1,
                    keyword: keyword
                }).then((result) => {
                    console.log(result.data);
                    if (result.data.status === '0' && result.data.data.length > 0) {
                        $select.append('<option>請選擇</option>');
                        (result.data.data)
                        .forEach(c => {
                            $select.append(`<option value="${c.id}">${c.name} ${c.mcode}</option>`);
                        });
                    } else {
                        $select.append('<option>查無資料</option>');
                    }
                }).catch((err) => {
                    console.error(err);
                    toast.show('發生錯誤', {
                        type: 'danger'
                    });
                });
            });

            // 發放紅利
            const changeAutoUrl = @json(route('api.cms.order.change-auto-dividend'));
            const activePointUrl = @json(route('api.cms.order.active-dividend'));
            const order_sn = @json($order->sn);

            setAutoSend($('input.-auto-send').prop('checked'));
            $('input.-auto-send').off('change.auto').on('change.auto', function() {
                const $switch = $(this);
                const active = $switch.prop('checked');

                // API
                axios.post(changeAutoUrl, {
                    order_sn: order_sn,
                    auto_dividend: active ? 1 : 0
                }).then((result) => {
                    console.log(result.data);
                    if (result.data.status === '0') {
                        setAutoSend(active);
                        if (active) {
                            toast.show('鴻利改為自動發放');
                        } else {
                            toast.show('鴻利改為手動發放', {
                                type: 'warning'
                            });
                        }
                    } else {
                        toast.show(`失敗：${result.data.message}`, {
                            type: 'danger'
                        });
                    }
                }).catch((err) => {
                    console.error(err);
                    toast.show('發生錯誤', {
                        type: 'danger'
                    });
                });
            });

            function setAutoSend(auto) {
                $('.btn.-active-send').prop('disabled', auto);
            }

            // 手動發放紅利
            $('.btn.-active-send').off('click.send').on('click.send', function() {
                if (!$(this).prop('disabled')) {
                    // API
                    axios.post(activePointUrl, {
                        order_sn: order_sn
                    }).then((result) => {
                        console.log(result.data);
                        if (result.data.status === '0') {
                            toast.show('已發放鴻利、優惠劵');
                            $('.badge.bg-secondary').removeClass('bg-secondary')
                                .addClass('bg-success').text('已發');
                            $('input.-auto-send').prop('disabled', true);
                        } else {
                            toast.show(`失敗：${result.data.message}`, {
                                type: 'danger'
                            });
                        }
                    }).catch((err) => {
                        console.error(err);
                        toast.show('發生錯誤', {
                            type: 'danger'
                        });
                    });
                }
            });
        </script>
    @endpush
@endonce
