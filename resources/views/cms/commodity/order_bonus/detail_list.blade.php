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
                        <th scope="col">收款人帳號</th>
                        <th scope="col">收款人銀行代號</th>
                        <th scope="col">收款人身分證(統一編號)</th>
                        <th scope="col">收款人戶名</th>
                        <th scope="col">收款人聯絡姓名</th>
                        <th scope="col">手續費負擔別</th>
                        <th scope="col">付款人帳號</th>
                        <th scope="col">付款銀行代號</th>
                        <th scope="col">付款人身分證(統一編號)</th>
                        <th scope="col">付款人戶名</th>
                        <th scope="col">EDI用戶代碼</th>  
                        <th scope="col">入帳通知處理方式</th>  
                        <th scope="col">付款說明</th>    
                        <th scope="col">業務類別</th>
                        
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
                            <td>{{ $data->created_at }}</td>
                            <td>{{ $data->bank_title }}</td>
                            <td>{{ $data->bank_account }}</td>
                            <td>{{ $data->bank_code }}</td>
                            <td>{{ $data->identity_sn }}</td>
                            <td>{{ $data->bank_account_name }}</td>
                            <td>{{ $data->bank_account_name }}</td>
                            <td>{{ $baseData->pay_code }}</td>
                            <td>{{ $baseData->pay_bank_account }}</td>
                            <td>{{ $baseData->pay_bank_code }}</td>
                            <td>{{ $baseData->pay_identity_sn }}</td>
                            <td>{{ $baseData->pay_bank_account_name }}</td>
                            <td>{{ $baseData->pay_edi }}</td>
                            <td>{{ $baseData->pay_notify }}</td>
                            <td>{{ $baseData->pay_note }}</td>
                            <td>{{ $baseData->pay_category }}</td>
                           
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="3"></td>
                        <td>{{ $month_report->qty }}</td>
                        <td>{{ $month_report->bonus }}</td>
                        <td colspan="16"></td>
                       
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
