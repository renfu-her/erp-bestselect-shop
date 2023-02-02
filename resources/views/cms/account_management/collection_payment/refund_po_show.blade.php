@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">退出付款單</h2>

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
            @can('cms.collection_payment.edit')
            <a href="{{ route('cms.collection_payment.edit', ['id' => $paying_order->id]) }}" class="btn btn-sm btn-success px-3" role="button">修改</a>
            @endcan

            @if(! $paying_order->payment_date)
                <a href="{{ Route('cms.collection_payment.refund-po-edit', ['id' => $paying_order->id]) }}" 
                    class="btn btn-sm btn-primary px-3" role="button">付款</a>
            @endif

            @can('cms.collection_payment.delete')
            @if(! $data_status_check)
            @if(! ($paying_order->payment_date && $paying_order->append_po_id))
                <a href="{{ route('cms.collection_payment.payable_list', ['id' => $paying_order->id]) }}" class="btn btn-sm btn-primary" role="button">付款記錄</a>

                <a href="javascript:void(0)" role="button" class="btn btn-outline-danger btn-sm"
                    data-bs-toggle="modal" data-bs-target="#confirm-delete"
                    data-href="{{ Route('cms.collection_payment.delete', ['id' => $paying_order->id]) }}">刪除付款單</a>
            @endif
            @endif
            @endcan

            <a href="{{ url()->full() . '?action=print' }}" target="_blank" 
                class="btn btn-sm btn-warning" rel="noopener noreferrer">中一刀列印畫面</a>

            @can('cms.collection_payment.edit')
            <a href="{{ route('cms.collection_payment.edit_note', ['id' => $paying_order->id]) }}"
                class="btn btn-dark btn-sm" role="button">編輯付款項目備註</a> 

            <a href="{{ route('cms.ref_expenditure_petition.edit', ['current_sn' => $paying_order->sn]) }}" class="btn btn-sm btn-primary" role="button">相關單號</a>
            @endcan
            @if (count($relation_order) > 0)
                @foreach ($relation_order as $value)
                    <a href="{{ $value->url }}" class="btn btn-sm btn-primary" role="button">{{ $value->sn }}</a>
                @endforeach
            @endif
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
            <h4 class="text-center">退出付款單</h4>
            <hr>

            <dl class="row mb-0">
                <div class="col">
                    <dd>付款單號：{{ $paying_order->sn }}</dd>
                </div>
                <div class="col">
                    <dd>製表日期：{{ date('Y-m-d', strtotime($paying_order->created_at)) }}</dd>
                </div>
            </dl>

            <dl class="row mb-0">
                <div class="col">
                    <dd>單據編號：
                        @php
                            $i = 1;
                            $count = count($parent_source);
                        @endphp
                        @foreach($parent_source as $p_value)
                        <a href="{{ $p_value['url'] }}">{{ $p_value['sn'] }}</a>{{ $count != $i ? ' / ' : '' }}
                        @php
                            $i++;
                        @endphp
                        @endforeach
                    </dd>
                </div>
                <div class="col">
                    <dd>付款日期：{{ $paying_order->payment_date ? date('Y-m-d', strtotime($paying_order->payment_date)) : '' }}</dd>
                </div>
            </dl>

            <dl class="row mb-0">
                <div class="col">
                    <dd>支付對象：{{ $paying_order->payee_name }}</dd>
                </div>
                <div class="col">
                    <dd>承辦人：{{ $undertaker ? $undertaker->name : '' }}</dd>
                </div>
            </dl>

            <dl class="row mb-0">
                <div class="col">
                    <dd>電話：{{ $paying_order->payee_phone }}</dd>
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
                            <th scope="col" colspan="2">備註</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($target_items as $t_value)
                            <tr>
                                <td>{{ $t_value->refund_grade_code }} {{ $t_value->refund_grade_name }} - {{ $t_value->refund_title . ' ' . $t_value->refund_summary}}</td>
                                <td class="text-end">{{ number_format($t_value->refund_qty) }}</td>
                                <td class="text-end">${{ number_format($t_value->refund_price, 2) }}</td>
                                <td class="text-end">${{ number_format($t_value->refund_total_price) }}</td>
                                <td>{!! nl2br($t_value->refund_note) !!}</td>
                                <td>{!! nl2br($t_value->refund_po_note) !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3">
                                <div class="d-flex justify-content-between">
                                    <span>合計：</span>
                                    <span>（{{ $zh_price }}）</span>
                                </div>
                            </td>
                            <td class="text-end">{{ number_format($paying_order->price) }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="mb-3">
            @foreach($payable_data as $value)
            <dl class="row mb-0">
                <div class="col-12">
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
        @can('cms.collection_payment.index')
        <a href="javascript:void(0);" class="btn btn-outline-primary px-4 keep_po_url" role="button">
            返回 付款作業
        </a>

        <a href="javascript:void(0);" class="btn btn-outline-primary px-4 keep_poc_url" role="button">
            返回 合併付款作業
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

            const keep_po_url = localStorage.getItem('collection_payment_url') ?? "{{ route('cms.collection_payment.index') }}";
            const keep_poc_url = localStorage.getItem('collection_payment_claim_url') ?? "{{ route('cms.collection_payment.claim') }}";
            $('.keep_po_url').attr('href', keep_po_url);
            $('.keep_poc_url').attr('href', keep_poc_url);
        </script>
    @endpush
@endonce
