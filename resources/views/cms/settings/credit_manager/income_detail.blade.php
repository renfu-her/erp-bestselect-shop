@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">信用卡入款明細</h2>
    <a href="{{ route('cms.credit_manager.index') }}" class="btn btn-primary" role="button">
        <i class="bi bi-arrow-left"></i> 返回上一頁
    </a>
    <div class="card shadow p-4 mb-4">
        <div class="row mb-3 border rounded mx-0 px-0 pt-2">
            <div class="card-body px-4 d-flex align-items-center bg-white flex-wrap justify-content-end">
                <strong class="flex-grow-1 mb-0">喜鴻國際企業股份有限公司</strong>
            </div>
            <div class="card-body px-4 d-flex align-items-center bg-white flex-wrap justify-content-end">
                <strong class="flex-grow-1 mb-0">{{ date('Y/m/d', strtotime($income->posting_date)) }} 信用卡入款明細</strong>
            </div>
            <div class="card-body px-4 d-flex align-items-center bg-white flex-wrap justify-content-end">
                <strong class="flex-grow-1 mb-0">入款人員：{{ $income->affirmant_name }} {{ date('Y/m/d H:i:s', strtotime($income->updated_at)) }}</strong>
            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-hover tableList mb-1">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">會計科目</th>
                        <th scope="col">摘要</th>
                        <th scope="col">信用卡號</th>
                        <th scope="col">借方</th>
                        <th scope="col">貸方</th>
                        <th scope="col">地區</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <th>1</th>
                        <td>{{ $income->fee_grade_code }} {{ $income->fee_grade_name }}</td>
                        <td>信用卡手續費</td>
                        <td></td>
                        <td>{{ number_format($income->amt_total_service_fee) }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>2</th>
                        <td>{{ $income->net_grade_code }} {{ $income->net_grade_name }}</td>
                        <td>信用卡入款</td>
                        <td></td>
                        <td>{{ number_format($income->amt_total_net) }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                    @foreach ($data_list as $key => $value)
                        <tr>
                            <th>{{ $key + 3 }}</th>
                            <td>{{ $value->ro_received_grade_code }} {{ $value->ro_received_grade_name }}</td>
                            <td>信用卡 {{ $value->credit_card_number }}</td>
                            <td><a href="{{ route('cms.credit_manager.record', ['id'=>$value->credit_card_received_id])}}">{{ $value->credit_card_number }}</a></td>
                            <td></td>
                            <td>{{ number_format($value->credit_card_price) }}</td>
                            <td>{{ $value->credit_card_area}}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="4" class="text-center">合計</td>
                        <td>{{ number_format($income->amt_total_service_fee + $income->amt_total_net) }}</td>
                        <td>{{ number_format($data_list->sum('credit_card_price')) }}</td>
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