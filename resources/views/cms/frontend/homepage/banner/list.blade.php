@extends('layouts.main')
@section('sub-content')
<h2 class="mb-3">首頁設定</h2>
<x-b-home-navi></x-b-home-navi>

<form method="post" action="{{ $formAction }}">
    @method('POST')
    @csrf
    <div class="card shadow p-4 mb-4">
        <div class="col mb-4">
            @can('cms.homepage.edit')
            <a href="{{ Route('cms.homepage.banner.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> 新增橫幅廣告
            </a>
            @endcan
        </div>

        <div class="d-flex col-12 mb-3 border-bottom border-2 border-dark fw-bold">
            <div class="col-auto px-2" style="width: 50px;">#</div>
            <div class="col px-2">橫幅廣告主標題</div>
            <div class="col-auto text-center" style="width: 100px;">預覽圖</div>
            <div class="col-auto text-center" style="width: 40px;">顯示</div>
            <div class="col-auto text-center" style="width: 40px;">編輯</div>
            <div class="col-auto text-center" style="width: 40px;">排序</div>
            <div class="col-auto text-center" style="width: 40px;">刪除</div>
        </div>

        <div class="sortabled col-12">
            @foreach ($dataList as $key => $data)
            <div class="d-flex col-12 mb-3 align-items-sm-stretch sortabled_box">
                <div class="input-group col">
                    <span class="input-group-text" style="width: 50px;">{{ $key + 1 }}</span>
                    <input type="hidden" name="banner_id[]" value="{{$data->id}}">
                    <span class="form-control" style="align-items: center;display: flex">{{ $data->title }}</span>
                </div>

                <!-- 預覽圖 -->
                <div class="input-group col-auto ms-1" @if(false == isset($data->img_pc)) hidden @endif>
                    <img style="max-width:100px" src="@if(true == isset($data->img_pc)) {{asset($data->img_pc)}} @endif" />
                </div>
                <span class="col-auto text-center fs-5 align-self-center" style="width: 40px;">
                    @if ($data->is_public == '1')
                        <i class="bi bi-eye-fill fs-5"></i>
                    @else
                        <i class="bi bi-eye-slash text-secondary fs-5"></i>
                    @endif
                </span>
                @can('cms.homepage.edit')
                <a href="{{ Route('cms.homepage.banner.edit', ['id' => $data->id], true) }}"
                    data-bs-toggle="tooltip" title="編輯"
                    class="icon -edit icon-btn col-auto fs-5 text-primary rounded-circle border-0 p-0">
                    <i class="bi bi-pencil-square"></i>
                </a>
                <span class="icon -move icon-btn col-auto fs-5 text-primary rounded-circle border-0 p-0"
                    data-bs-toggle="tooltip" title="拖曳排序">
                    <i class="bi bi-arrows-move"></i>
                </span>
                <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#confirm-delete"
                    data-href="{{ Route('cms.homepage.banner.delete', ['id' => $data->id], true) }}"
                    class="icon -del icon-btn col-auto fs-5 text-danger rounded-circle border-0 p-0">
                    <i class="bi bi-trash"></i>
                </a>
                @endcan
            </div>
            @endforeach
        </div>
    </div>

    <div>
        <div class="col-auto">
            @can('cms.homepage.edit')
            <button type="submit" class="btn btn-success"
                @if(!isset($dataList) || 0 >= count($dataList)) disabled @endif
            >儲存排序</button>
            @endcan
        </div>
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
            // 綁定拖曳功能
            bindSortableMove($('.sortabled'), {
                axis: 'y',
                placeholder: 'placeholder-highlight mb-3',
            });

            // 刪除 btn
            $('#confirm-delete').on('show.bs.modal', function (e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
        </script>
    @endpush
@endonce
