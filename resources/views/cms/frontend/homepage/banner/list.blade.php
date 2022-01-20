@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">首頁設定</h2>
    <x-b-home-navi></x-b-home-navi>
    <div class="col">
        <a href="{{ Route('cms.homepage.banner.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> 新增橫幅廣告
        </a>
    </div>

    <form class="card-body" method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf
        <table>
            <thead>
            </thead>
            <tbody>
            @foreach ($dataList as $key => $data)
                <tr>
                    <th scope="row">{{ $key + 1 }}</th>
                    <td>{{ $data->id }}</td>
                    <td>{{ $data->title }}</td>
                    <td class="text-center">
                        <a href="{{ Route('cms.homepage.banner.edit', ['id' => $data->id], true) }}"
                           data-bs-toggle="tooltip" title="編輯"
                           class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                    </td>

                    <td>
                        <input type="hidden" name="banner_id[]" value="{{$data->id}}">
                        <a href="javascript:void(0)"
                           data-href="{{ Route('cms.homepage.banner.delete', ['id' => $data->id], true) }}"
                           data-bs-toggle="modal" data-bs-target="#confirm-delete"
                           class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
        </div>
    </form>

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
            $('#confirm-delete').on('show.bs.modal', function (e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
        </script>
    @endpush
@endonce
