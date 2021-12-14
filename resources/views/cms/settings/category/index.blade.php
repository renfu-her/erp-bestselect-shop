@extends('layouts.main')
@section('sub-content')
<div class="d-flex align-items-center">
    <h2>商品類別</h2>
</div>

<div class="card shadow p-4 mb-4">
    <div class="row mb-4">
        <div class="col-auto">
            @can('cms.category.create')
                <a href="{{ Route('cms.category.create', null, true) }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> 新增商品類別
                </a>
            @endcan
        </div>
    </div>

    <div class="table-responsive tableOverBox">
        <table class="table table-striped tableList mb-0">
            <thead>
            <tr>
                <th scope="col" style="width:10%">#</th>
                <th scope="col">商品類別</th>
                <th scope="col">編輯</th>
                <th scope="col">刪除</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($dataList as $key => $data)
                <tr>
                    <th scope="row">{{ $key + 1 }}</th>
                    <td>{{ $data->category }}</td>
                    <td class="text-center">
                        <a href="{{ Route('cms.category.edit', ['id' => $data->id], true) }}"
                           data-bs-toggle="tooltip" title="編輯"
                           class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                    </td>
                    <td class="text-center">
                        <a href="javascript:void(0)" data-href="{{ Route('cms.category.delete', ['id' => $data->id], true) }}"
                           data-bs-toggle="modal" data-bs-target="#confirm-delete"
                           class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                            <i class="bi bi-trash"></i>
                        </a>
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
    @push('scripts')
        <script>
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
        </script>
    @endpush
@endonce
@once
    @push('sub-styles')
        <style>
        </style>
    @endpush
    @push('sub-scripts')
        <script>
        </script>
    @endpush
@endOnce
