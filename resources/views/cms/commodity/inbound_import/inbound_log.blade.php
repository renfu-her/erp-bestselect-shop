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
            <a class="nav-link" href="{{ Route('cms.inbound_import.inbound_list', [], true) }}">入庫單列表</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="{{ Route('cms.inbound_import.inbound_log', [], true) }}">入庫單調整紀錄</a>
        </li>
    </ul>
    <hr class="narbarBottomLine mb-3">

    <div class="card shadow p-4 mb-4">
        <div class="row justify-content-end mb-4">
            <div class="col">
                <h6>明細</h6>
            </div>
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
            <table class="table table-striped tableList mb-1">
                <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">更新日期</th>
                    <th scope="col">採購單號</th>
                    <th scope="col">SKU</th>
                    <th scope="col">商品名稱</th>
                    <th scope="col">入庫單</th>
                    <th scope="col">調整數量</th>
                    <th scope="col">調整人員</th>
                    <th scope="col">調整原因</th>
                    <th scope="col">事件</th>
                    <th scope="col">行為</th>
                    <th scope="col">倉庫</th>
                </tr>
                </thead>
                <tbody>
                 @foreach ($purchaseLog as $key =>$data)
                     <tr>
                         <th scope="row">{{ $key + 1 }}</th>
                         <td>{{$data->created_at}}</td>
                         <td>{{$data->event_sn}}</td>
                         <td>{{$data->sku}}</td>
                         <td>{{$data->title}}</td>
                         <td>{{$data->inbound_sn}}</td>
                         <td>{{$data->qty}}</td>
                         <td>{{$data->user_name}}</td>
                         <td>{{$data->note}}</td>
                         <td>{{$data->event}}</td>
                         <td>{{$data->feature}}</td>
                         <td>{{$data->depot_name}}</td>
                     </tr>
                 @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row flex-column-reverse flex-sm-row">
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
             @if($purchaseLog)
            <div class="mx-3">共 {{ $purchaseLog->lastPage() }} 頁(共找到 {{ $purchaseLog->total() }} 筆資料)</div>
            {{-- 頁碼 --}}
            <div class="d-flex justify-content-center">{{ $purchaseLog->links() }}</div>
             @endif
        </div>
    </div>

@endsection
@once
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endonce
