@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">收款單</h2>

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
            <a href="{{ route('cms.collection_received.edit', ['id' => $received_order->id]) }}" class="btn btn-sm btn-success px-3" role="button">修改</a>

            @if(! $received_order->receipt_date)
            <a href="{{ route('cms.account_received.ro-review', ['id' => $received_order->source_id]) }}" 
                class="btn btn-primary px-4" role="button">收款單入款審核</a>
            @else
                @if(! $data_status_check)
                <a href="{{ route('cms.account_received.ro-review', ['id' => $received_order->source_id]) }}" 
                    class="btn btn-outline-success px-4" role="button">取消入帳</a>
                @endif
            @endif
            <a href="{{ route('cms.account_received.ro-taxation', ['id' => $received_order->source_id]) }}" 
                class="btn btn-outline-success px-4" role="button">修改摘要/稅別</a>

            <a href="{{ url()->full() . '?action=print' }}" target="_blank" class="btn btn-danger" rel="noopener noreferrer">中一刀列印畫面</a>
            {{--
            <button type="submit" class="btn btn-danger">A4列印畫面</button>
            <button type="submit" class="btn btn-danger">修改記錄</button>
            <button type="submit" class="btn btn-danger">明細修改記錄</button>
            --}}
        </div>
    </nav>

    <div class="card shadow mb-4 -detail -detail-primary">
        <div class="card-body px-4">
            <h2>收款單</h2>
            <dl class="row">
                <div class="col">
                    <dt>喜鴻國際企業股份有限公司</dt>
                    <dd></dd>
                </div>
            </dl>
            <dl class="row mb-0">
                <div class="col">
                    <dd>客戶：{{ $received_order->drawee_name }}</dd>
                </div>
                <div class="col">
                    <dd>地址：{{ $received_order->drawee_address }}</dd>
                </div>
            </dl>
            <dl class="row mb-0">
                <div class="col">
                    <dd>電話：{{ $received_order->drawee_phone }}</dd>
                </div>
                <div class="col">
                    <dd>傳真：</dd>
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
                    <dt>訂單流水號：</dt>
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
                            <a href="{{ $supplierUrl }}" target="_blank">{{ $supplier->name }}</a>
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
                                <td>{{ $value->ro_received_grade_code }} {{ $value->ro_received_grade_name }}</td>
                                <td>1</td>
                                <td>{{ number_format($value->tw_price, 2) }}</td>
                                <td>{{ number_format($value->account_amt_net) }}</td>
                                <td>{{ $received_order->memo }} {{ $value->taxation == 1 ? '應稅' : '免稅' }} {{ $value->note }}</td>
                            </tr>
                        @endforeach

                        <tr class="table-light">
                            <td>合計：</td>
                            <td></td>
                            <td>（{{ $zh_price }}）</td>
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
                    <dt>商品負責人：{{-- $product_qc --}}</dt>
                    <dd></dd>
                </div>
            </dl>
        </div>
    </div>
    
    <div class="col-auto">
        <a href="{{ route('cms.account_received.index') }}" class="btn btn-primary" 
            role="button">返回上一頁</a>
    </div>
@endsection

@once
    @push('sub-scripts')
    @endpush
@endonce
