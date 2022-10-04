@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">轉帳傳票</h2>

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
            @can('cms.transfer_voucher.edit')
            <a href="{{ route('cms.transfer_voucher.edit', ['id' => $voucher->tv_id]) }}" 
                class="btn btn-sm btn-success px-3" role="button">修改</a>
            @endcan

            @can('cms.transfer_voucher.delete')
            <a href="javascript:void(0)" role="button" class="btn btn-sm btn-outline-danger" 
                data-bs-toggle="modal" data-bs-target="#confirm-delete" 
                data-href="{{ Route('cms.transfer_voucher.delete', ['id' => $voucher->tv_id]) }}">刪除傳票</a>
            @endcan
        </div>
    </nav>

    <div class="card shadow p-4 mb-4">
        <div class="mb-3">
            <h4 class="text-center">{{ $voucher->company_name }}</h4>
            <h4 class="text-center">轉帳傳票</h4>
            <h4 class="text-center">中華民國 {{ date('Y', strtotime($voucher->tv_voucher_date)) - 1911 }} 年 {{ date('m', strtotime($voucher->tv_voucher_date)) }} 月 {{ date('d', strtotime($voucher->tv_voucher_date)) }} 日</h4>
            
            <hr>
            <dl class="row mb-0">
                <div class="col">
                    <dd>傳票編號：{{ $day_emd_item ? $day_emd_item->sn : '' }}</dd>
                </div>
                <div class="col">
                    <dd>單號：{{ $voucher->tv_sn }}</dd>
                </div>
            </dl>
        </div>

        <div class="mb-3">
            <div class="table-responsive tableoverbox">
                <table class="table tablelist table-sm mb-0 align-middle">
                    <thead class="table-light text-secondary text-nowrap">
                        <tr>
                            <th scope="col">會計科目</th>
                            <th scope="col">摘要</th>
                            <th scope="col">幣別</th>
                            <th scope="col" class="text-end">匯率</th>
                            <th scope="col" class="text-end">借方</th>
                            <th scope="col" class="text-end">貸方</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($voucher->tv_items)
                        @foreach(json_decode($voucher->tv_items) as $value)
                        <tr>
                            <td>{{ $value->grade_code . ' ' . $value->grade_name }}</td>
                            <td>{{ $value->summary }}</td>
                            <td>{{ $value->currency_name }}</td>
                            <td class="text-end">{{ $value->rate }}</td>
                            <td class="text-end">{{ $value->debit_credit_code == 'debit' ? number_format($value->final_price, 2) : '' }}</td>
                            <td class="text-end">{{ $value->debit_credit_code == 'credit' ? number_format($value->final_price, 2) : '' }}</td>
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="2">
                                <div class="d-flex justify-content-between">
                                    <span>合計：</span>
                                    <span class="text-danger">{{ $voucher->tv_debit_price != $voucher->tv_credit_price ? '（借貸不平）' : '' }}</span>
                                </div>
                            </td>
                            <td></td>
                            <td></td>
                            <td class="text-end">{{ number_format($voucher->tv_debit_price) }}</td>
                            <td class="text-end">{{ number_format($voucher->tv_credit_price) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div>
            <dl class="row">
                <div class="col">
                    <dd>主管：</dd>
                </div>
                <div class="col">
                    <dd>會計：{{ $voucher->accountant_name }}</dd>
                </div>
                <div class="col">
                    <dd>承辦人：{{ $voucher->creator_name }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="col-auto">
        <a href="{{ Route('cms.transfer_voucher.index') }}" 
            class="btn btn-outline-primary px-4" role="button">
            返回列表
        </a>
    </div>

    <!-- Modal -->
    <x-b-modal id="confirm-delete">
        <x-slot name="title">刪除確認</x-slot>
        <x-slot name="body">確認要刪除此傳票？</x-slot>
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