@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">{{ $type == 'collection' ? '託收' : '次交票'}}轉存銀行明細清單</h2>
    <a href="{{ $previous_url }}" class="btn btn-primary" role="button">
        <i class="bi bi-arrow-left"></i> 返回上一頁
    </a>
    <div class="card shadow p-4 mb-4">
        <div class="row mb-3 border rounded mx-0 px-0 pt-2">
            <div class="card-body px-4 d-flex align-items-center bg-white flex-wrap justify-content-end">
                <strong class="flex-grow-1 mb-0">{{ $type == 'collection' ? '託收' : '次交票'}}轉存銀行明細清單</strong>
                <strong class="flex-grow-1 mb-0">{{ $data_list->first() ? $data_list->first()->ro_received_grade_name : '' }}</strong>
            </div>
            <div class="card-body px-4 d-flex align-items-center bg-white flex-wrap justify-content-end">
                <strong class="flex-grow-1 mb-0">{{ $type == 'collection' ? '託收' : '次交票'}}日期：{{ request('qd') ? date('Y-m-d', strtotime(request('qd'))) : '' }}</strong>
            </div>
            <div class="card-body px-4 d-flex align-items-center bg-white flex-wrap justify-content-end">
                <strong class="flex-grow-1 mb-0">{{ $type == 'collection' ? '託收' : '次交票'}}票據存入帳戶：喜鴻國際企業 合庫-長春 帳號：0844871001158</strong>
            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-hover tableList mb-1">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">編號</th>
                        <th scope="col">付款行別</th>
                        <th scope="col">票據號碼</th>
                        <th scope="col">付款帳號</th>
                        <th scope="col">到期日</th>
                        <th scope="col">票面金額</th>
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
                            <td>{{ $value->cheque_due_date ? date('Y-m-d', strtotime($value->cheque_due_date)) : '' }}</td>
                            <td>{{ number_format($value->tw_price) }}</td>
                        </tr>
                        @php
                            $d_count++;
                        @endphp
                    @endforeach
                    <tr>
                        <th>{{ $d_count + 1}}</th>
                        <td></td>
                        <td></td>
                        <td>張數：{{ $d_count }} 張</td>
                        <td>合計</td>
                        <td>{{ number_format($data_list->sum('tw_price')) }}</td>
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