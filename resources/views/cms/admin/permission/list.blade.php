@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">頁面權限管理</h2>
    <div class="card shadow p-4 mb-4">
        <div class="row mb-4">
            <div class="col-auto">
                @can('cms.permission.create')
                    <a href="{{ Route('cms.permission.create', null, true) }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> 新增頁面
                    </a>
                @endcan
            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                <tr>
                    <th scope="col" style="width:10%">#</th>
                    <th scope="col">頁面名稱</th>
                    <th scope="col">權限</th>
                    <th scope="col" class="text-center">名稱編輯</th>
                    <th scope="col" class="text-center">權限設定</th>
                    <th scope="col" class="text-center">刪除</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($dataList as $key => $data)
                    <tr>
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $data->title }}</td>
                        <td class="wrap">
                            @php
                                echo implode(
                                    '、',
                                    array_map(function ($n) {
                                        return $n->title;
                                    }, $data->permissions),
                                );
                            @endphp
                        </td>
                        <td class="text-center">
                            @can('cms.permission.edit')
                                <a href="{{ Route('cms.permission.edit', ['id' => $data->id], true) }}"
                                   data-bs-toggle="tooltip" title="頁面名稱編輯"
                                   class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan
                        </td>
                        <td class="text-center">
                            @can('cms.permission.child')
                                <a href="{{ Route('cms.permission.child', ['id' => $data->id], true) }}"
                                   data-bs-toggle="tooltip" title="權限設定"
                                   class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-file-earmark-lock2"></i>
                                </a>
                            @endcan
                        </td>
                        <td class="text-center">
                            @can('cms.permission.delete')
                                <a href="javascript:void(0)"
                                   data-href="{{ Route('cms.permission.delete', ['id' => $data->id], true) }}"
                                   data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                   class="icon icon-btn fs-5 text-danger rounded-circle border-0">
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
            $('#confirm-delete').on('show.bs.modal', function (e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
        </script>
    @endpush
@endonce
