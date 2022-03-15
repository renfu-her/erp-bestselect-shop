@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">現折優惠</h2>

    <form id="search" action="" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <label class="form-label">SKU</label>
                    <input class="form-control" type="text" placeholder="SKU" name="sku">
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label class="form-label">名稱</label>
                    <input class="form-control" type="text" name="keyword" placeholder="組合包名稱 或 組合款式名稱">
                </div>
            </div>

            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $data_per_page }}" />

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
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col">【組合包】款式名稱</th>
                        <th scope="col">SKU</th>
                        <th scope="col">庫存</th>
                        <th scope="col" class="text-center">組裝/拆包</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>【{{ $data->product_title}}】{{ $data->spec }}</td>
                            <td>{{ $data->sku }}</td>
                            <td>{{ $data->in_stock }}</td>
                            <td class="text-center">
                                <a type="button" data-bs-toggle="tooltip" title="組裝/拆包"
                                    href="{{ Route('cms.combo-purchase.edit', ['id' => $data->id], true) }}"
                                    class="icon icon-btn fs-5 text-primary rounded-circle border-0 p-0">
                                    <i class="bi bi-plus-slash-minus"></i>
                                </a>
                            </td>
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
