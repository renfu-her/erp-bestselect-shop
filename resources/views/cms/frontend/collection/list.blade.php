@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">商品群組</h2>

    <form id="search" action="{{ Route('cms.collection.index') }}" method="GET">
        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label">搜尋條件</label>
                    <input class="form-control" name="name" type="text" placeholder="請輸入商品群組名稱" value=""
                           aria-label="商品群組名稱">
                </div>
            </div>

            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $data_per_page }}"/>
                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </div>
    </form>

    <div class="card shadow p-4 mb-4">
        <div class="row mb-4">
            <div class="col-auto">
                @can('cms.collection.create')
                    <a href="{{ Route('cms.collection.create', null, true) }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> 新增商品群組
                    </a>
                @endcan
            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                <tr>
                    <th scope="col" style="width:10%">#</th>
                    <th scope="col">商品群組</th>
                    @if(auth()->user()->can('cms/collection/publish'))
                        <th scope="col" class="text-center">公開上架</th>
                    @endif
                    <th scope="col" class="text-center">酒類</th>
                    <th scope="col" class="text-center">編輯</th>
                    <th scope="col" class="text-center">刪除</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($dataList as $key => $data)
                    <tr>
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $data->name }}</td>
                        @if(auth()->user()->can('cms/collection/publish'))
                            <td class="text-center">
                                <div class="form-check form-switch form-switch-lg">
                                    <input class="form-check-input" name="is_public[]" value="{{ $data->is_public }}"
                                           type="checkbox" @if ($data->is_public) checked @endif>
                                    <input type="hidden" name="id[]" value="{{ $data->id }}">
                                </div>
                            </td>
                        @endif
                        <td class="text-center">
                            @if ($data->is_liquor == 1)
                                <i class="bi bi-check-lg text-success fs-5"></i>
                            @else
                                <i class="bi bi-x-lg text-danger fs-6"></i>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ Route('cms.collection.edit', ['id' => $data->id], true) }}"
                               data-bs-toggle="tooltip" title="編輯"
                               class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        </td>
                        <td class="text-center">
                            <a href="javascript:void(0)"
                               data-href="{{ Route('cms.collection.delete', ['id' => $data->id], true) }}"
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
    
    @if ($dataList->hasPages())
        <div class="row flex-column-reverse flex-sm-row mb-4">
            <div class="col d-flex justify-content-end align-items-center">
                {{-- 頁碼 --}}
                <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
            </div>
        </div>
    @endif

    {{-- 首頁推薦群組 --}}
    <form action="" method="post">
        <div class="card shadow p-4 mb-4">
            <h6>【總覽】推薦商品群組設定</h6>
            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label">請選擇最多4項群組</label>
                    <select name="[]" multiple class="-select2 -multiple form-select" data-maximum-selection-length="4">
                        @foreach ($dataList as $key => $data)
                        <option value="{{ $data->id }}">{{ $data->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
            </div>
        </div>
    </form>

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
            $('#confirm-delete').on('show.bs.modal', function (e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });

            $('tbody').on('change', 'input[name="is_public[]"]', function () {
                let currentStatus = $(this).val();
                let collectionId = $(this).next().val();
                let _URL = '/cms/collection/publish/' + collectionId;
                let DATA = {id: collectionId};

                const ON = '1';
                const OFF = '0';

                axios.post(_URL, DATA).then((result) => {
                    if (currentStatus === ON) {
                        $(this).val(OFF);
                        toast.show('群組已下架', { type: 'warning' });
                    } else if (currentStatus === OFF) {
                        $(this).val(ON);
                        toast.show('群組已公開');
                    }
                }).catch((error) => {
                    console.log('post error:' + error);
                    toast.show('發生錯誤', { type: 'danger' });
                });
            });
        </script>
    @endpush
@endOnce
