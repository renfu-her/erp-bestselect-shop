@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">角色管理</h2>
    <div class="card shadow p-4 mb-4">
        <div class="col mb-4">
            @can('cms.role.create')
                <a href="{{ Route('cms.role.create', null, true) }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> 新增角色
                </a>
            @endcan
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList mb-0">
                <thead>
                <tr>
                    <th scope="col" style="width:10%">#</th>
                    <th scope="col">角色名稱</th>
                    @can('cms.role.edit')
                        <th scope="col" class="text-center">編輯</th>
                    @endcan
                    @can('cms.role.delete')
                        <th scope="col" class="text-center">刪除</th>
                    @endcan
                </tr>
                </thead>
                <tbody>
                @foreach ($dataList as $key => $data)
                    @if ($is_super_admin || $data->name != 'Super Admin')
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>{{ $data->title }}</td>
                            <td class="text-center">
                                @if ($data->name != 'Super Admin')
                                    @can('cms.role.edit')
                                        <a href="{{ Route('cms.role.edit', ['id' => $data->id], true) }}"
                                           data-bs-toggle="tooltip" title="編輯"
                                           class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endcan
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($data->name != 'Super Admin')
                                    @can('cms.role.delete')
                                        <a href="javascript:void(0)"
                                           data-href="{{ Route('cms.role.delete', ['id' => $data->id], true) }}"
                                           data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                           class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @endif
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
