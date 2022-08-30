@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">兌現明細清單</h2>
    
    <div id="DivIdToPrint" class="card shadow p-4 mb-4">
        <div class="border rounded p-3 mb-3 text-center">
            <h5>喜鴻國際企業股份有限公司</h5>
            <p class="m-0 lh-1">{{ request('qd') ? date('Y/m/d', strtotime(request('qd'))) : '' }} 兌現之應收票據清單</p>
        </div>
    
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList mb-1">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">編號</th>
                        <th scope="col">會計科目</th>
                        <th scope="col">摘要</th>
                        <th scope="col">支票號碼</th>
                        <th scope="col" class="text-end">借方</th>
                        <th scope="col" class="text-end">貸方</th>
                        <th scope="col">地區</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $d_count = 0;
                    @endphp
                    @if(count($data_list) > 0 && $note_receivable_order)
                        <tr>
                            <th>{{ $d_count + 1 }}</th>
                            <td>{{ $note_receivable_order->code . ' ' . $note_receivable_order->name }}</td>
                            <td>應收票據兌現（{{ $note_receivable_order->name }}）</td>
                            <td></td>
                            <td class="text-end">${{ number_format($note_receivable_order->amt_total_net) }}</td>
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
                            <td class="text-end">${{ number_format($value->cheque_amt_net) }}</td>
                            <td>{{ $value->cheque_deposited_area}}</td>
                        </tr>
                        @php
                            $d_count++;
                        @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4">合計</th>
                        <th class="text-end">${{ number_format($note_receivable_order ? $note_receivable_order->amt_total_net : 0) }}</th>
                        <th class="text-end">${{ number_format($data_list->sum('tw_price')) }}</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    
    <div class="col-auto">
        <button type="button" id="print" class="btn btn-warning px-4">列印畫面</button>
        <a href="{{ $previous_url }}" class="btn btn-outline-primary px-4" role="button">
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
            const cssLink1 = @json(Asset('dist/css/app.css'));
            const cssLink2 = @json(Asset('dist/css/sub-content.css')) + '?1.0';
            $('#print').on('click',  function () {
                printDiv('#DivIdToPrint', [cssLink1, cssLink2]);
            });
        </script>
    @endpush
@endonce