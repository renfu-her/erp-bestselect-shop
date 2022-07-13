@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-3">收款單</h2>
    <a href="{{ Route('cms.order.detail', ['id' => $received_order->source_id]) }}" class="btn btn-primary" role="button">
        <i class="bi bi-arrow-left"></i> 返回上一頁
    </a>
    @if(! $received_order->receipt_date)
    <a href="{{ route('cms.collection_received.review', ['id' => $received_order->source_id]) }}" class="btn btn-primary px-4" role="button">收款單入款審核</a>
    @else
    <a href="{{ route('cms.collection_received.review', ['id' => $received_order->source_id]) }}" class="btn btn-outline-success px-4" role="button">取消入帳</a>
    @endif
    <a href="{{ route('cms.collection_received.taxation', ['id' => $received_order->source_id]) }}" class="btn btn-outline-success px-4" role="button">修改摘要/稅別</a>
    {{--
    <button type="submit" class="btn btn-danger">中一刀列印畫面</button>
    <button type="submit" class="btn btn-danger">A4列印畫面</button>
    <button type="submit" class="btn btn-danger">修改記錄</button>
    <button type="submit" class="btn btn-danger">明細修改記錄</button>
    --}}

    <br>

    <div class="card shadow mb-4 -detail -detail-primary">
        <div class="card-body px-4">
            <h2>收款單</h2>
            <dl class="row">
                <div class="col">
                    <dt>喜鴻國際企業股份有限公司</dt>
                    <dd></dd>
                </div>
            </dl>
            <dl class="row">
                <div class="col">
                    <dt>客戶：{{ $order_purchaser->name }}</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>地址：{{ $order_purchaser->address }}</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>電話：{{ $order_purchaser->phone }}</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>傳真：{{ $order_purchaser->fax }}</dt>
                    <dd></dd>
                </div>
            </dl>
            <dl class="row mb-0 border-top">
                <div class="col">
                    <dt>收款單號：{{ $received_order->sn }}</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>製表日期：{{ date('Y-m-d', strtotime($received_order->created_at)) }}</dt>
                    <dd></dd>
                </div>
            </dl>
            <dl class="row mb-0">
                <div class="col">
                    <dt>訂單流水號：<a href="{{ Route('cms.order.detail', ['id' => $order->id], true) }}">{{ $order->sn }}</a></dt>
                    <dd></dd>
                </div>
                @if($received_order->receipt_date)
                <div class="col">
                    <dt>入帳日期：{{ date('Y-m-d', strtotime($received_order->receipt_date)) }}</dt>
                    <dd></dd>
                </div>
                @endif
            </dl>
            <dl class="row mb-0">
                <div class="col">
                    <dt>收款對象：
                        {{--
                            <a href="{{ $supplierUrl }}" target="_blank"> {{ $supplier->name }}
                                <span class="icon"><i class="bi bi-box-arrow-up-right"></i></span>
                            </a>
                        --}}
                    </dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>承辦人：{{ $undertaker ? $undertaker->name : '' }}</dt>
                    <dd></dd>
                </div>
            </dl>
        </div>
        <div class="card-body px-4 py-2">
            <div class="table-responsive tableoverbox">
                <table class="table tablelist table-sm mb-0">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th scope="col">收款項目</th>
                            <th scope="col">數量</th>
                            <th scope="col">單價</th>
                            <th scope="col">應收金額</th>
                            <th scope="col">備註</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order_list_data as $value)
                            <tr>
                                <td>{{ $product_grade_name }} --- {{ $value->product_title }}{{'（' . $value->del_even . ' - ' . $value->del_category_name . '）'}}{{'（' . $value->product_price . ' * ' . $value->product_qty . '）'}}</td>
                                <td>{{ $value->product_qty }}</td>
                                <td>{{ number_format($value->product_price, 2) }}</td>
                                <td>{{ number_format($value->product_origin_price) }}</td>
                                <td>{{ $received_order->memo }} <a href="{{ Route('cms.order.detail', ['id' => $order->id], true) }}">{{ $order->sn }}</a> {{ $value->product_taxation == 1 ? '應稅' : '免稅' }} {{ $order->note }}</td>
                            </tr>
                        @endforeach

                        @if($order->dlv_fee > 0)
                            <tr>
                                <td>{{ $logistics_grade_name }}</td>
                                <td>1</td>
                                <td>{{ number_format($order->dlv_fee, 2) }}</td>
                                <td>{{ number_format($order->dlv_fee) }}</td>
                                <td>{{ $received_order->memo }} <a href="{{ Route('cms.order.detail', ['id' => $order->id], true) }}">{{ $order->sn }}</a> {{ $order->dlv_taxation == 1 ? '應稅' : '免稅' }}</td>
                            </tr>
                        @endif

                        @if($order->discount_value > 0)
                        @foreach($order_discount ?? [] as $d_value)
                            <tr>
                                <td>{{ $d_value->account_code }} - {{ $d_value->account_name }} - {{ $d_value->title }}</td>
                                <td>1</td>
                                <td>-{{ number_format($d_value->discount_value, 2) }}</td>
                                <td>-{{ number_format($d_value->discount_value) }}</td>
                                <td>{{ $received_order->memo }} <a href="{{ Route('cms.order.detail', ['id' => $order->id], true) }}">{{ $order->sn }}</a> {{ $d_value->discount_taxation == 1 ? '應稅' : '免稅' }}</td>
                            </tr>
                        @endforeach
                        @endif

                        <tr class="table-light">
                            <td>合計：</td>
                            <td></td>
                            <td></td>
                            <td>{{ number_format($received_order->price) }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-body px-4 pb-4">
            @foreach($received_data as $value)
            <dl class="row">
                <div class="col">
                    <dt></dt>
                    <dd>
                        {{ $value->account->code . ' - ' . $value->account->name }}
                        {{ number_format($value->credit_card_price ?? $value->tw_price) }}
                        @if($value->received_method == 'credit_card')
                            {{ '（' . $value->received_method_name . ' - ' . $value->credit_card_number . '（' . $value->credit_card_owner_name . '）' . '）' }}
                        @elseif($value->received_method == 'remit')
                            {{ '（' . $value->received_method_name . ' - ' . $value->summary . '（' . $value->remit_memo . '）' . '）' }}
                        @else
                            {{ '（' . $value->received_method_name . ' - ' . $value->account->name . ' - ' . $value->summary . '）' }}
                        @endif
                    </dd>
                </div>
            </dl>
            @endforeach
        </div>

        <div class="card-body px-4 pb-4">
            <dl class="row">
                <div class="col">
                    <dt>財務主管：</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>會計：{{ $accountant }}</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>商品主管：</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>商品負責人：{{ $product_qc }}</dt>
                    <dd></dd>
                </div>
            </dl>
        </div>
    </div>
@endsection

@once
    @push('sub-scripts')
    @endpush
@endonce