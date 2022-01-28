@extends('layouts.main')
@section('sub-content')
    <h2 class="d-flex mb-4">選單列表設定(beta版)<a href="{{ route('cms.navinode.index') }}" style="display: inline-block;width:40px;height:40px"></a></h2>
    <form method="GET" action="">
        <div class="card shadow p-4 mb-4">
            <div>設定主分類和子分類，例如：女裝（主分類）>上衣（子分類）。
                <ul>
                    <li>[<i class="bi bi-arrows-move text-primary"></i>]符號：可以拖曳，拉到想要的主分類底下</li>
                    <li>[<i class="bi bi-arrow-right text-primary"></i>]符號：可使分類成為子分類</li>
                    <li>[<i class="bi bi-arrow-left text-primary"></i>]符號：可移出該分類</li>
                    <li>[<i class="bi bi-pencil-square text-primary"></i>]符號：進入選單內容設定群組或連結網址</li>
                </ul>
            </div>

            {{-- 編輯區 --}}
            <div class="-appendClone">
                <ul class="d-flex align-items-end flex-column level level_1"></ul>
            </div>

            {{-- -cloneElem --}}
            <div class="row mx-0 align-items-center oneItem -cloneElem d-none">
                <div class="form-control d-flex align-items-center col me-2 py-1">
                    <span class="icon -upLv icon-btn col-auto fs-3 text-primary p-0 rounded"
                        data-bs-toggle="tooltip" title="上階">
                        <i class="bi bi-arrow-left-short"></i>
                    </span>
                    <span class="icon -downLv icon-btn col-auto fs-3 text-primary p-0 rounded"
                        data-bs-toggle="tooltip" title="下階">
                        <i class="bi bi-arrow-right-short"></i>
                    </span>
                    <div class="ms-2 col fs-5 -title"></div>
                </div>
                <div class="row col-auto py-1">
                    <a href="#" 
                        data-bs-toggle="tooltip" title="編輯"
                        class="icon -edit icon-btn col-auto fs-5 text-primary rounded-circle border-0 p-0">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    <span class="icon -move icon-btn col-auto fs-5 text-primary rounded-circle border-0 p-0"
                        data-bs-toggle="tooltip" title="拖曳排序">
                        <i class="bi bi-arrows-move"></i>
                    </span>
                    <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#confirm-delete"
                        data-href="#"
                        class="icon -del icon-btn col-auto fs-5 text-danger rounded-circle border-0 p-0">
                        <i class="bi bi-trash"></i>
                    </a>
                </div>
            </div>

            {{-- 新增鈕 --}}
            <div class="d-grid gap-2">
                <button id="addNewItem" type="button" class="btn btn-outline-primary border-dashed" style="font-weight: 500;">
                    <i class="bi bi-plus-circle"></i> 新增
                </button>
            </div>
        </div>
        
        <div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4" @if (!isset($dataList) || 0 >= count($dataList)) disabled @endif>儲存排序</button>
            </div>
        </div>
    </form>

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
        ul.level, ul.level li {
            list-style: none;
            padding: 0;
        }
        .oneItem .-title {
            line-height: 1;
        }
        .oneItem .icon {
            width: 30px;
            height: 30px;
        }
        .oneItem input:focus {
            outline: none;
        }
        /* 間距 */
        ul.level {
            padding: .5rem 0;
            position: relative;
        }
        ul.level:empty {
            padding: .25rem 0;
        }
        ul.level_1 > li {
            margin-bottom: .25rem;
        }
        ul.level_2:empty,
        ul.level_3 > li:not(:last-child) {
            margin-bottom: .5rem;
        }
        /* 縮排 */
        .level:not(.level_1) > li {
            padding-left: 1rem;
            width: calc(100% - 1rem);
        }
        .level:not(.level_1) > li > div {
            position: relative;
        }
        .level:not(.level_1) > li > div.oneItem::before {
            content: "\f132";
            font-family: "bootstrap-icons";
            position: absolute;
            left: -1.4rem;
            line-height: 40px;
        }
        /* 拖曳預覽框 */
        .level > li.placeholder-highlight {
            height: 40px;
            width: calc(100% - 2rem);
            margin-bottom: .5rem;
        }
        .level.level_1 > li.placeholder-highlight {
            width: 100%;
            margin-bottom: .75rem;
        }
    </style>
    @endpush
    @push('sub-scripts')
    <script src="{{ Asset('dist/js/navinode.js') }}"></script>
    <script>
        const data = @json($dataList);
        console.log(data);
        // loadNaviNode(data, '.-appendClone');
        $('#addNewItem').on('click', function () {
            bindNewItemBtn('.-appendClone');
        });

        // 刪除 btn
        $('#confirm-delete').on('show.bs.modal', function(e) {
            $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
        });
    </script>
    @endpush
@endOnce
