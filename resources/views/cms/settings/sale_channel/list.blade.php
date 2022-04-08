@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">銷售通路管理</h2>
    <div class="card shadow p-4 mb-4">
        <div class="row mb-4">
            <div class="col-auto">
                @can('cms.sale_channel.create')
                    <a href="{{ Route('cms.sale_channel.create', null, true) }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> 新增通路
                    </a>
                @endcan
            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col">通路名稱</th>
                        <th scope="col">通路聯絡人</th>
                        <th scope="col">通路聯絡電話</th>
                        <th scope="col">庫存類型</th>
                        <th scope="col">折扣</th>
                        <th scope="col" class="text-center">同步價格</th>
                        <th scope="col" class="text-center">編輯</th>
                        <th scope="col" class="text-center">刪除</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>{{ $data->title }}</td>
                            <td>{{ $data->contact_person }}</td>
                            <td>{{ $data->contact_tel }}</td>
                            <td>{{ $data->is_realtime_title }}</td>
                            <td>{{ $data->discount }}</td>
                            <td class="text-center">
                                @if ($data->is_master != 1)
                                    <a href="{{ Route('cms.sale_channel.batch-price', ['id' => $data->id], true) }}"
                                        data-bs-toggle="tooltip" title="同步價格"
                                        class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-tag"></i>
                                    </a>
                                @endif
                            </td>
                            <td class="text-center">
                                {{-- @can('admin.sale_channel.edit') --}}
                                <a href="{{ Route('cms.sale_channel.edit', ['id' => $data->id], true) }}"
                                    data-bs-toggle="tooltip" title="編輯"
                                    class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                {{-- @endcan --}}
                            </td>
                            <td class="text-center">
                                {{-- @can('admin.sale_channel.delete') --}}
                                <a href="javascript:void(0)"
                                    data-href="{{ Route('cms.sale_channel.delete', ['id' => $data->id], true) }}"
                                    data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                    class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                    <i class="bi bi-trash"></i>
                                </a>
                                {{-- @endcan --}}
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
