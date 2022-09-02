@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">付款單</h2>

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
            <a href="{{ route('cms.collection_payment.edit', ['id' => $paying_order->id]) }}" class="btn btn-sm btn-success px-3" role="button">修改</a>

            <a href="{{ url()->full() . '?action=print' }}" target="_blank" 
                class="btn btn-sm btn-warning" rel="noopener noreferrer">中一刀列印畫面</a>

            @if(! $data_status_check)
            <a href="javascript:void(0)" role="button" class="btn btn-outline-danger btn-sm"
                data-bs-toggle="modal" data-bs-target="#confirm-delete"
                data-href="{{ Route('cms.collection_payment.delete', ['id' => $paying_order->id]) }}">刪除付款單</a>
            @endif
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
                <h4 class="text-center">付款單</h4>
                <hr>

                <dl class="row mb-0">
                    <div class="col">
                        <dd>客戶：{{ $paying_order->payee_name }}</dd>
                    </div>
                    <div class="col">
                        <dd>編號：{{ $paying_order->sn }}</dd>
                    </div>
                </dl>

                <dl class="row mb-0">
                    <div class="col">
                        <dd>電話：{{ $paying_order->payee_phone }}</dd>
                    </div>
                    <div class="col">
                        <dd>日期：{{ date('Y-m-d', strtotime($paying_order->created_at)) }}</dd>
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
                            @foreach($target_items as $value)
                            <tr>
                                <td>{{ $value->po_payable_grade_code . ' ' . $value->po_payable_grade_name . ' ' . $value->summary }}</td>
                                <td class="text-end">1</td>
                                <td class="text-end">{{ number_format($value->tw_price, 2) }}</td>
                                <td class="text-end">{{ number_format($value->account_amt_net) }}</td>
                                <td>{{ $value->taxation == 1 ? '應稅' : '免稅' }} {{ $value->note }}</td>
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
                    <div class="col">
                        <dd>承辦人：{{ $undertaker ? $undertaker->name : '' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
        
        <div class="col-auto">
            <a href="{{ route('cms.accounts_payable.index') }}" class="btn btn-outline-primary px-4" role="button">
                返回上一頁
            </a>
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
