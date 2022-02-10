@extends('layouts.main')
@section('sub-content')
<h2 class="mb-4">倉庫管理</h2>
<div class="card shadow p-4 mb-4">
    <div class="row mb-4">
        <div class="col">
            @can('cms.depot.create')
            <a href="{{ Route('cms.depot.create', null, true) }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> 新增倉庫
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
                    <th scope="col">倉庫名稱</th>
{{--                    <th scope="col">代碼</th>--}}
                    <th scope="col">倉商窗口</th>
                    <th scope="col">自取服務</th>
                    <th scope="col">理貨倉</th>
                    <th scope="col">地址</th>
                    <th scope="col">電話</th>
                    <th scope="col" class="text-center">編輯</th>
                    <th scope="col" class="text-center">刪除</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dataList as $key => $data)
                    <tr>
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $data->name }}</td>
{{--                        <td>{{ $data->sn }}</td>--}}
                        <td>{{ $data->sender }}</td>
                        <td>{{ $data->can_pickup ? '開放' : '關閉' }}</td>
                        <td>{{ $data->can_tally ? '是' : '否' }}</td>
                        <td>{{ $data->address }}</td>
                        <td>{{ $data->tel }}</td>
                        <td class="text-center">
                            @can('cms.depot.edit')
                            <a href="{{ Route('cms.depot.edit', ['id' => $data->id], true) }}"
                                data-bs-toggle="tooltip" title="編輯"
                                class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            @endcan
                        </td>
                        <td class="text-center">
                            @can('cms.depot.delete')
                            <a href="javascript:void(0)" data-href="{{ Route('cms.depot.delete', ['id' => $data->id], true) }}"
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
        {{-- 頁碼 --}}
        <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
    </div>
</div>


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
        </script>
    @endpush
@endonce
