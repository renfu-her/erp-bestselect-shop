@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">兌現明細清單</h2>
    <a href="{{ $previous_url }}" class="btn btn-primary" role="button">
        <i class="bi bi-arrow-left"></i> 返回上一頁
    </a>
    <div class="card shadow p-4 mb-4">
        <div class="row mb-3 border rounded mx-0 px-0 pt-2">
            <div class="card-body px-4 d-flex align-items-center bg-white flex-wrap justify-content-end">
                <strong class="flex-grow-1 mb-0">喜鴻國際企業股份有限公司</strong>
            </div>
            <div class="card-body px-4 d-flex align-items-center bg-white flex-wrap justify-content-end">
                <strong class="flex-grow-1 mb-0">{{ request('qd') ? date('Y-m-d', strtotime(request('qd'))) : '' }} 兌現之應收票據清單</strong>
            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-hover tableList mb-1">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">編號</th>
                        <th scope="col">會計科目</th>
                        <th scope="col">摘要</th>
                        <th scope="col">支票號碼</th>
                        <th scope="col">借方</th>
                        <th scope="col">貸方</th>
                        <th scope="col">地區</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $d_count = 0;
                    @endphp
                    @if($note_receivable_order)
                        <tr>
                            <th>{{ $d_count + 1 }}</th>
                            <td>{{ $note_receivable_order->code . ' ' . $note_receivable_order->name }}</td>
                            <td>應收票據兌現（{{ $note_receivable_order->name }}）</td>
                            <td></td>
                            <td>{{ number_format($note_receivable_order->amt_total_net) }}</td>
                            <td></td>
                            <td></td>
                        </tr>
                        @php
                            $d_count++;
                        @endphp
                    @endif
                    @foreach($data_list as $key => $value)
                        <tr>
                            <th>{{ $d_count + 1 }}</th>
                            <td>{{ $value->ro_received_grade_code . ' ' . $value->ro_received_grade_name }}</td>
                            <td>{{ $value->ro_received_grade_name }} {{ $value->cheque_ticket_number }}</td>
                            <td><a href="{{ route('cms.note_receivable.record', ['id'=>$value->cheque_received_id]) }}">{{ $value->cheque_ticket_number }}</a></td>
                            <td></td>
                            <td>{{ number_format($value->cheque_amt_net) }}</td>
                            <td>{{ $value->cheque_deposited_area}}</td>
                        </tr>
                        @php
                            $d_count++;
                        @endphp
                    @endforeach
                    <tr>
                        <th></th>
                        <td></td>
                        <td>合計</td>
                        <td></td>
                        <td>{{ number_format($note_receivable_order ? $note_receivable_order->amt_total_net : 0) }}</td>
                        <td>{{ number_format($data_list->sum('tw_price')) }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
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