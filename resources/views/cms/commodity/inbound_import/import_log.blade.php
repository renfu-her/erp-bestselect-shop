@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">匯入紀錄</h2>

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
                <x-b-form-group name="status" title="匯入狀態">
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
                </x-b-form-group>
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
                        <th scope="col">匯入序號</th>
                        <th scope="col">採購單號</th>
                        <th scope="col">入庫單</th>
                        <th scope="col">匯入狀態</th>
                        <th scope="col">說明</th>
                        <th scope="col">SKU</th>
                        <th scope="col">商品款式名稱</th>
                        <th scope="col">庫存</th>
                        <th scope="col">效期</th>
                        <th scope="col">庫存採購總價</th>
                        <th scope="col">更新時間</th>
                        <th scope="col">匯入人員</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>{{ $data->sn }}</td>
                            <td>{{ $data->purchase_sn }}</td>
                            <td>{{ $data->inbound_sn }}</td>
                            <td>{{ ( \App\Enums\Globals\Status::hasValue($data->status)) ? \App\Enums\Globals\Status::getDescription($data->status) : ''}}</td>
                            <td>{{ $data->memo }}</td>
                            <td>{{ $data->sku }}</td>
                            <td>{{ $data->title }}</td>
                            <td>{{ $data->qty }}</td>
                            <td>{{ $data->expiry_date ? date('Y-m-d', strtotime($data->expiry_date)) : '' }}</td>
                            <td>${{ (isset($data->price)) ? number_format($data->price) : '' }}</td>
                            <td>{{ $data->updated_at }}</td>
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
    @push('sub-scripts')
        <script>
            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });
        </script>
    @endpush
@endOnce
