@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">選單列表設定</h2>
    <div class="card shadow p-4 mb-4">
        <div class="row justify-content-end mb-4">
            <div class="col">
                @if (!is_null($prev))
                    <a href="{{ Route('cms.navinode.index', ['level' => $prev]) }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> 返回上一階層
                    </a>
                @endif
                <a href="{{ Route('cms.navinode.create', ['level' => $level]) }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> 新增階層
                </a>

            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <form method="GET" action="{{ Route('cms.navinode.sort', ['level' => $level]) }}">
                <table class="table table-striped tableList">
                    <thead>
                        <tr>
                            <th scope="col" style="width:10%">#</th>
                            <th scope="col">名稱</th>
                            <th scope="col">網址</th>
                            <th scope="col">群組名稱</th>
                            <th scope="col">階層</th>
                           
                            <th scope="col" class="text-center">編輯</th>
                            <th scope="col" class="text-center">子階層</th>
                            <th scope="col" class="text-center">刪除</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dataList as $key => $data)
                            <tr>
                                <th scope="row">{{ $key + 1 }}</th>
                                <td>{{ $data->node_title }}</td>
                                <td>{{ $data->url }}</td>
                                <td>{{ $data->group_title }}</td>
                                <td>@if ($data->has_child == 0) 單 @else 多階 @endif</td>
                               
                                <td class="text-center">
                                    <a href="{{ Route('cms.navinode.edit', ['level' => $level, 'id' => $data->id]) }}"
                                        data-bs-toggle="tooltip" title="編輯"
                                        class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                                <td>
                                    @if ($data->has_child == 1)
                                        <a href="{{ Route('cms.navinode.index', ['level' => $level . '-' . $data->id]) }}"
                                            data-bs-toggle="tooltip" title="編輯"
                                            class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                            <i class="bi bi-diagram-2"></i>
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    <a href="javascript:void(0)"
                                        data-href="{{ Route('cms.navinode.delete', ['level' => $level, 'id' => $data->id]) }}"
                                        data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                        class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                           <input type="hidden" name="id[]" value="{{ $data->id }}">
                        @endforeach
                    </tbody>
                </table>
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-plus-lg"></i> 排序
                </button>
            </form>
        </div>
    </div>


    <!-- Modal -->
    <x-b-modal id="confirm-delete">
        <x-slot name="name">刪除確認</x-slot>
        <x-slot name="body">刪除後將無法復原！確認要刪除？</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
        </x-slot>
    </x-b-modal>
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
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
        </script>
    @endpush
@endOnce
