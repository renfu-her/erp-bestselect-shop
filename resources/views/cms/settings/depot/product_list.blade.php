@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-4">寄倉選品</h2>

    <div class="card shadow p-4 mb-4">
        <form id="search" action="{{ Route('cms.depot.product-index', ['id' => $depot->id], true) }}" method="GET">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">倉庫名稱</label>
                    <input class="form-control" type="text" value="{{$depot->name}}" readonly disabled>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">商品名稱</label>
                    <input class="form-control" type="text" name="keyword" placeholder="輸入商品名稱或SKU" value="{{request('keyword')}}">
                </div>
            </div>
            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $data_per_page }}" />
                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </form>
    </div>


    <div class="card shadow p-4 mb-4">
        <div class="row justify-content-end mb-4">
            <div class="col">
                @can('cms.depot.product-create')
                <a href="{{ Route('cms.depot.product-create', ['id' => $depot->id], true) }}" class="btn btn-success" role="button">
                    <i class="bi bi-inboxes-fill"></i> 選品
                </a>
                @endcan

                @can('cms.depot.product-edit')
                <a href="{{ Route('cms.depot.product-edit', ['id' => $depot->id], true) }}" class="btn btn-primary" role="button">
                    <i class="bi bi-pencil-square"></i> 修改
                </a>
                @endcan
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
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col">寄倉商品編號</th>
                        <th scope="col">商品</th>
                        <th scope="col">款式</th>
                        <th scope="col">SKU</th>
                        <th scope="col">寄倉售價</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>{{ $data->depot_product_no }}</td>
                            <td>{{ $data->product_title }}</td>
                            <td>{{ $data->spec }}</td>
                            <td>{{ $data->sku }}</td>
                            <td>$ {{ number_format($data->depot_price, 2) }}</td>
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
    
    <div>
        <div class="col-auto">
            <a href="{{ Route('cms.depot.index') }}"
                class="btn btn-outline-primary px-4" role="button">返回列表</a>
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
            // 顯示筆數選擇
            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });
        </script>
    @endpush
@endOnce
