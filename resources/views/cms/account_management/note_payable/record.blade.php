@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-4">應付票據明細</h2>

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        @can('cms.note_payable.edit')
        <div class="p-1 pe-2">
            @if($cheque->cheque_status_code == 'cashed')
            <a href="javascript:void(0)" role="button" class="btn btn-outline-danger btn-sm"
            data-bs-toggle="modal" data-bs-target="#confirm-reverse"
            data-href="{{ Route('cms.note_payable.reverse', ['id' => $cheque->cheque_payable_id]) }}">取消兌現</a>
            @endif
        </div>
        @endcan
    </nav>

    <div class="card mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <tbody class="border-top-0">
                        <tr class="table-light text-center">
                            <td colspan="4">應付票據明細</td>
                        </tr>
                        <tr>
                            <th class="table-light" style="width:15%">付款單編號</th>
                            <td style="width:35%"><a href="{{ $cheque->link }}">{{ $cheque->po_sn }}</a></td>
                            <th class="table-light" style="width:15%">兌現清單</th>
                            <td style="width:35%"><a href="{{ $cheque->cheque_sn && $cheque->cheque_cashing_date ? route('cms.note_payable.detail', ['type' => $cheque->cheque_status_code, 'qd' => date('Y-m-d', strtotime($cheque->cheque_cashing_date))]) : 'javascript:void(0);' }}">{{ $cheque->cheque_sn }}</a></td>
                        </tr>

                        <tr>
                            <th class="table-light" style="width:15%">支票號碼</th>
                            <td style="width:35%">{{ $cheque->cheque_ticket_number }}</td>
                            <th class="table-light" style="width:15%">開票日期</th>
                            <td style="width:35%">{{ $cheque->payment_date ? date('Y-m-d', strtotime($cheque->payment_date)) : '' }}</td>
                        </tr>

                        <tr>
                            <th class="table-light" style="width:15%">到期日</th>
                            <td style="width:35%">{{ $cheque->cheque_due_date ? date('Y-m-d', strtotime($cheque->cheque_due_date)) : '' }}</td>
                            <th class="table-light" style="width:15%">兌現日</th>
                            <td style="width:35%">{{ $cheque->cheque_cashing_date ? date('Y-m-d', strtotime($cheque->cheque_cashing_date)) : '' }}</td>
                        </tr>

                        <tr>
                            <th class="table-light" style="width:15%">對象類別</th>
                            <td style="width:35%">{{ $cheque->po_target_name }}</td>
                            <th class="table-light" style="width:15%">會計科目</th>
                            <td style="width:35%">{{ $cheque->po_payable_grade_code . ' ' . $cheque->po_payable_grade_name }}</td>
                        </tr>

                        <tr>
                            <th class="table-light" style="width:15%">金額</th>
                            <td style="width:35%">{{ number_format($cheque->tw_price, 2) }}</td>
                            <th class="table-light" style="width:15%">狀態</th>
                            <td style="width:35%">{{ $cheque->cheque_status }}</td>
                        </tr>

                        <tr>
                            <th class="table-light" style="width:15%">備註</th>
                            <td colspan="3">{{ $cheque->note }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-auto">
        <a href="{{ url()->previous() }}" class="btn btn-outline-primary px-4" role="button">
            返回上一頁
        </a>
    </div>

    <!-- Modal -->
    <x-b-modal id="confirm-reverse">
        <x-slot name="title">取消確認</x-slot>
        <x-slot name="body">確認要取消此兌現狀態？</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認並取消</a>
        </x-slot>
    </x-b-modal>
@endsection

@once
    @push('sub-styles')
        <style>

        </style>
    @endpush

    @push('sub-scripts')
        <script>
            // Modal Control
            $('#confirm-reverse').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });

        </script>
    @endpush
@endonce