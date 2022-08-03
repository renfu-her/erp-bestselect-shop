@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">發票月報表</h2>

    <fieldset class="col-12 mb-2">
        <div class="p-2 border rounded">
            <a href="{{ Route('cms.order_invoice_manager.index') }}" class="btn btn-primary" role="button">發票查詢</a>
            <a href="{{ Route('cms.order_invoice_manager.month') }}" class="btn btn-primary active" aria-current="page" role="button">月報表</a>
        </div>
    </fieldset>

    <form id="search" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 mb-3">
                    <div class="col-12 col-sm-6 mb-3">
                        <label class="form-label">報表月份</label>
                        <input type="month" name="invoice_month" class="form-control" value="{{$cond['invoice_month'] ?? ''}}">
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
            <div class="col">
                <button type="button" id="exportBtn" class="btn btn-primary px-4" @cannot('cms.order_invoice_manager.export_excel_month') disabled @endcannot>匯出Excel</button>
            </div>
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
                        <th scope="col">發票日期</th>
                        <th scope="col">買受人</th>
                        <th scope="col">訂購單號</th>
                        <th scope="col">統一編號</th>
                        <th scope="col">摘要</th>
                        <th scope="col">未稅金額</th>
                        <th scope="col">稅金</th>
                        <th scope="col">含稅金額</th>
{{--                        <th scope="col">是否作廢</th>--}}
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data_list as $key => $data)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $data->invoice_number }}</td>
                            <td>{{ $data->invoice_date }}</td>
                            <td>{{ $data->buyer_name }}</td>
                            <td>{{ $data->merchant_order_no }}</td>
                            <td>{{ $data->buyer_ubn }}</td>
                            <td>{{ $data->item_1_name }}</td>
                            <td>{{ number_format($data->amt, 2) }}</td>
                            <td>{{ number_format($data->tax_amt, 2) }}</td>
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

            // Form URL
            let searchForm = $('#search');
            let urls = [
                @json(Route('cms.order_invoice_manager.month')),
                @json(Route('cms.order_invoice_manager.export_excel_month'))
            ];
            let csrf = @json(csrf_token());

            $('#exportBtn').on('click', function() {
                searchForm.attr('action', urls[1]).attr('method', 'post').attr('target', '_blank');
                let csrfELem = $("<input />").attr("type", "hidden")
                    .attr("name", "_token")
                    .attr("value", csrf)
                    .appendTo("#search");
                searchForm.submit();
                csrfELem.remove();
                searchForm.attr('action', urls[0]).attr('method', 'get').attr('target', '_self');
            });
        </script>
    @endpush
@endonce
