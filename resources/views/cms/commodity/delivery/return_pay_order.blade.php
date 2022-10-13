@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">退貨付款單</h2>

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
            @can('cms.collection_payment.edit')
            <a href="{{ route('cms.collection_payment.edit', ['id' => $delivery->po_id]) }}" 
                class="btn btn-sm btn-success px-3" role="button">修改</a>
            @endcan

            @if(! $delivery->po_balance_date)
                <a href="{{ Route('cms.delivery.return-pay-create', ['id' => $delivery->delivery_id]) }}" 
                    class="btn btn-sm btn-primary px-3" role="button">付款</a>
            @endif

            @can('cms.collection_payment.delete')
            @if(! $data_status_check)
            @if(! ($paying_order->payment_date && $paying_order->append_po_id))
                <a href="{{ route('cms.collection_payment.payable_list', ['id' => $delivery->po_id]) }}" class="btn btn-sm btn-primary" role="button">付款記錄</a>

                <a href="javascript:void(0)" role="button" class="btn btn-outline-danger btn-sm"
                    data-bs-toggle="modal" data-bs-target="#confirm-delete"
                    data-href="{{ Route('cms.collection_payment.delete', ['id' => $delivery->po_id]) }}">刪除付款單</a>
            @endif
            @endif
            @endcan

            <a href="{{ url()->full() . '?action=print' }}" target="_blank" 
                class="btn btn-sm btn-warning" rel="noopener noreferrer">中一刀列印畫面</a>

            <a href="{{ route('cms.collection_payment.edit_note', ['id' => $paying_order->id]) }}"
                class="btn btn-sm btn-dark" role="button">編輯付款項目備註</a>
        </div>
    </nav>

    <div class="card shadow p-4 mb-4">
        <div class="mb-3">
            <h4 class="text-center">{{ $applied_company->company }}</h4>
            <div class="text-center small mb-2">
                <span>地址：{{ $applied_company->address }}</span>
                <span class="ms-3">電話：{{ $applied_company->phone }}</span>
                <span class="ms-3">傳真：{{ $applied_company->fax }}</span>
            </div>
            <h4 class="text-center">退貨付款單</h4>
            <hr>

            <dl class="row mb-0">
                <div class="col">
                    <dd>付款單號：{{ $delivery->po_sn }}{!! $paying_order->append_po_id ? ' / ' . '<a href="' . $paying_order->append_po_link . '">' . $paying_order->append_po_sn . '</a>' : '' !!}</dd>
                </div>
                <div class="col">
                    <dd>製表日期：{{ date('Y-m-d', strtotime($delivery->po_created_at)) }}</dd>
                </div>
            </dl>

            <dl class="row mb-0">
                <div class="col">
                    <dd>單據編號：</dd>
                </div>
                <div class="col">
                    <dd>付款日期：{{ $paying_order->payment_date ? date('Y-m-d', strtotime($paying_order->payment_date)) : '' }}</dd>
                </div>
            </dl>

            <dl class="row mb-0">
                <div class="col">
                    <dd>支付對象：
                        {{ $delivery->po_payee_name }}
                    </dd>
                </div>
                <div class="col">
                    <dd>承辦人：{{ $undertaker ? $undertaker->name : '' }}</dd>
                </div>
            </dl>

            <dl class="row mb-0">
                <div class="col">
                    <dd>電話：{{ $delivery->po_payee_phone }}</dd>
                </div>
            </dl>
        </div>

        <div class="mb-2">
            <div class="table-responsive tableoverbox">
                <table class="table tablelist table-sm mb-0 align-middle">
                    <thead class="table-light text-secondary text-nowrap">
                        <tr>
                            <th scope="col">費用說明</th>
                            <th scope="col" class="text-end">數量</th>
                            <th scope="col" class="text-end">單價</th>
                            <th scope="col" class="text-end">金額</th>
                            <th scope="col">備註</th>
                        </tr>
                    </thead>

                    <tbody>
                        @if($delivery->delivery_back_items)
                        @foreach($delivery->delivery_back_items as $db_value)
                            <tr>
                                <td>{{ $db_value->grade_code . ' ' . $db_value->grade_name }} - {{ $db_value->product_title }}{{'（' . $delivery->sub_order_ship_event . ' - ' . $delivery->sub_order_ship_category_name . '）'}}{{'（' . $db_value->price . ' * ' . $db_value->qty . '）'}}</td>
                                <td class="text-end">{{ $db_value->qty }}</td>
                                <td class="text-end">{{ number_format($db_value->price, 2) }}</td>
                                <td class="text-end">{{ number_format($db_value->total_price) }}</td>
                                <td>{{ $delivery->po_memo }} <a href="{{ route('cms.delivery.back_detail', ['event' => $delivery->delivery_event, 'eventId' => $delivery->delivery_event_id]) }}">{{ $delivery->delivery_event_sn }}</a> {{ $db_value->taxation == 1 ? '應稅' : '免稅' }} {!! nl2br($db_value->note) !!} {!! nl2br($db_value->po_note) !!}</td>
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
                            <td class="text-end">{{ number_format($delivery->po_price) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
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
                    <dd>商品負責人：</dd>
                </div>
            </dl>
        </div>
    </div>
    
    <div class="col-auto">
        {{--
        <a href="{{ Route('cms.delivery.back_detail', ['event' => $delivery->delivery_event, 'eventId' => $delivery->delivery_event_id]) }}" 
            class="btn btn-outline-primary px-4" role="button">返回 銷貨退回明細</a>
        --}}
        @can('cms.collection_payment.index')
        <a href="{{ session('collection_payment_url') ?? route('cms.collection_payment.index') }}" class="btn btn-outline-primary px-4" role="button">
            返回 付款作業
        </a>
        @endcan
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
