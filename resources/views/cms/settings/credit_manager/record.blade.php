@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-4">信用卡刷卡記錄</h2>

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
        @if($record->credit_card_status_code != 0)
            <form method="POST" action="{{ $form_action }}" style="display: inline-block;">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">取消{{ $record->credit_card_status_code == 1 ? '請款' : '入款' }}</button>
            </form>
        @else

            <a href="{{ route('cms.credit_manager.record-edit', ['id'=>$record->credit_card_received_id]) }}" class="btn btn-primary btn-sm px-3" role="button">編輯</a>
        @endif
        </div>
    </nav>

    <div class="card mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <tbody class="border-top-0">
                        <tr class="table-light text-center">
                            <td colspan="4">信用卡刷卡記錄</td>
                        </tr>
                        <tr>
                            <th class="table-light" style="width:15%">卡號</th>
                            <td style="width:35%">{{ $record->credit_card_number }}</td>
                            <th class="table-light" style="width:15%">卡別</th>
                            <td style="width:35%">{{ $record->credit_card_type }}</td>
                        </tr>
                        <tr>
                            <th class="table-light" style="width:15%">刷卡人</th>
                            <td style="width:35%">{{ $record->credit_card_owner_name }}</td>
                            <th class="table-light" style="width:15%">入款單號</th>
                            <td style="width:35%"><a href="{{ $record->io_id ? route('cms.credit_manager.income-detail', ['id' => $record->io_id]) : 'javascript:void(0);'}}">{{ $record->io_sn }}</a></td>
                        </tr>

                        <tr>
                            <th class="table-light" style="width:15%">狀態</th>
                            <td style="width:35%">{{ $record->credit_card_status_code == 0 ? '刷卡' : ($record->credit_card_status_code == 1 ? '請款' : '入款') }}</td>
                            <th class="table-light" style="width:15%">收款單號</th>
                            <td style="width:35%"><a href="{{ $record->link }}">{{ $record->ro_sn }}</a></td>
                        </tr>
                        <tr>
                            <th class="table-light" style="width:15%">刷卡金額</th>
                            <td style="width:35%">{{ number_format($record->credit_card_price, 2) }}</td>
                            <th class="table-light" style="width:15%">刷卡日期</th>
                            <td style="width:35%">{{ date('Y-m-d', strtotime($record->credit_card_checkout_date)) }}</td>
                        </tr>

                        <tr>
                            <th class="table-light" style="width:15%">請款日期</th>
                            <td style="width:35%">{{ $record->credit_card_transaction_date ? date('Y-m-d', strtotime($record->credit_card_transaction_date)) : '' }}</td>
                            <th class="table-light" style="width:15%">入款日期</th>
                            <td style="width:35%">{{ $record->credit_card_posting_date ? date('Y-m-d', strtotime($record->credit_card_posting_date)) : '' }}</td>
                        </tr>
                        <tr>
                            <th class="table-light" style="width:15%">入款金額</th>
                            <td style="width:35%">{{ number_format($record->credit_card_amt_net, 2) }}</td>
                            <th class="table-light" style="width:15%">手續費</th>
                            <td style="width:35%">{{ number_format($record->credit_card_amt_service_fee, 2) }}</td>
                        </tr>

                        <tr>
                            <th class="table-light" style="width:15%">刷卡對象</th>
                            <td style="width:35%">{{ $record->credit_card_owner_name }}</td>
                            <th class="table-light" style="width:15%">業務員</th>
                            <td style="width:35%">{{ $record->ro_undertaker }}</td>
                        </tr>
                        <tr>
                            <th class="table-light" style="width:15%">會計科目</th>
                            <td style="width:35%">{{ $record->ro_received_grade_code }} {{ $record->ro_received_grade_name }}</td>
                            <th class="table-light" style="width:15%">請款銀行</th>
                            <td style="width:35%">{{ $record->bank_name }}</td>
                        </tr>

                        <tr>
                            <th class="table-light" style="width:15%">線上刷卡</th>
                            <td style="width:35%">{!! $record->credit_card_checkout_mode == 'online' ? '<i class="bi bi-check-lg"></i>' : '<i class="bi bi-x-lg"></i>' !!}</td>
                            <th class="table-light" style="width:15%">EDC</th>
                            <td style="width:35%">{!! $record->credit_card_checkout_mode == 'online' ? '<i class="bi bi-x-lg"></i>' : '<i class="bi bi-check-lg"></i>' !!}</td>
                        </tr>
                        <tr>
                            <th class="table-light" style="width:15%">結帳地區</th>
                            <td style="width:35%">{{ $record->credit_card_area }}</td>
                            <th class="table-light" style="width:15%">授權碼</th>
                            <td style="width:35%">{{ $record->credit_card_authcode }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-auto">
        <a href="{{ route('cms.credit_manager.index') }}" class="btn btn-outline-primary px-4" role="button">
            返回上一頁
        </a>
    </div>
@endsection

@once
    @push('sub-styles')
        <style>

        </style>
    @endpush

    @push('sub-scripts')
        <script>

        </script>
    @endpush
@endonce