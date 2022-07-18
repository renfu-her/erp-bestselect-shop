@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">分潤報表</h2>


    <div class="card shadow p-4 mb-4">

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col">報表月份</th>
                        <th scope="col">推薦碼</th>
                        <th scope="col">筆數</th>
                        <th scope="col">銷售獎金</th>
                        <th scope="col">匯款日期</th>
                        <th scope="col">銀行</th>
                        <th scope="col">建立日期</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customer_reports as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>{{ $data->report_at }}</td>
                            <td> <a href="#">
                                    {{ $data->name }}_{{ $data->mcode }}
                                </a>
                            </td>
                            <td>{{ $data->qty }}</td>
                            <td>{{ $data->bonus }}</td>
                            <td></td>
                            <td>{{ $data->bank_title }}</td>
                            <td>{{ $data->created_at }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>{{ $month_report->qty }}</td>
                        <td>{{ $month_report->bonus }}</td>
                        <td></td>
                        <td></td>
                        <td></td>

                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
@once
    @push('sub-scripts')
    @endpush
@endOnce
