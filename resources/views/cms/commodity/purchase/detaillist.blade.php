@extends('layouts.main')
@section('sub-content')

<h2 class="mb-4">採購單管理</h2>

<form id="search" action="{{ Route('cms.purchase.index') }}" method="GET">
    <div class="card shadow p-4 mb-4">
        <h6>搜尋條件</h6>
        <div class="row">
            <div class="col-12 mb-3">
                <label class="form-label">出貨日期範圍</label>
                <div class="input-group has-validation">
                    <input type="date" class="form-control -startDate @error('startDate') is-invalid @enderror"
                           name="startDate" value="{{ $startDate }}" aria-label="出貨日期起始" required />
                    <input type="date" class="form-control -endDate @error('endDate') is-invalid @enderror"
                           name="endDate" value="{{ $endDate }}" aria-label="出貨日期結束" required />
                    <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                    <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                    <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                    <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                    <button class="btn" data-daysBefore="month" type="button">本月</button>
                    <div class="invalid-feedback">
                        @error('startDate')
                        {{ $message }}
                        @enderror
                        @error('endDate')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label">發票號碼</label>
                <input class="form-control" name="title" type="text" placeholder="發票號碼" value="{{ $title }}"
                       aria-label="發票號碼">
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
            <div class="col">
                @can('cms.purchase.create')
                <a href="{{ Route('cms.purchase.create', null, true) }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg pe-1"></i> 新增採購單
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
                    <th scope="col">採購單號</th>
                    <th scope="col">商品名稱</th>
                    <th scope="col">採購日期</th>
                    <th scope="col">入庫狀態</th>
                    <th scope="col">訂金單號</th>
                    <th scope="col">尾款單號</th>
                    <th scope="col">SKU</th>
                    <th scope="col">總價</th>
                    <th scope="col">單價</th>
                    <th scope="col">數量</th>
                    <th scope="col">採購人員</th>
                    <th scope="col">廠商</th>
                    <th scope="col">發票號碼</th>
                    <th scope="col" class="text-center">編輯</th>
                    <th scope="col" class="text-center">刪除</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($dataList as $key => $data)
                    <tr>
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $data->sn }}</td>
                        <td>{{ $data->title }}</td>
                        <td>{{ $data->scheduled_date }}</td>
                        <td>{{ $data->inbound_status }}</td>
                        <td>{{ $data->deposit_num }}</td>
                        <td>{{ $data->final_pay_num }}</td>
                        <td>{{ $data->sku }}</td>
                        <td>{{ $data->total_price }}</td>
                        <td>{{ $data->price }}</td>
                        <td>{{ $data->num }}</td>
                        <td>{{ $data->user_name }}</td>
                        <td>{{ $data->supplier_name }}</td>
                        <td>{{ $data->invoice_num }}</td>
                        <td class="text-center">
{{--                            @can('admin.purchase.edit')--}}
                            <a href="{{ Route('cms.purchase.edit', ['id' => $data->id], true) }}"
                               data-bs-toggle="tooltip" title="編輯"
                               class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                <i class="bi bi-pencil-square"></i>
                            </a>
{{--                            @endcan--}}
                        </td>
                        <td class="text-center">
{{--                            @can('admin.purchase.delete')--}}
                            <a href="javascript:void(0)" data-href="{{ Route('cms.purchase.delete', ['id' => $data->id], true) }}"
                               data-bs-toggle="modal" data-bs-target="#confirm-delete"
                               class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                <i class="bi bi-trash"></i>
                            </a>
{{--                            @endcan--}}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row flex-column-reverse flex-sm-row">
        <div class="col-auto">

        </div>
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
            <div class="mx-3">共 {{ $dataList->lastPage() }} 頁(共找到 {{ $dataList->total() }} 筆資料)</div>
            {{-- 頁碼 --}}
            <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
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
    @push('scripts')
        <script>
            // 顯示筆數選擇
            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
        </script>
    @endpush
@endonce
