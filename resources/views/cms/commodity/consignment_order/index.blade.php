@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">寄倉訂購</h2>

    <form id="search" action="{{ Route('cms.consignment-order.index') }}" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">倉庫</label>
                    <select class="form-select" name="depot_id" aria-label="倉庫">
                        <option value="" @if ('' == $depot_id ?? '') selected @endif disabled>請選擇</option>
                        @foreach ($depotList as $key => $data)
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

            <div class="col">
                @can('cms.consignment-order.create')
                    <a href="{{ Route('cms.consignment-order.create', null, true) }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg pe-1"></i> 新增寄倉訂購單
                    </a>
                @endcan
            </div>
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
                        <th scope="col">單號</th>
                        <th scope="col" class="text-center">編輯</th>
                        <th scope="col">倉庫名稱</th>
                        <th scope="col">訂購人</th>
                        <th scope="col">訂購日期</th>
                        <th scope="col">商品名稱</th>
                        <th scope="col">SKU碼</th>
                        <th scope="col">單價</th>
                        <th scope="col">數量</th>
                        <th scope="col">小計</th>
                        <th scope="col">出貨日期</th>
                        <th scope="col">物態</th>

                        <th scope="col" class="text-center">刪除</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if($dataList)
                        @foreach ($dataList as $key => $data)
                            <tr>
                                <th scope="row">{{ $key + 1 }}</th>
                                <td>{{ $data->sn }}</td>
                                <td class="text-center">
                                    @can('cms.consignment-order.edit')
                                        <a href="{{ Route('cms.consignment-order.edit', ['id' => $data->id], true) }}"
                                           data-bs-toggle="tooltip" title="編輯"
                                           class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endcan
                                </td>
                                <td>{{ $data->depot_name }}</td>
                                <td>{{ $data->create_user_name }}</td>
                                <td>{{ $data->scheduled_date }}</td>
                                <td>{{ $data->title }}</td>
                                <td>{{ $data->sku }}</td>
                                <td>{{ $data->price }}</td>
                                <td>{{ $data->num }}</td>
                                <td>{{ $data->total_price }}</td>
                                <td>{{ $data->audit_date }}</td>
                                <td>{{ $data->logistic_status }}</td>
                                <td class="text-center">
                                    @can('cms.consignment-order.delete')
                                        @if(null == $data->dlv_audit_date)
                                            <a href="javascript:void(0)" data-href="{{ Route('cms.consignment-order.delete', ['id' => $data->id], true) }}"
                                               data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                               class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        @endif
                                    @endcan
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
                    {{--頁碼--}}
                    <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
                @endif
            </div>
        </div>
    </form>

    <!-- Modal -->
    <x-b-modal id="confirm-delete">
        <x-slot name="title">刪除確認</x-slot>
        <x-slot name="body">刪除後將無法復原！確認要刪除？</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
        </x-slot>
    </x-b-modal>
@endsection

@once
    @push('sub-scripts')
        <script>
            // 顯示筆數選擇
            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
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
