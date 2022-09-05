@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">{{ $type == 'collection' ? '託收' : '次交票'}}轉存銀行明細清單</h2>
    
    <div id="DivIdToPrint" class="card shadow p-4 mb-4">
        <div class="border rounded p-3 mb-3">
            <p class="lh-1 d-flex">
                <span class="flex-grow-1">{{ $type == 'collection' ? '託收' : '次交票'}}轉存銀行明細清單</span>
                <span class="flex-grow-1">{{ $data_list->first() ? $data_list->first()->ro_received_grade_name : '' }}</span>
            </p>
            <p class="lh-1">{{ $type == 'collection' ? '託收' : '次交票'}}日期：{{ request('qd') ? date('Y/m/d', strtotime(request('qd'))) : '' }}</p>
            <p class="lh-1">{{ $type == 'collection' ? '託收' : '次交票'}}票據存入帳戶：喜鴻國際企業 合庫-長春</p>
            <p class="lh-1 m-0">{{ $type == 'collection' ? '託收' : '次交票'}}票據存入帳號：0844871001158</p>
        </div>

        <div class="table-responsive">
            <table class="table mb-1">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">付款行別</th>
                        <th scope="col">票據號碼</th>
                        <th scope="col">付款帳號</th>
                        <th scope="col">到期日</th>
                        <th scope="col" class="text-end">票面金額</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                        $d_count = 0;
                    @endphp
                    @foreach($data_list as $key => $value)
                        <tr>
                            <th>{{ $key + 1 }}</th>
                            <td>{{ $value->cheque_banks }}</td>
                            <td><a href="{{ route('cms.note_receivable.record', ['id'=>$value->cheque_received_id]) }}">{{ $value->cheque_ticket_number }}</a></td>
                            <td>{{ $value->cheque_accounts }}</td>
                            <td>{{ $value->cheque_due_date ? date('Y/m/d', strtotime($value->cheque_due_date)) : '' }}</td>
                            <td class="text-end">${{ number_format($value->tw_price) }}</td>
                        </tr>
                        @php
                            $d_count++;
                        @endphp
                    @endforeach
                    
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4">張數：{{ $d_count }} 張</th>
                        <th>合計</th>
                        <th class="text-end">${{ number_format($data_list->sum('tw_price')) }}</th>
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