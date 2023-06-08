@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">活動-四季鮮果</h2>

    <div class="card shadow p-4 mb-4">
        <div class="row mb-4">
            <div class="col">
                @can('cms.act-fruits.index')
                    <a href="{{ Route('cms.act-fruits.create', null, true) }}" class="btn btn-primary mb-1">
                        <i class="bi bi-plus-lg"></i> 新增水果
                    </a>
                @endcan
                <a href="{{ Route('cms.act-fruits.season') }}" class="btn btn-success mb-1">水果分類設定</a>
            </div>
            <div class="col-auto">
                <form id="search" method="get">
                    顯示
                    <select class="form-select d-inline-block w-auto" name="data_per_page" aria-label="表格顯示筆數">
                        <option value="40" @if ($data_per_page == 40) selected @endif>40</option>
                        <option value="60" @if ($data_per_page == 60) selected @endif>60</option>
                        <option value="100" @if ($data_per_page == 100) selected @endif>100</option>
                    </select>
                    筆
                </form>
            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList mb-1">
                <thead class="align-middle">
                    <tr>
                        <th scope="col" width="50" class="text-center">#</th>
                        <th scope="col" width="50" class="text-center">編輯</th>
                        <th scope="col">名稱</th>
                        <th scope="col">產季</th>
                        <th scope="col">目前狀態</th>
                        <th scope="col" style="width:10%" class="text-center">刪除</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row" class="text-center">{{ $key + 1 }}</th>
                            <td class="text-center">
                                @can('cms.act-fruits.index')
                                    <a href="{{ Route('cms.act-fruits.edit', ['id' => $data->id], true) }}" data-bs-toggle="tooltip"
                                        title="編輯" class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                @endcan
                            </td>
                            <td>{{ $data->title }}</td>
                            <td class="wrap lh-sm">{{ $data->season }}</td>
                            <td>{{ $data->status }}</td>
                            <td class="text-center">
                                @can('cms.act-fruits.index')
                                    <a href="javascript:void(0)" data-href="{{ Route('cms.act-fruits.delete', ['id' => $data->id], true) }}" data-bs-toggle="modal"
                                        data-bs-target="#confirm-delete"
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

    @if ($dataList->hasPages())
        <div class="row flex-column-reverse flex-sm-row mb-4">
            <div class="col d-flex justify-content-end align-items-center">
                {{-- 頁碼 --}}
                <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
            </div>
        </div>
    @endif

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
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
            // 顯示筆數選擇
            $('select[name="data_per_page"]').on('change', function(e) {
                $('#search').submit();
            });
        </script>
    @endpush
@endOnce
