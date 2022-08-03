@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">發票查詢</h2>

    <fieldset class="col-12 mb-2">
        <div class="p-2 border rounded">
            <a href="{{ Route('cms.order_invoice_manager.index') }}" class="btn btn-primary active" aria-current="page" role="button">發票查詢</a>
            <a href="{{ Route('cms.order_invoice_manager.month') }}" class="btn btn-primary" role="button">月報表</a>
        </div>
    </fieldset>

    <form id="search" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">發票號碼</label>
                    <input class="form-control" type="text" name="invoice_number" value="{{ $cond['invoice_number'] }}" placeholder="請輸入發票號碼">
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">客戶名稱</label>
                    <input class="form-control" type="text" name="buyer_name" value="{{ $cond['buyer_name'] }}" placeholder="請輸入客戶名稱">
                </div>

                <div class="col-12 col-sm-4 mb-3">
                    <label class="form-label">統一編號</label>
                    <input class="form-control" type="text" name="buyer_ubn" value="{{ $cond['buyer_ubn'] }}" placeholder="請輸入統一編號">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">刷卡日期起訖</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate @error('invoice_sdate') is-invalid @enderror" name="invoice_sdate" value="{{ $cond['invoice_sdate'] }}" aria-label="起始日期" />
                        <input type="date" class="form-control -endDate @error('invoice_edate') is-invalid @enderror" name="invoice_edate" value="{{ $cond['invoice_edate'] }}" aria-label="結束日期" />
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                            @error('invoice_sdate')
                                {{ $message }}
                            @enderror
                            @error('invoice_edate')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $data_per_page }}">
                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </div>
    </form>

    <div class="card shadow p-4 mb-4">
        <div class="row justify-content-end mb-4">
            <div class="col-auto">
                顯示
                <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                    @foreach (config('global.dataPerPage') as $value)
                        <option value="{{ $value }}" @if ($data_per_page == $value) selected @endif>
                            {{ $value }}</option>
                    @endforeach
                </select>
                筆
            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th scope="col">編號</th>
                        <th scope="col">發票號碼</th>
                        <th scope="col">訂購單號</th>
                        <th scope="col">日期</th>
                        <th scope="col">類型</th>
                        <th scope="col">買受人</th>
                        <th scope="col">金額</th>
{{--                        <th scope="col">是否作廢</th>--}}
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data_list as $key => $data)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $data->invoice_number }}</td>
                            <td>{{ $data->merchant_order_no }}</td>
                            <td>{{ $data->invoice_date }}</td>
                            <td>{{ $data->category }}</td>
                            <td>{{ $data->buyer_name }}</td>
                            <td>{{ number_format($data->total_amt, 2) }}</td>
{{--                            <td>是否作廢</td>--}}
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row flex-column-reverse flex-sm-row">
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
            @if($data_list)
                <div class="mx-3">共 {{ $data_list->lastPage() }} 頁(共找到 {{ $data_list->total() }} 筆資料)</div>
                {{-- 頁碼 --}}
                <div class="d-flex justify-content-center">{{ $data_list->links() }}</div>
            @endif
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
            $(function() {
                // 顯示筆數選擇
                $('#dataPerPageElem').on('change', function(e) {
                    $('input[name=data_per_page]').val($(this).val());
                    $('#search').submit();
                });
            });
        </script>
    @endpush
@endonce
