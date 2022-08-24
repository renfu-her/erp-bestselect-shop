@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">退貨付款單</h2>

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
            @if(! $paying_order->balance_date)
                <a href="{{ Route('cms.order.return-pay-create', ['id' => $paying_order->source_id, 'sid' => $paying_order->source_sub_id]) }}" 
                    class="btn btn-primary" role="button">付款</a>
            @endif

            <a href="{{ url()->full() . '?action=print' }}" target="_blank" class="btn btn-danger" rel="noopener noreferrer">中一刀列印畫面</a>
            <button type="submit" class="btn btn-danger">A4列印畫面</button>
            <button type="submit" class="btn btn-danger">圖片管理</button>
        </div>
    </nav>

    <form id="" method="POST" action="">
        @csrf
        <div class="card shadow mb-4 -detail -detail-primary">
            <div class="card-body px-4">
                <h2>退貨付款單</h2>

                <dl class="row">
                    <div class="col">
                        <dt>{{ $applied_company->company }}</dt>
                        <dd></dd>
                    </div>
                </dl>

                <dl class="row">
                    <div class="col">
                        <dt>地址：{{ $applied_company->address }}</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>電話：{{ $applied_company->phone }}</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>傳真：{{ $applied_company->fax }}</dt>
                        <dd></dd>
                    </div>
                </dl>

                <dl class="row mb-0 border-top">
                    <div class="col">
                        <dt>客戶：{{ $paying_order->payee_name }}</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>編號：{{ $paying_order->sn }}</dt>
                        <dd></dd>
                    </div>
                </dl>

                <dl class="row mb-0">
                    <div class="col">
                        <dt>電話：{{ $paying_order->payee_phone }}</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>日期：{{ date('Y-m-d', strtotime($paying_order->created_at)) }}</dt>
                        <dd></dd>
                    </div>
                </dl>
            </div>

            <div class="card-body px-4 py-2">
                <div class="table-responsive tableoverbox">
                    <table class="table tablelist table-sm mb-0">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th scope="col">費用說明</th>
                                <th scope="col">數量</th>
                                <th scope="col">單價</th>
                                <th scope="col">金額</th>
                                <th scope="col">備註</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($sub_order as $so_value)
                            @foreach($so_value->items as $p_value)
                                <tr>
                                    <td>{{ $product_grade_name }} - {{ $p_value->product_title }}{{'（' . $so_value->ship_event . ' - ' . $so_value->ship_category_name . '）'}}{{'（' . $p_value->price . ' * ' . $p_value->qty . '）'}}</td>
                                    <td>{{ $p_value->qty }}</td>
                                    <td>{{ number_format($p_value->price, 2) }}</td>
                                    <td>{{ number_format($p_value->total_price) }}</td>
                                    <td>{{ $paying_order->memo }} <a href="{{ Route('cms.order.detail', ['id' => $order->id]) }}">{{ $order->sn }}</a> {{ $p_value->product_taxation == 1 ? '應稅' : '免稅' }} {{ $order->note }}</td>
                                </tr>
                            @endforeach
                            @endforeach

                            @if($order->dlv_fee > 0)
                                <tr>
                                    <td>{{ $logistics_grade_name }} - 物流費用</td>
                                    <td>1</td>
                                    <td>{{ number_format($order->dlv_fee, 2) }}</td>
                                    <td>{{ number_format($order->dlv_fee) }}</td>
                                    <td>{{ $paying_order->memo }} <a href="{{ Route('cms.order.detail', ['id' => $order->id]) }}">{{ $order->sn }}</a> {{ $order->dlv_taxation == 1 ? '應稅' : '免稅' }}</td>
                                </tr>
                            @endif

                            @if($order->discount_value > 0)
                            @foreach($order_discount ?? [] as $d_value)
                                <tr>
                                    <td>{{ $d_value->account_code }} {{ $d_value->account_name }} - {{ $d_value->title }}</td>
                                    <td>1</td>
                                    <td>-{{ number_format($d_value->discount_value, 2) }}</td>
                                    <td>-{{ number_format($d_value->discount_value) }}</td>
                                    <td>{{ $paying_order->memo }} <a href="{{ Route('cms.order.detail', ['id' => $order->id]) }}">{{ $order->sn }}</a> {{ $d_value->discount_taxation == 1 ? '應稅' : '免稅' }}</td>
                                </tr>
                            @endforeach
                            @endif

                            <tr class="table-light">
                                <td>合計：</td>
                                <td></td>
                                <td>（{{ $zh_price }}）</td>
                                <td>{{ number_format($paying_order->price) }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                @foreach($payable_data as $value)
                <dl class="row">
                    <div class="col">
                        <dt></dt>
                        <dd>
                            {{ $value->account->code . ' ' . $value->account->name }}
                            {{ number_format($value->tw_price) }}
                            @if($value->acc_income_type_fk == 3)
                                {{ '（' . $value->payable_method_name . ' - ' . $value->summary . '）' }}
                            @elseif($value->acc_income_type_fk == 2)
                                {!! '（<a href="' . route('cms.note_payable.record', ['id'=>$value->payable_method_id]) . '">' . $value->payable_method_name . ' ' . $value->cheque_ticket_number . '（' . date('Y-m-d', strtotime($value->cheque_due_date)) . '）' . '</a>）' !!}
                            @else
                                {{ '（' . $value->payable_method_name . ' - ' . $value->account->name . ' - ' . $value->summary . '）' }}
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
                        <dt>商品負責人：</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>承辦人：{{ $undertaker ? $undertaker->name : '' }}</dt>
                        <dd></dd>
                    </div>
                </dl>
            </div>
        </div>
    </form>
@endsection

@once
    @push('sub-scripts')

    @endpush
@endonce
