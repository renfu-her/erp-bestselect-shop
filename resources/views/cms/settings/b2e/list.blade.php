@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">企業網管理</h2>

    <form id="search" action="" method="GET">
        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label">搜尋條件</label>
                    <input class="form-control" name="keyword" type="text" placeholder="請輸入企業名稱 / 企業簡稱 / 統編" value=""
                        aria-label="企業名稱 / 企業簡稱 / 統編">
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
                    @can('cms.b2e-company.create')
                        <a href="{{ Route('cms.b2e-company.create', null, true) }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg pe-1"></i> 新增企業網
                        </a>
                    @endcan
                </div>
                <div class="col-auto">
                    顯示
                    <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                        @foreach (config('global.dataPerPage') as $value)
                            <option value="{{ $value }}" @if ($data_per_page == $value) selected @endif>
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
                            <th scope="col">企業名稱</th>
                            <th scope="col">統編</th>
                            <th scope="col">窗口</th>
                            <th scope="col">電話</th>
                            <th scope="col">電子郵件</th>
                            <th scope="col">銷售通路</th>
                            <th scope="col">業務員</th>
                            @can('cms.b2e-company.edit')
                                <th scope="col" class="text-center">編輯</th>
                            @endcan
                            @can('cms.b2e-company.delete')
                                <th scope="col" class="text-center">刪除</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dataList as $key => $data)
                            <tr>
                                <th scope="row">{{ $key + 1 }}</th>
                                <td>{{ $data->title }}</td>
                                <td>{{ $data->vat_no }}</td>
                                <td>{{ $data->contact_person }}</td>
                                <td>{{ $data->tel }}({{ $data->ext }})</td>
                                <td>{{ $data->contact_email }}</td>
                                <td>{{ $data->sale_channel_title }}</td>
                                <td>{{ $data->user_name }}</td>
                                <td class="text-center">
                                    @can('cms.b2e-company.edit')
                                        <a href="{{ Route('cms.b2e-company.edit', ['id' => $data->id], true) }}"
                                            data-bs-toggle="tooltip" title="編輯"
                                            class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endcan
                                </td>
                                <td class="text-center">
                                    @can('cms.b2e-company.delete')
                                        <a href="{{ Route('cms.b2e-company.delete', ['id' => $data->id], true) }}" data-href=""
                                            data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                            class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row flex-column-reverse flex-sm-row">
            <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
                <div class="mx-3">共 頁(共找到 筆資料)</div>
                {{-- 頁碼 --}}
                <div class="d-flex justify-content-center"></div>
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
        </script>
    @endpush
@endonce
