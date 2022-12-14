@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">
        一頁式購物</h2>

    <form id="search" action="" method="GET">
        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label">搜尋條件</label>
                    <input class="form-control" name="title" type="text" placeholder="請輸入名稱" value=""
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
                @can('cms.onepage.create')
                    <a href="{{ Route('cms.onepage.create', null, true) }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> 新增一頁式
                    </a>
                @endcan
            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                <tr>
                    <th scope="col" style="width:10%">#</th>
                    <th scope="col">名稱</th>
                    <th scope="col">商品群組</th>
                    <th scope="col">銷售通路</th>
                    <th scope="col" class="text-center">啟用</th>
                    <th scope="col" class="text-center">線上付款</th>
                    <th scope="col" class="text-center">編輯</th>
                    <th scope="col" class="text-center">刪除</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($dataList as $key => $data)
                    <tr>
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $data->title }}</td>
                        <td>{{ $data->collection_title }}</td>
                        <td>{{ $data->salechannel_title }}</td>
                        <td class="text-center">
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" name="active[]" value="{{ $data->active }}"
                                       type="checkbox" @if ($data->active) checked @endif
                                       @cannot('cms.onepage.edit') disabled @endcannot>
                                <input type="hidden" name="id[]" value="{{ $data->id }}">
                            </div>
                        </td>
                       

                        <td class="text-center">
                            @if ($data->online_pay == 1)
                                <i class="bi bi-check-lg text-success fs-5"></i>
                            @else
                                <i class="bi bi-x-lg text-danger fs-6"></i>
                            @endif
                        </td>
                      
                        <td class="text-center">
                            @can('cms.onepage.edit')
                                <a href="{{ Route('cms.onepage.edit', ['id' => $data->id], true) }}"
                                   data-bs-toggle="tooltip" title="編輯"
                                   class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan
                        </td>
                        <td class="text-center">
                            @can('cms.onepage.delete')
                                <a href="javascript:void(0)"
                                   data-href="{{ Route('cms.onepage.delete', ['id' => $data->id], true) }}"
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

            $('tbody').on('change', 'input[name="active[]"]', function () {
                let currentStatus = $(this).val();
                let onepageId = $(this).next().val();
                console.log(onepageId);
                let _URL = '/cms/onepage/active/' + onepageId;
                let DATA = {
                    id: onepageId
                };

                const ON = '1';
                const OFF = '0';

                axios.post(_URL, DATA).then((result) => {
                    if (currentStatus === ON) {
                        $(this).val(OFF);
                        toast.show('下架', {
                            type: 'warning'
                        });
                    } else if (currentStatus === OFF) {
                        $(this).val(ON);
                        toast.show('公開');
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
                let onepageId = $(this).attr('cid');
                let _URL = '/cms/onepage/set-edm/' + onepageId;
                let DATA = {
                    id: onepageId
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
