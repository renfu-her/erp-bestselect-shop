@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">退貨付款單</h2>

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
            <a href="{{ route('cms.collection_payment.edit', ['id' => $delivery->po_id]) }}" 
                class="btn btn-sm btn-success px-3" role="button">修改</a>

            @if(! $delivery->po_balance_date)
                <a href="{{ Route('cms.delivery.return-pay-create', ['id' => $delivery->delivery_id]) }}" 
                    class="btn btn-sm btn-primary px-3" role="button">付款</a>
            @endif

            <a href="{{ url()->full() . '?action=print' }}" target="_blank" 
                class="btn btn-sm btn-warning" rel="noopener noreferrer">中一刀列印畫面</a>

            @if(! $data_status_check)
            <a href="javascript:void(0)" role="button" class="btn btn-outline-danger btn-sm"
                data-bs-toggle="modal" data-bs-target="#confirm-delete"
                data-href="{{ Route('cms.collection_payment.delete', ['id' => $delivery->po_id]) }}">刪除付款單</a>
            @endif
        </div>
    </nav>

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
                    <dt>客戶：{{ $delivery->po_payee_name }}</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>編號：{{ $delivery->po_sn }}</dt>
                    <dd></dd>
                </div>
            </dl>

            <dl class="row mb-0">
                <div class="col">
                    <dt>電話：{{ $delivery->po_payee_phone }}</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>日期：{{ date('Y-m-d', strtotime($delivery->po_created_at)) }}</dt>
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
                        @if($delivery->delivery_back_items)
                        @foreach($delivery->delivery_back_items as $db_value)
                            <tr>
                                <td>{{ $product_grade_name }} - {{ $db_value->product_title }}{{'（' . $delivery->sub_order_ship_event . ' - ' . $delivery->sub_order_ship_category_name . '）'}}{{'（' . $db_value->price . ' * ' . $db_value->qty . '）'}}</td>
                                <td>{{ $db_value->qty }}</td>
                                <td>{{ number_format($db_value->price, 2) }}</td>
                                <td>{{ number_format($db_value->total_price) }}</td>
                                <td>{{ $delivery->po_memo }} <a href="{{ route('cms.delivery.back_detail', ['event' => $delivery->delivery_event, 'eventId' => $delivery->delivery_event_id]) }}">{{ $delivery->delivery_event_sn }}</a> {{ $db_value->taxation == 1 ? '應稅' : '免稅' }} {{ $delivery->order_note }}</td>
                            </tr>
                        @endforeach
                        @endif

                        {{--
                        @if($order->dlv_fee > 0)
                            <tr>
                                <td>{{ $logistics_grade_name }} - 物流費用</td>
                                <td>1</td>
                                <td>{{ number_format($order->dlv_fee, 2) }}</td>
                                <td>{{ number_format($order->dlv_fee) }}</td>
                                <td>{{ $delivery->po_memo }} <a href="{{ Route('cms.order.detail', ['id' => $order->id]) }}">{{ $order->sn }}</a> {{ $order->dlv_taxation == 1 ? '應稅' : '免稅' }}</td>
                            </tr>
                        @endif

                        @if($order->discount_value > 0)
                        @foreach($order_discount ?? [] as $delivery)
                            <tr>
                                <td>{{ $delivery->account_code }} {{ $delivery->account_name }} - {{ $delivery->title }}</td>
                                <td>1</td>
                                <td>-{{ number_format($delivery->discount_value, 2) }}</td>
                                <td>-{{ number_format($delivery->discount_value) }}</td>
                                <td>{{ $delivery->po_memo }} <a href="{{ Route('cms.order.detail', ['id' => $order->id]) }}">{{ $order->sn }}</a> {{ $delivery->discount_taxation == 1 ? '應稅' : '免稅' }}</td>
                            </tr>
                        @endforeach
                        @endif
                        --}}

                        <tr class="table-light">
                            <td>合計：</td>
                            <td></td>
                            <td>（{{ $zh_price }}）</td>
                            <td>{{ number_format($delivery->po_price) }}</td>
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

    <!-- Modal -->
    <x-b-modal id="confirm-delete">
        <x-slot name="title">刪除確認</x-slot>
        <x-slot name="body">確認要刪除此付款單？</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
        </x-slot>
    </x-b-modal>
@endsection

@once
    @push('sub-scripts')
        <script>
            // Modal Control
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
        </script>
    @endpush
@endonce
