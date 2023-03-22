@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">
        商品群組</h2>

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
                <thead class="align-middle">
                    <tr>
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col">商品群組</th>
                        <th scope="col">主圖</th>
                        <th scope="col" class="text-center">公開</th>
                        <th scope="col" class="text-center">EDM</th>
                        <th scope="col" class="text-center">酒類</th>
                        <th scope="col" class="text-center lh-1 small">複製<br>連結</th>
                        <th scope="col" class="text-center">編輯</th>
                        <th scope="col" class="text-center">刪除</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($dataList as $key => $data)
                    <tr>
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $data->name }}</td>
                        <td class="text-center text-secondary">
                            @if (isset($data->img))
                                <a href="{{asset($data->img)}}" target="_blank">
                                    <img style="max-width:100px;height:100%;" src="{{asset($data->img)}}" />
                                </a>
                            @else
                                -
                            @endif
                        </td>

                        <td class="text-center">
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" name="is_public[]" value="{{ $data->is_public }}"
                                       type="checkbox" @if ($data->is_public) checked @endif
                                       @cannot('cms.collection.edit') disabled @endcannot>
                                <input type="hidden" name="id[]" value="{{ $data->id }}">
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" name="edm[]" value="{{ $data->edm }}" cid="{{ $data->id }}"
                                       type="checkbox" @if ($data->edm) checked @endif
                                       @cannot('cms.collection.edit') disabled @endcannot>
                            </div>
                        </td>

                        <td class="text-center">
                            @if ($data->is_liquor == 1)
                                <i class="bi bi-check-lg text-success fs-5"></i>
                            @else
                                <i class="bi bi-x-lg text-danger fs-6"></i>
                            @endif
                        </td>
                        <td class="text-center">
                            <button type="button" data-bs-toggle="tooltip" title="複製"
                                    data-url="{{ \App\Models\Collection::getCollectionFullPath($data->id, $data->is_liquor, $data->url) }}"
                                    class="icon -copy icon-btn fs-5 text-primary rounded-circle border-0">
                                <i class="bi bi-clipboard2-check"></i>
                            </button>
                        </td>
                        <td class="text-center">
                            @can('cms.collection.edit')
                                <a href="{{ Route('cms.collection.edit', ['id' => $data->id], true) }}"
                                   data-bs-toggle="tooltip" title="編輯"
                                   class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan
                        </td>
                        <td class="text-center">
                            @can('cms.collection.delete')
                                <a href="javascript:void(0)"
                                   data-href="{{ Route('cms.collection.delete', ['id' => $data->id], true) }}"
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

    @if ($dataList->hasPages())
        <div class="row flex-column-reverse flex-sm-row mb-4">
            <div class="col d-flex justify-content-end align-items-center">
                {{-- 頁碼 --}}
                <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
            </div>
        </div>
    @endif

    {{-- 首頁推薦群組 --}}
    <form action="{{ route('cms.collection.set-erp-top') }}" method="post">
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>【總覽】推薦商品群組設定</h6>
            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label">請選擇最多4項群組</label>
                    <select name="top_id[]" multiple class="-select2 -multiple form-select"
                            data-maximum-selection-length="4">
                        @foreach ($topList as $key => $data)
                            <option value="{{ $data->id }}" @if ($data->erp_top == '1') selected @endif>
                                {{ $data->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col">
                @can('cms.collection.edit')
                    <button type="submit" class="btn btn-primary px-4">儲存</button>
                @endcan
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
                let DATA = {
                    id: collectionId
                };

                const ON = '1';
                const OFF = '0';

                axios.post(_URL, DATA).then((result) => {
                    if (currentStatus === ON) {
                        $(this).val(OFF);
                        toast.show('群組已下架', {
                            type: 'warning'
                        });
                    } else if (currentStatus === OFF) {
                        $(this).val(ON);
                        toast.show('群組已公開');
                    }
                }).catch((error) => {
                    console.log('post error:' + error);
                    toast.show('發生錯誤', {
                        type: 'danger'
                    });
                });
            });

            $('tbody').on('change', 'input[name="edm[]"]', function () {
           
                let currentStatus = $(this).val();
                let collectionId = $(this).attr('cid');
                let _URL = '/cms/collection/set-edm/' + collectionId;
                let DATA = {
                    id: collectionId
                };

                const ON = '1';
                const OFF = '0';

                axios.post(_URL, DATA).then((result) => {
                    if (currentStatus === ON) {
                        $(this).val(OFF);
                        toast.show('取消EDM', {
                            type: 'warning'
                        });
                    } else if (currentStatus === OFF) {
                        $(this).val(ON);
                        toast.show('公開EDM');
                    }
                }).catch((error) => {
                    console.log('post error:' + error);
                    toast.show('發生錯誤', {
                        type: 'danger'
                    });
                });
            });

            //複製群組連結
            $('button.-copy').off('click').on('click', function() {
                const copy_url = $(this).data('url');
                copyToClipboard(copy_url, '已複製群組連結至剪貼簿', `請手動複製連結：<br>${copy_url}`);
            });
        </script>
    @endpush
@endOnce
