@extends('layouts.main')
@section('sub-content')

    <ul class="nav pm_navbar">
        <li class="nav-item">
            <a class="nav-link" href="{{ Route('cms.inbound_import.index', [], true) }}">上傳檔案</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ Route('cms.inbound_import.import_log', [], true) }}">匯入紀錄</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="{{ Route('cms.inbound_import.inbound_list', [], true) }}">入庫單列表</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ Route('cms.inbound_import.inbound_log', [], true) }}">入庫單調整紀錄</a>
        </li>
    </ul>
    <hr class="narbarBottomLine mb-3">

    <form id="search" action="{{ Route('cms.inbound_import.inbound_list') }}" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">搜尋商品</label>
                    <input class="form-control" value="{{ $searchParam['title'] }}" type="text" name="title"
                           placeholder="輸入商品名稱">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">採購單號</label>
                    <input class="form-control" value="{{ $searchParam['purchase_sn'] }}" type="text" name="purchase_sn"
                           placeholder="輸入採購單號">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">入庫單號</label>
                    <input class="form-control" value="{{ $searchParam['inbound_sn'] }}" type="text" name="inbound_sn"
                           placeholder="輸入入庫單號">
                </div>
            </div>

            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $searchParam['data_per_page'] }}" />
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
                        <th scope="col">#</th>
                        <th scope="col">編輯</th>
                        <th scope="col">採購單號</th>
                        <th scope="col">SKU</th>
                        <th scope="col">商品款式名稱</th>
                        <th scope="col">入庫單</th>
                        <th scope="col">庫存剩餘數量</th>
                        <th scope="col">單價</th>
                        <th scope="col">效期</th>
                        <th scope="col">倉庫</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>
                                <a href="{{ Route('cms.inbound_import.inbound_edit', ['inboundId' => $data->inbound_id], true) }}"
                                   data-bs-toggle="tooltip" title="編輯"
                                   class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </td>
                            <td>{{ $data->event_sn }}</td>
                            <td>{{ $data->style_sku }}</td>
                            <td>{{ $data->product_title }}</td>
                            <td>{{ $data->inbound_sn }}</td>
                            <td class="text-end">{{ number_format($data->qty) }}</td>
                            <td class="text-end">{{ ($data->unit_cost) }}</td>
                            <td>{{ $data->expiry_date ? date('Y/m/d', strtotime($data->expiry_date)) : '' }}</td>
                            <td>{{ $data->depot_name }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="row flex-column-reverse flex-sm-row">
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
            @if($dataList)
                <div class="mx-3">共 {{ $dataList->lastPage() }} 頁(共找到 {{ $dataList->total() }} 筆資料)</div>
                {{-- 頁碼 --}}
                <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
            @endif
        </div>
    </div>
@endsection
@once
    @push('sub-scripts')
        <script>
            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });
        </script>
    @endpush
@endOnce
