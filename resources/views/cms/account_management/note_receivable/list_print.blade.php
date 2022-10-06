@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">應收票據列印</h2>
    <div class="card shadow p-4 mb-4">
        <fieldset id="printTD" class="col-12">
            <legend class="col-form-label p-0 mb-2">列印欄位</legend>
            <div class="px-1 pt-1"></div>
        </fieldset>
    </div>

    <div id="DivIdToPrint" class="card shadow p-4 mb-4">
        <div class="border rounded p-3 mb-3 text-center">
            <h5 class="m-0">喜鴻國際企業股份有限公司應收票據資料</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead class="small table-primary align-middle text-center">
                    <tr>
                        <th scope="col" class="text-center">#</th>
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
                        <th class="text-center">{{ $key + 1 }}</th>
                        <td><a href="{{ route('cms.note_receivable.record', ['id'=>$data->cheque_received_id]) }}">{{ $data->cheque_ticket_number }}</a></td>
                        <td class="text-end">${{ number_format($data->tw_price) }}</td>
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
                </tbody>
                <tfoot class="table-warning">
                    <tr>
                        <th></th>
                        <th>合計：{{ count($data_list) }} 張</th>
                        <th class="text-end">合計：${{ number_format($data_list->sum('tw_price')) }}</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="col-auto">
        <button type="button" id="print" class="btn btn-warning px-4">列印畫面</button>
        <a href="{{ url()->previous() }}" class="btn btn-outline-primary px-4" role="button">
            返回上一頁
        </a>
    </div>
@endsection

@once
    @push('sub-scripts')
        <script>
            const cssLink1 = @json(Asset('dist/css/app.css'));
            const cssLink2 = @json(Asset('dist/css/sub-content.css')) + '?1.0';
            $('#print').on('click',  function () {
                printDiv('#DivIdToPrint', [cssLink1, cssLink2]);
            });

            // 選擇表格顯示欄位
            setPrintTrCheckbox($('#DivIdToPrint table'), $('#printTD > div'), 
                { defaultHide: [5, 7, 8, 9, 10, 11, 13, 14, 15, 16, 17] }
            );
        </script>
    @endpush
@endonce