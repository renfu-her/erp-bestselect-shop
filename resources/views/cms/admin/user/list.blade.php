@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">員工帳號管理</h2>
    <x-b-search-account></x-b-search-account>
    <div class="card shadow p-4 mb-4">
        <div class="col mb-4">
            @can('cms.user.create')
                <a href="{{ Route('cms.user.create', null, true) }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> 新增帳號
                </a>
            @endcan
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col">姓名</th>
                        <th scope="col">帳號</th>
                        <th scope="col">角色設定狀況</th>
                        <th scope="col">通路權限</th>
                        @can('cms.user.edit')
                            <th scope="col" class="text-center">編輯</th>
                        @endcan
                        @can('cms.user.delete')
                            <th scope="col" class="text-center">刪除</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>{{ $data['name'] }}</td>
                            <td>{{ $data['account'] }}</td>
                            <td @class(['text-danger' => count($data['role']) < 1])>
                                @if (count($data['role']) >= 1)
                                    已設定角色
                                @else
                                    未設定角色
                                @endif
                            </td>
                            <td class="text-center">
                                @can('cms.user.salechannel')
                                    <a href="{{ Route('cms.user.salechannel', ['id' => $data['id']], true) }}"
                                        data-bs-toggle="tooltip" title="通路權限"
                                        class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                @endcan
                            </td>
                            <td class="text-center">
                                @can('cms.user.edit')
                                    <a href="{{ Route('cms.user.edit', ['id' => $data['id']], true) }}"
                                        data-bs-toggle="tooltip" title="編輯"
                                        class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                @endcan
                            </td>
                            <td class="text-center">
                                @can('cms.user.delete')
                                    <a href="javascript:void(0)"
                                        data-href="{{ Route('cms.user.delete', ['id' => $data['id']], true) }}"
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
            <div class="d-flex justify-content-center">{{ $users->links() }}</div>
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
