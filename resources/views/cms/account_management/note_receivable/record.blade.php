@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-4">應收票據明細</h2>
    <a href="{{ route('cms.note_receivable.index') }}" class="btn btn-primary" role="button">
        <i class="bi bi-arrow-left"></i> 返回上一頁
    </a>

    @if($cheque->cheque_status_code == 'cashed')

    @endif

    <div class="card mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <tbody class="border-top-0">
                        <tr class="table-light text-center">
                            <td colspan="4">應收票據明細</td>
                        </tr>
                        <tr>
                            <th class="table-light" style="width:15%">收款單編號</th>
                            <td style="width:35%">
                                @if($cheque->ro_source_type == 'ord_orders')
                                <a href="{{ route('cms.collection_received.receipt', ['id' => $cheque->ro_source_id]) }}">{{ $cheque->ro_sn }}</a>
                                @elseif($cheque->ro_source_type == 'csn_orders')
                                <a href="{{ route('cms.ar_csnorder.receipt', ['id' => $cheque->ro_source_id]) }}">{{ $cheque->ro_sn }}</a>
                                @elseif($cheque->ro_source_type == 'ord_received_orders')
                                <a href="{{ route('cms.account_received.ro-receipt', ['id' => $cheque->ro_source_id]) }}">{{ $cheque->ro_sn }}</a>
                                @elseif($cheque->ro_source_type == 'acc_request_orders')
                                <a href="{{ route('cms.request.ro-receipt', ['id' => $cheque->ro_source_id]) }}">{{ $cheque->ro_sn }}</a>
                                @endif
                            </td>
                            <th class="table-light" style="width:15%">兌現清單</th>
                            <td style="width:35%"><a href="{{ $cheque->cheque_sn && $cheque->cheque_cashing_date ? route('cms.note_receivable.detail', ['type' => $cheque->cheque_status_code, 'qd' => date('Y-m-d', strtotime($cheque->cheque_cashing_date))]) : 'javascript:void(0);' }}">{{ $cheque->cheque_sn }}</a></td>
                        </tr>

                        <tr>
                            <th class="table-light" style="width:15%">支票號碼</th>
                            <td style="width:35%">{{ $cheque->cheque_ticket_number }}</td>
                            <th class="table-light" style="width:15%">存入地區</th>
                            <td style="width:35%">{{ $cheque->cheque_deposited_area }}</td>
                        </tr>

                        <tr>
                            <th class="table-light" style="width:15%">收票日期</th>
                            <td style="width:35%">{{ $cheque->ro_receipt_date ? date('Y-m-d', strtotime($cheque->ro_receipt_date)) : '' }}</td>
                            <th class="table-light" style="width:15%">託收/次交日</th>
                            <td style="width:35%">{{ $cheque->cheque_c_n_date ? date('Y-m-d', strtotime($cheque->cheque_c_n_date)) : '' }}</td>
                        </tr>

                        <tr>
                            <th class="table-light" style="width:15%">到期日</th>
                            <td style="width:35%">{{ $cheque->cheque_due_date ? date('Y-m-d', strtotime($cheque->cheque_due_date)) : '' }}</td>
                            <th class="table-light" style="width:15%">兌現日</th>
                            <td style="width:35%">{{ $cheque->cheque_cashing_date ? date('Y-m-d', strtotime($cheque->cheque_cashing_date)) : '' }}</td>
                        </tr>

                        <tr>
                            <th class="table-light" style="width:15%">抽票日期</th>
                            <td style="width:35%">{{ $cheque->cheque_draw_date ? date('Y-m-d', strtotime($cheque->cheque_draw_date)) : '' }}</td>
                            <th class="table-light" style="width:15%">團號</th>
                            <td style="width:35%"></td>
                        </tr>

                        <tr>
                            <th class="table-light" style="width:15%">會計科目</th>
                            <td style="width:35%">{{ $cheque->ro_received_grade_code . ' ' . $cheque->ro_received_grade_name }}</td>
                            <th class="table-light" style="width:15%">業務員</th>
                            <td style="width:35%">{{ $cheque->ro_undertaker }}</td>
                        </tr>

                        <tr>
                            <th class="table-light" style="width:15%">金額</th>
                            <td style="width:35%">{{ number_format($cheque->tw_price, 2) }}</td>
                            <th class="table-light" style="width:15%">狀態</th>
                            <td style="width:35%">{{ $cheque->cheque_status }}</td>
                        </tr>

                        <tr>
                            <th class="table-light" style="width:15%">付款銀行</th>
                            <td style="width:35%">{{ $cheque->cheque_banks }}</td>
                            <th class="table-light" style="width:15%">付款帳號</th>
                            <td style="width:35%">{{ $cheque->cheque_accounts }}</td>
                        </tr>

                        <tr>
                            <th class="table-light" style="width:15%">對象類別</th>
                            <td style="width:35%">{{ $cheque->ro_target_name }}</td>
                            <th class="table-light" style="width:15%">發票人</th>
                            <td style="width:35%">{{ $cheque->cheque_drawer }}</td>
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