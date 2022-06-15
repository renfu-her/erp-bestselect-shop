@extends('layouts.main')
@section('sub-content')
    <h2>庫存管理</h2>

    <form id="search" action="{{ Route('cms.stock.index') }}" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">商品名稱</label>
                    <input class="form-control" value="{{ $searchParam['keyword'] }}" type="text" name="keyword"
                        placeholder="輸入商品名稱或SKU">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">廠商名稱</label>
                    <select class="form-select -select2 -single" name="supplier" aria-label="廠商名稱">
                        <option value="" selected disabled>請選擇</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier['id'] }}" @if ($supplier['id'] == $searchParam['supplier']) selected @endif>
                                {{ $supplier['name'] }}（{{ $supplier['vat_no'] }}）
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">負責人</label>
                    <select class="form-select -select2 -multiple" multiple name="user[]" aria-label="負責人"
                        data-placeholder="多選">
                        @foreach ($users as $user)
                            <option value="{{ $user['id'] }}" @if (in_array($user['id'], $searchParam['user'] ?? [])) selected @endif>
                                {{ $user['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">型態</legend>
                    <div class="px-1 pt-1">
                        @foreach ($typeRadios as $key => $typeRadio)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" value="{{ $key }}"
                                        @if ($searchParam['type'] == $key) checked @endif name="type" type="radio">
                                    {{ $typeRadio }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">耗材</legend>
                    <div class="px-1 pt-1">
                        @foreach ($consumes as $key => $consume)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="consume" type="radio"
                                           value="{{ $consume[0] }}" @if ($consume[0] == $searchParam['consume']) checked @endif>
                                    {{ $consume[1] }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
                <fieldset class="col-12 mb-3">
                    <legend class="col-form-label p-0 mb-2">庫存狀態</legend>
                    <div class="px-1 pt-1">
                        @foreach ($stockRadios as $key => $stockRadio)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" value="{{ $key }}" name="stock[]"
                                        @if (in_array($key, $searchParam['stock'])) checked @endif type="checkbox">
                                    {{ $stockRadio }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">倉庫</label>
                    <select class="form-select -select2  -multiple" multiple name="depot_id[]" aria-label="倉庫" data-placeholder="請選擇倉庫">
                        @foreach ($depotList as $key => $data)
                            <option value="{{ $data->id }}"
                                    @if (in_array($data->id, $searchParam['depot_id'])) selected @endif >{{ $data->name }}</option>
                        @endforeach
                    </select>
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
                        <option value="{{ $value }}" @if ($searchParam['data_per_page'] == $value) selected @endif>
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
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col" class="text-center">明細</th>
                        <th scope="col">商品名稱</th>
                        <th scope="col">款式</th>
                        <th scope="col">類型</th>
                        <th scope="col">SKU</th>
                        <th scope="col">倉庫名稱</th>
                        <th scope="col">理貨倉庫存</th>
                        <th scope="col">寄倉庫存</th>
                        <th scope="col">官網可售數量</th>
                        <!--<th scope="col">預扣庫存</th>-->
                        <th scope="col">安全庫存</th>
                        <th scope="col">廠商名稱</th>
                        <th scope="col">負責人</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td class="text-center">
                                <a href="{{ Route('cms.stock.stock_detail_log', ['depot_id' => $data->depot_id ?? -1, 'id' => $data->id], true) }}"
                                   data-bs-toggle="tooltip" title="明細"
                                   class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-card-list"></i>
                                </a>
                            </td>
                            <td> <a
                                    href="{{ Route('cms.product.edit', ['id' => $data->product_id], true) }}">{{ $data->product_title }}</a>
                            </td>
                            <td>{{ $data->spec }}</td>
                            <td>{{ $data->type_title }}</td>
                            <td>{{ $data->sku }}</td>
                            <td>{{ $data->depot_name }}</td>
                            <td>{{ $data->total_in_stock_num }}</td>
                            <td>{{ $data->total_in_stock_num_csn }}</td>
                            <td>
                                {{ $data->in_stock }}
                            </td>
                            <!--
                            <td>
                                {{-- if (銷售控管 = 0) --}}
                                <a
                                    href="{{ Route('cms.product.edit-stock', ['id' => $data->product_id, 'sid' => $data->id]) }}"></a>
                            </td>
                        -->
                            <td>{{ $data->safety_stock }}

                                @if ($data->in_stock <= $data->safety_stock)
                                    <a href="{{ Route('cms.product.edit-stock', ['id' => $data->product_id, 'sid' => $data->id]) }}"
                                        class="link-danger">(未達)</a>
                                @endif

                            </td>
                            <td>
                                {{ $data->suppliers_name }}
                            </td>
                            <td>{{ $data->user_name }}</td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>
    <div class="row flex-column-reverse flex-sm-row">
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
            @if ($dataList)
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
            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });
        </script>
    @endpush
@endOnce
