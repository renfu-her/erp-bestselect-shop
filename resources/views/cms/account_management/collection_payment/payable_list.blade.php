@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-4">{{ $paying_order->sn }} 付款記錄</h2>

    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox">
            <table class="table tableList border-bottom">
                <thead class="small align-middle">
                    <tr>
                        <th scope="col" style="width:40px">#</th>
                        <th scope="col" class="text-center">刪除</th>
                        <th scope="col">會計科目</th>
                        <th scope="col">付款方式</th>
                        <th scope="col">摘要</th>
                        <th scope="col">付款日期</th>
                        <th scope="col" class="text-end">付款金額</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payable_data as $key => $data)
                        <tr>
                            <td>{{ $key + 1 }}</td>

                            <td class="text-center">
                                @can('cms.collection_payment.delete')
                                @if(! $data_status_check)
                                <a href="javascript:void(0)" data-href="{{ Route('cms.collection_payment.payable_delete', ['payable_id' => $data->payable_id], true) }}"
                                    data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                    class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                    <i class="bi bi-trash"></i>
                                </a>
                                @endif
                                @endcan
                            </td>
                            <td>{{ $data->account->code . ' ' . $data->account->name }}</td>
                            <td>{{ $data->payable_method_name }}</td>
                            <td>
                                @if($data->acc_income_type_fk == 2)
                                {{ $data->cheque_ticket_number . '（' . date('Y-m-d', strtotime($data->cheque_due_date)) . '） ' }}
                                @endif
                                {{ $data->summary }}
                            </td>

                            <td>{{ $data->payment_date ? date('Y/m/d', strtotime($data->payment_date)) : '-' }}</td>
                            <td class="text-end">{{ number_format($data->tw_price) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-auto">
        <a href="{{ $previous_url }}" class="btn btn-outline-primary px-4" role="button">
            返回上一頁
        </a>
    </div>

    <!-- Modal -->
    <x-b-modal id="confirm-delete">
        <x-slot name="title">刪除確認</x-slot>
        <x-slot name="body">刪除後將無法復原！確認要刪除？</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
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
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
        </script>
    @endpush
@endonce
