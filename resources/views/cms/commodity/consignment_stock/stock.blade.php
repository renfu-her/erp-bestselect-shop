@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">寄倉庫存</h2>

    <form id="search" action="{{ Route('cms.consignment-stock.stocklist') }}" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">倉庫</label>
                    <select class="form-select" name="depot_id" aria-label="倉庫">
                        <option value="" @if ('' == $depot_id ?? '') selected @endif disabled>請選擇</option>
                        <@foreach ($depotList as $key => $data)
                            <option value="{{ $data->id }}"
                                    @if ($data->id == $depot_id ?? '') selected @endif>{{ $data->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $data_per_page }}" />
                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </div>
    </form>
    <form id="actionForms">
        @csrf
        <div class="card shadow p-4 mb-4">
            <div class="row justify-content-end mb-4">
                <div class="col-auto">
                    顯示
                    <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                        @foreach (config('global.dataPerPage') as $value)
                            <option value="{{ $value }}" @if ($data_per_page == $value) selected @endif>{{ $value }}</option>
                        @endforeach
                    </select>
                    筆
                </div>
            </div>

            <div class="table-responsive tableOverBox">
                <table class="table table-striped tableList">
                    <thead>
                    <tr>
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col">SKU碼</th>
                        <th scope="col">商品名稱</th>
                        <th scope="col">款式</th>
                        <th scope="col">寄倉數量</th>
                        <th scope="col">已銷售數量</th>
                        <th scope="col">耗材消耗數量</th>
                        <th scope="col">剩餘數量</th>
                        <th scope="col" class="text-center">明細</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($dataList)
                        @foreach ($dataList as $key => $data)
                            <tr>
                                <th scope="row">{{ $key + 1 }}</th>
                                <td>{{ $data->sku }}</td>
                                <td>{{ $data->product_title }}</td>
                                <td>{{ $data->spec }}</td>
                                <td>{{ $data->inbound_num }}</td>
                                <td>{{ $data->sale_num }}</td>
                                <td>{{ $data->consume_num }}</td>
                                <td>{{ $data->available_num }}</td>

                                <td class="text-center">
                                    <a href="{{ Route('cms.consignment-stock.stock_detail_log', ['id' => $data->product_style_id], true) }}"
                                       data-bs-toggle="tooltip" title="明細"
                                       class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row flex-column-reverse flex-sm-row">
            <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
                @if($dataList)
                    <div class="mx-3">共 {{ $dataList->lastPage() }} 頁(共找到 {{ $dataList->total() }} 筆資料)</div>
                    頁碼
                    <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
                @endif
            </div>
        </div>
    </form>

@endsection

@once
    @push('sub-scripts')
        <script>
            // 顯示筆數選擇
            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });
            // 清空
            $('#clear_iStatus').on('click', function(e) {
                selectStatus = [];
                Chips_regions.clear();
                e.preventDefault();
            });
        </script>
    @endpush
@endonce
