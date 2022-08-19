@extends('layouts.main')

@section('sub-content')
    @if(! $paying_order->balance_date)
        <a href="{{ Route('cms.order.logistic-po-create', ['id' => $sub_order->order_id, 'sid' => $sub_order->id]) }}" class="btn btn-primary" role="button">付款</a>
    @endif
    <button type="submit" class="btn btn-danger" onclick="location.href='{{ url()->full() . '?method=print' }}'">中一刀列印畫面</button>
    <button type="submit" class="btn btn-danger">A4列印畫面</button>
    <button type="submit" class="btn btn-danger">圖片管理</button>
    <br>
    <form id="" method="POST" action="">
        @csrf
        <div class="card shadow mb-4 -detail -detail-primary">
            <div class="card-body px-4">
                <h2>物流付款單</h2>

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
                        <dt>付款單號：{{ $paying_order->sn }}</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>製表日期：{{ date('Y-m-d', strtotime($paying_order->created_at)) }}</dt>
                        <dd></dd>
                    </div>
                </dl>

                <dl class="row mb-0">
                    <div class="col">
                        <dt>單據編號：<a href="{{ Route('cms.order.detail', ['id' => $sub_order->order_id, 'subOrderId' => $sub_order->id]) }}">{{ $sub_order->sn }}</a></dt>
                        <dd></dd>
                    </div>
                    @if($paying_order->balance_date)
                    <div class="col">
                        <dt>付款日期：{{ date('Y-m-d', strtotime($paying_order->balance_date)) }}</dt>
                        <dd></dd>
                    </div>
                    @endif
                </dl>

                <dl class="row mb-0">
                    <div class="col">
                        <dt>支付對象：
                            {{ $paying_order->payee_name }}
                            {{--
                            @if($paying_order->payee_id)
                            <a href="{{ Route('cms.supplier.edit', ['id' => $paying_order->payee_id,]) }}" target="_blank">
                                {{ $paying_order->payee_name }}
                                <span class="icon"><i class="bi bi-box-arrow-up-right"></i></span>
                            </a>
                            @endif
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
                            <th scope="col">付款項目</th>
                            <th scope="col">數量</th>
                            <th scope="col">單價</th>
                            <th scope="col">應付金額</th>
                            <th scope="col">備註</th>
                        </tr>
                        </thead>

                        <tbody>
                            @if($sub_order->logistic_cost > 0)
                                <tr>
                                    <td>{{ $logistics_grade_name . ' - 物流費用' }}</td>
                                    <td>1</td>
                                    <td>{{ number_format($sub_order->logistic_cost, 2) }}</td>
                                    <td>{{ number_format($sub_order->logistic_cost) }}</td>
                                    <td>{{ $sub_order->logistic_memo }}</td>
                                </tr>
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
                </dl>
            </div>
        </div>

        <div id="submitDiv">
            <div class="col-auto">
                <input type="hidden" name="del_item_id">
                <a href="{{ Route('cms.collection_payment.index') }}" class="btn btn-primary px-4" role="button">返回「付款作業」列表</a>
                <a href="{{ Route('cms.order.detail', ['id' => $sub_order->order_id, 'subOrderId' => $sub_order->id]) }}" class="btn btn-outline-primary px-4" role="button">返回訂單資訊</a>
            </div>
        </div>
    </form>
@endsection

@once
    @push('sub-scripts')
    @endpush
@endonce
