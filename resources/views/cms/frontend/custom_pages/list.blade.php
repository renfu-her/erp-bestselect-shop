@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">自訂頁面管理</h2>

    <div class="card shadow p-4 mb-4">
        <div class="row mb-4">
            <div class="col-auto">
                @can('cms.custom-pages.create')
                    <a href="{{ Route('cms.custom-pages.create', null, true) }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> 新增自訂頁面
                    </a>
                @endcan
            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col">頁面名稱</th>
                        <th scope="col">類型</th>
                        <th scope="col">最後更新日期</th>
                        <th scope="col">最後更新人員</th>
                        <th scope="col" class="text-center">複製連結</th>
                        <th scope="col" class="text-center">編輯</th>
                        <th scope="col" class="text-center">刪除</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>{{ $data->page_name }}</td>
                            <td>
                                {{ \App\Enums\FrontEnd\CustomPageType::getDescription($data->type) }}
                            </td>
                            <td>
                                {{ date('Y-m-d', strtotime($data->updated_at)) ?? '' }}
                            </td>
                            <td>{{ $data->user_name ?? '' }}</td>
                            <td class="text-center">
                                <button type="button" data-bs-toggle="tooltip" title="複製"
                                    data-url="{{ \App\Models\CustomPages::getFullUrlPath($data->url, $data->id) }}"
                                    class="icon -copy icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-clipboard2-check"></i>
                                </button>
                            </td>
                            <td class="text-center">
                                @can('cms.custom-pages.edit')
                                    <a href="{{ Route('cms.custom-pages.edit', ['id' => $data->id], true) }}"
                                        data-bs-toggle="tooltip" title="編輯"
                                        class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                @endcan
                            </td>
                            <td class="text-center">
                                @can('cms.custom-pages.delete')
                                    <a href="javascript:void(0)"
                                        data-href="{{ Route('cms.custom-pages.delete', ['id' => $data->id], true) }}"
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
            <div class="d-flex justify-content-center"></div>
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

            $('button.-copy').off('click').on('click', function() {
                const copy_url = $(this).data('url');
                if (navigator && navigator.clipboard) {
                    navigator.clipboard.writeText(copy_url)
                        .then(() => {
                            toast.show('已複製頁面連結至剪貼簿', {
                                type: 'success'
                            });
                        }).catch((err) => {
                            console.error('剪貼簿錯誤', err);
                            toast.show('請手動複製連結：<br>' + copy_url, {
                                title: '發生錯誤',
                                type: 'danger'
                            });
                        });
                } else {
                    toast.show('請手動複製連結：<br>' + copy_url, {
                        title: '不支援剪貼簿功能',
                        type: 'danger'
                    });
                }
            });
        </script>
    @endpush
@endOnce
