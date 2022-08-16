@extends('layouts.main')
@section('sub-content')
    
    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
            @if(! $received_order->receipt_date)
                <a href="{{ route('cms.collection_received.review', ['id' => $received_order->source_id]) }}" class="btn btn-sm btn-primary" role="button">收款單入款審核</a>
            @else
                @if(! $data_status_check)
                <a href="{{ route('cms.collection_received.review', ['id' => $received_order->source_id]) }}" class="btn btn-sm btn-outline-danger" role="button">取消入帳</a>
                @endif
            @endif

            <a href="{{ route('cms.collection_received.taxation', ['id' => $received_order->source_id]) }}" class="btn btn-sm btn-dark" role="button">修改摘要/稅別</a>

            <a href="{{ route('cms.collection_received.print_received') }}" target="_blank" class="btn btn-sm btn-warning" rel="noopener noreferrer">中一刀列印畫面</a>
            {{--
            <button type="submit" class="btn btn-sm btn-warning">A4列印畫面</button>
            <button type="submit" class="btn btn-dark">修改記錄</button>
            <button type="submit" class="btn btn-dark">明細修改記錄</button>
            --}}
        </div>
    </nav>

    <div class="card shadow p-4 mb-4">
        <div class="mb-3">
            <h4 class="text-center">喜鴻國際企業股份有限公司</h4>
            <div class="text-center small mb-2">
                <span>地址：台北市中山區松江路148號6樓之2</span>
                <span class="ms-3">電話：02-25637600</span>
                <span class="ms-3">傳真：02-25711377</span>
            </div>
            <h4 class="text-center">收　款　單</h4>
            <hr>
            <dl class="row mb-0">
                <div class="col">
                    <dd>客戶：{{ $order_purchaser->name }}</dd>
                </div>
                <div class="col">
                    <dd>地址：{{ $order_purchaser->address }}</dd>
                </div>
            </dl>
            <dl class="row mb-0">
                <div class="col">
                    <dd>電話：{{ $order_purchaser->phone }}</dd>
                </div>
                <div class="col">
                    <dd>傳真：{{ $order_purchaser->fax }}</dd>
                </div>
            </dl>
            <hr class="mt-2">
            <dl class="row mb-0">
                <div class="col">
                    <dd>收款單號：{{ $received_order->sn }}</dd>
                </div>
                <div class="col">
                    <dd>製表日期：{{ date('Y/m/d', strtotime($received_order->created_at)) }}</dd>
                </div>
            </dl>
            <dl class="row mb-0">
                <div class="col">
                    <dd>訂單流水號：<a href="{{ Route('cms.order.detail', ['id' => $order->id], true) }}">{{ $order->sn }}</a></dd>
                </div>
                @if($received_order->receipt_date)
                <div class="col">
                    <dd>入帳日期：{{ date('Y-m-d', strtotime($received_order->receipt_date)) }}</dd>
                </div>
                @endif
            </dl>
            <dl class="row mb-0">
                <div class="col">
                    <dd>收款對象：
                        {{--
                            <a href="{{ $supplierUrl }}" target="_blank"> {{ $supplier->name }}
                                <span class="icon"><i class="bi bi-box-arrow-up-right"></i></span>
                            </a>
                        --}}
                    </dd>
                </div>
                <div class="col">
                    <dd>承辦人：{{ $undertaker ? $undertaker->name : '' }}</dd>
                </div>
            </dl>
        </div>

        <div class="mb-2">
            <div class="table-responsive tableoverbox">
                <table class="table tablelist table-sm mb-0 align-middle">
                    <thead class="table-light text-secondary text-nowrap">
                        <tr>
                            <th scope="col">收款項目</th>
                            <th scope="col" class="text-end">數量</th>
                            <th scope="col" class="text-end">單價</th>
                            <th scope="col" class="text-end">應收金額</th>
                            <th scope="col">備註</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order_list_data as $value)
                            <tr>
                                <td>{{ $product_grade_name }} --- {{ $value->product_title }}{{'（' . $value->del_even . ' - ' . $value->del_category_name . '）'}}{{'（' . $value->product_price . ' * ' . $value->product_qty . '）'}}</td>
                                <td class="text-end">{{ number_format($value->product_qty) }}</td>
                                <td class="text-end">{{ number_format($value->product_price, 2) }}</td>
                                <td class="text-end">{{ number_format($value->product_origin_price) }}</td>
                                <td>{{ $received_order->memo }} <a href="{{ Route('cms.order.detail', ['id' => $order->id], true) }}">{{ $order->sn }}</a> {{ $value->product_taxation == 1 ? '應稅' : '免稅' }} {{ $value->product_note }}{{-- $order->note --}}</td>
                            </tr>
                        @endforeach

                        @if($order->dlv_fee > 0)
                            <tr>
                                <td>{{ $logistics_grade_name }}</td>
                                <td class="text-end">1</td>
                                <td class="text-end">{{ number_format($order->dlv_fee, 2) }}</td>
                                <td class="text-end">{{ number_format($order->dlv_fee) }}</td>
                                <td>{{ $received_order->memo }} <a href="{{ Route('cms.order.detail', ['id' => $order->id], true) }}">{{ $order->sn }}</a> {{ $order->dlv_taxation == 1 ? '應稅' : '免稅' }}</td>
                            </tr>
                        @endif

                        @if($order->discount_value > 0)
                        @foreach($order_discount ?? [] as $d_value)
                            <tr>
                                <td>{{ $d_value->account_code }} {{ $d_value->account_name }} - {{ $d_value->title }}</td>
                                <td class="text-end">1</td>
                                <td class="text-end">-{{ number_format($d_value->discount_value, 2) }}</td>
                                <td class="text-end">-{{ number_format($d_value->discount_value) }}</td>
                                <td>{{ $received_order->memo }} <a href="{{ Route('cms.order.detail', ['id' => $order->id], true) }}">{{ $order->sn }}</a> {{ $d_value->discount_taxation == 1 ? '應稅' : '免稅' }}</td>
                            </tr>
                        @endforeach
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="3">
                                <div class="d-flex justify-content-between">
                                    <span>合計：</span>
                                    <span>（{{ $zh_price }}）</span>
                                </div>
                            </td>
                            <td class="text-end">{{ number_format($received_order->price) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="mb-3">
            @foreach($received_data as $value)
            <dl class="row mb-0">
                <div class="col-12">
                    <dd>
                        {{ $value->account->code . ' ' . $value->account->name }}
                        {{ number_format($value->credit_card_price ?? $value->tw_price) }}
                        @if($value->received_method == 'credit_card')
                            {{ '（' . $value->received_method_name . ' - ' . $value->credit_card_number . '（' . $value->credit_card_owner_name . '）' . '）' }}
                        @elseif($value->received_method == 'remit')
                            {{ '（' . $value->received_method_name . ' - ' . $value->summary . '（' . $value->remit_memo . '）' . '）' }}
                        @elseif($value->received_method == 'cheque')
                            {!! '（<a href="' . route('cms.note_receivable.record', ['id'=>$value->received_method_id]) . '">' . $value->received_method_name . ' - ' . $value->cheque_ticket_number . '（' . date('Y-m-d', strtotime($value->cheque_due_date)) . '）' . '</a>）' !!}
                        @else
                            {{ '（' . $value->received_method_name . ' - ' . $value->account->name . ' - ' . $value->summary . '）' }}
                        @endif
                    </dd>
                </div>
            </dl>
            @endforeach
        </div>

        <div>
            <dl class="row">
                <div class="col">
                    <dd>財務主管：</dd>
                </div>
                <div class="col">
                    <dd>會計：{{ $accountant }}</dd>
                </div>
                <div class="col">
                    <dd>商品主管：</dd>
                </div>
                <div class="col">
                    <dd>商品負責人：{{-- $product_qc --}}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="col-auto">
        <a href="{{ Route('cms.order.detail', ['id' => $received_order->source_id]) }}" class="btn btn-outline-primary px-4" role="button">
            返回明細
        </a>
    </div>
@endsection

@once
    @push('sub-scripts')
    @endpush
@endonce