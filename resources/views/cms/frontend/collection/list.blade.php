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
                        <th scope="col">公開上架</th>
                    @endif
                    <th scope="col">編輯</th>
                    <th scope="col">刪除</th>
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
                        <td>
                            <a href="{{ Route('cms.collection.edit', ['id' => $data->id], true) }}"
                               data-bs-toggle="tooltip" title="編輯"
                               class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        </td>
                        <td>
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
    <div class="row flex-column-reverse flex-sm-row">
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
            {{-- 頁碼 --}}
            <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
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
                    } else if (currentStatus === OFF) {
                        $(this).val(ON);
                    }
                }).catch((error) => {
                    console.log('post error:' + error);
                });
            });
        </script>
    @endpush
@endOnce
