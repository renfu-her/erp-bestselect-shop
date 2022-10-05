@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">運費付款單</h2>

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
            @can('cms.collection_payment.edit')
            <a href="{{ route('cms.collection_payment.edit', ['id' => $paying_order->id]) }}" class="btn btn-sm btn-success px-3" role="button">修改</a>
            @endcan

            @if(! $paying_order->balance_date)
                <a href="{{ Route('cms.consignment.logistic-po-create', ['id' => $consignmentData->consignment_id]) }}" 
                    class="btn btn-sm btn-primary px-3" role="button">付款</a>
            @endif
            {{-- <button type="button" class="btn btn-sm btn-primary">圖片管理</button> --}}

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

            <a href="{{ route('cms.collection_payment.edit_note', ['id' => $paying_order->id]) }}"
                class="btn btn-dark btn-sm" role="button">編輯付款項目備註</a>
        </div>
    </nav>

    <form id="" method="POST" action="">
        @csrf
        <div class="card shadow p-4 mb-4">
            <div class="mb-3">
                <h4 class="text-center">{{ $applied_company->company }}</h4>
                <div class="text-center small mb-2">
                    <span>地址：{{ $applied_company->address }}</span>
                    <span class="ms-3">電話：{{ $applied_company->phone }}</span>
                    <span class="ms-3">傳真：{{ $applied_company->fax }}</span>
                </div>
                <h4 class="text-center">運費付款單</h4>
                <hr>

                <dl class="row mb-0">
                    <div class="col">
                        <dd>付款單號：{{ $paying_order->sn }}{!! $paying_order->append_po_id ? ' / ' . '<a href="' . $paying_order->append_po_link . '">' . $paying_order->append_po_sn . '</a>' : '' !!}</dd>
                    </div>
                    <div class="col">
                        <dd>製表日期：{{ date('Y-m-d', strtotime($paying_order->created_at)) }}</dd>
                    </div>
                </dl>

                <dl class="row mb-0">
                    <div class="col">
                        <dd>單據編號：<a href="{{ Route('cms.consignment.edit', ['id' => $consignmentData->consignment_id]) }}">{{ $consignmentData->consignment_sn }}</a>
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
            </div>

            <div class="mb-2">
                <div class="table-responsive tableoverbox">
                    <table class="table tablelist table-sm mb-0 align-middle">
                        <thead class="table-light text-secondary text-nowrap">
                            <tr>
                                <th scope="col">付款項目</th>
                                <th scope="col" class="text-end">數量</th>
                                <th scope="col" class="text-end">單價</th>
                                <th scope="col" class="text-end">應付金額</th>
                                <th scope="col" colspan="2">備註</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($consignmentData->lgt_cost > 0)
                                <tr>
                                    <td>{{ $logistics_grade_name . ' - 物流費用' }}</td>
                                    <td class="text-end">1</td>
                                    <td class="text-end">{{ number_format($consignmentData->lgt_cost, 2) }}</td>
                                    <td class="text-end">{{ number_format($consignmentData->lgt_cost) }}</td>
                                    <td>{!! nl2br(e($consignmentData->lgt_memo)) !!}</td>
                                    <td>{!! nl2br(e($consignmentData->lgt_po_note)) !!}</td>
                                </tr>
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
            <a href="{{ Route('cms.collection_payment.index') }}" class="btn btn-outline-primary px-4" 
                role="button">返回 付款作業列表</a>
            <a href="{{ Route('cms.consignment.edit', ['id' => $consignmentData->consignment_id]) }}" 
                class="btn btn-outline-primary px-4" role="button">返回 寄倉單資訊</a>
        </div>
    </form>

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
