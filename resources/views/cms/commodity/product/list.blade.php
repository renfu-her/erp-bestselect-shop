@extends('layouts.main')
@section('sub-content')
    <div class="mb-3">
        <h2>商品列表</h2>
        <div class="card shadow p-4 mb-4">
            <div class="col mb-4">
                <a href="{{ Route('cms.product.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> 新增商品
                </a>
            </div>
            <div class="col mb-4">
                顯示
                <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                    @foreach (config('global.dataPerPage') as $value)
                        <option value="{{ $value }}" @if ($data_per_page == $value) selected @endif>{{ $value }}</option>
                    @endforeach
                </select>
                筆
            </div>
            <div class="table-responsive tableOverBox">
                <table class="table table-striped tableList">
                    <thead>
                        <tr>
                            <th scope="col" style="width:10%">#</th>
                            <th scope="col">產品名稱</th>
                            <th scope="col">SKU</th>
                            <th scope="col" class="text-center">編輯</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dataList as $key => $data)
                            <tr>
                                <th scope="row">{{ $key + 1 }}</th>
                                <td>{{ $data['title'] }}</td>
                                <td>{{ $data['sku'] }}</td>
                                <td class="text-center">
                                    <a href="{{ Route('cms.product.edit', ['id' => $data['id']], true) }}"
                                        data-bs-toggle="tooltip" title="編輯"
                                        class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-pencil-square"></i>
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
                {{-- 頁碼 --}}
                <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
            </div>
        </div>

    </div>

@endsection
@once
    @push('sub-styles')
        <style>
            .icon.-close_eye+span.label::before {
                content: '不';
            }

        </style>
    @endpush
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endOnce
