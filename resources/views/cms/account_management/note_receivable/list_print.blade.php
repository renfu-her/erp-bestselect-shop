@extends('layouts.main')

@section('sub-content')
    <div class="card shadow p-4 mb-4">
        <div class="border rounded p-3 mb-3 text-center">
            <h5>喜鴻國際企業股份有限公司應收票據資料</h5>
        </div>

        <table class="table table-striped tableList">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">支票號碼</th>
                    <th scope="col">金額</th>
                    <th scope="col">狀態</th>
                    <th scope="col">收款單號</th>
                    <th scope="col">收票日期</th>
                    <th scope="col">託收次交日期</th>
                    <th scope="col">到期日</th>
                    <th scope="col">兌現日期</th>
                    <th scope="col">抽票日期</th>
                    <th scope="col">業務員</th>
                    <th scope="col">發票人</th>
                    <th scope="col">託收銀行</th>
                    <th scope="col">應付帳號</th>
                    <th scope="col">付款行別</th>
                    <th scope="col">存入地區</th>
                    <th scope="col">備註</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($data_list as $key => $data)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td><a href="{{ route('cms.note_receivable.record', ['id'=>$data->cheque_received_id]) }}">{{ $data->cheque_ticket_number }}</a></td>
                    <td>{{ number_format($data->tw_price) }}</td>
                    <td>{{ $data->cheque_status }}</td>
                    <td>{{ $data->ro_sn }}</td>
                    <td>{{ $data->ro_receipt_date ? date('Y/m/d', strtotime($data->ro_receipt_date)) : '' }}</td>
                    <td>{{ $data->cheque_c_n_date ? date('Y/m/d', strtotime($data->cheque_c_n_date)) : '' }}</td>
                    <td>{{ $data->cheque_due_date ? date('Y/m/d', strtotime($data->cheque_due_date)) : '' }}</td>
                    <td>{{ $data->cheque_cashing_date ? date('Y/m/d', strtotime($data->cheque_cashing_date)) : '' }}</td>
                    <td>{{ $data->cheque_draw_date ? date('Y/m/d', strtotime($data->cheque_draw_date)) : '' }}</td>
                    <td>{{ $data->ro_undertaker }}</td>
                    <td>{{ $data->cheque_drawer }}</td>
                    <td>{{ $data->cheque_banks }}</td>
                    <td>{{ $data->cheque_accounts }}</td>
                    <td></td>
                    <td>{{ $data->cheque_deposited_area }}</td>
                    <td>{{ $data->note}}</td>
                </tr>
            @endforeach
                <tr>
                    <td></td>
                    <td>合計：{{ count($data_list) }} 張</td>
                    <td>合計：{{ number_format($data_list->sum('tw_price')) }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection