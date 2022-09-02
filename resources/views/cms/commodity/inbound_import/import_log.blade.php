@extends('layouts.main')
@section('sub-content')
    
    <ul class="nav pm_navbar">
        <li class="nav-item">
            <a class="nav-link" href="{{ Route('cms.inbound_import.index', [], true) }}">上傳檔案</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="{{ Route('cms.inbound_import.import_log', [], true) }}">匯入紀錄</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ Route('cms.inbound_import.inbound_list', [], true) }}">入庫單列表</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ Route('cms.inbound_import.inbound_log', [], true) }}">入庫單調整紀錄</a>
        </li>
    </ul>
    <hr class="narbarBottomLine mb-3">

    <form id="search" action="{{ Route('cms.inbound_import.import_log') }}" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">匯入序號</label>
                    <input class="form-control" value="{{ $searchParam['sn'] }}" type="text" name="sn"
                           placeholder="輸入匯入序號">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">採購單號</label>
                    <input class="form-control" value="{{ $searchParam['purchase_sn'] }}" type="text" name="purchase_sn"
                           placeholder="輸入採購單號">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">商品款式名稱</label>
                    <input class="form-control" value="{{ $searchParam['title'] }}" type="text" name="title"
                           placeholder="輸入商品款式名稱">
                </div>
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">匯入狀態</legend>
                    <div class="px-1">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                全部
                                <input class="form-check-input" value="all" name="status" type="radio"
                                       @if ('all' == $searchParam['status'] ) checked @endif >
                            </label>
                        </div>
                        @foreach($all_status as $key_status => $val_status)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    {{$val_status}}
                                    <input class="form-check-input" value="{{$key_status}}" name="status" type="radio"
                                           @if ($key_status.'' == $searchParam['status'] ) checked @endif >
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
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
            <table class="table table-striped tableList table-sm small">
                <thead class="align-middle">
                    <tr>
                        <th scope="col" style="width:40px">#</th>
                        <td scope="col" class="wrap">
                            <div>匯入序號</div>
                            <div class="fw-bold">採購單號</div>
                            <div>入庫單</div>
                        </td>
                        <th scope="col" class="wrap lh-1">匯入<br>狀態</th>
                        <th scope="col">說明</th>
                        <th scope="col" class="wrap lh-1">商品款式</th>
                        <th scope="col" class="text-end">庫存</th>
                        <th scope="col">效期</th>
                        <th scope="col" class="wrap lh-1 text-center">庫存採購總價</th>
                        <th scope="col">更新時間</th>
                        <th scope="col" class="wrap lh-1">匯入<br>人員</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td class="wrap">
                                <div>{{ $data->sn }}</div>
                                <div class="fw-bold">{{ $data->purchase_sn }}</div>
                                <div>{{ $data->inbound_sn ?? '-' }}</div>
                            </td>
                            <td @class(['text-danger' => $data->status === 0, 'text-success' => $data->status === 1])>
                                {{ ( \App\Enums\Globals\Status::hasValue($data->status)) ? \App\Enums\Globals\Status::getDescription($data->status) : ''}}
                            </td>
                            <td @class(['wrap', 'minWidth100' => $data->memo != ''])>{{ $data->memo }}</td>
                            <td class="wrap">
                                <div class="lh-1 text-nowrap text-secondary">{{ $data->sku }}</div>
                                <div class="lh-lg">{{ $data->title ? $data->title : '-' }}</div>
                            </td>
                            <td class="text-end">{{ number_format($data->qty) }}</td>
                            <td>{{ $data->expiry_date ? date('Y/m/d', strtotime($data->expiry_date)) : '' }}</td>
                            <td class="text-end">${{ (isset($data->price)) ? number_format($data->price) : '' }}</td>
                            <td class="wrap">{{ $data->updated_at ? date('Y/m/d H:i:s', strtotime($data->updated_at)) : '' }}</td>
                            <td>{{ $data->import_user_name }}</td>
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
    @push('sub-styles')
        <style>
            .minWidth100 {
                min-width: 100px;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });
        </script>
    @endpush
@endOnce
