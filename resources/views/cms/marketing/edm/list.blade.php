@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">EDM</h2>

    <form id="search" method="GET">
        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label">搜尋條件</label>
                    <input class="form-control" name="name" type="text" placeholder="請輸入商品群組名稱" value=""
                        aria-label="商品群組名稱">
                </div>
            </div>

            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $data_per_page }}" />
                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </div>
    </form>

    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col">商品群組</th>
                        <th scope="col" class="text-center" style="width:15%">直客價EDM</th>
                        <th scope="col" class="text-center" style="width:15%">經銷價EDM</th>
                        <th scope="col" class="text-center" style="width:15%">下載</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>
                                @can('cms.collection.edit')
                                    <a href="{{ route('cms.collection.edit', ['id' => $data->id], true) }}">
                                        {{ $data->name }}
                                    </a>
                                @else
                                    {{ $data->name }}
                                @endcan
                            </td>

                            <td class="text-center">
                                <a href="{{ route('cms.edm.print', ['id' => $data->id, 'type' => 'normal']) }}"
                                    data-bs-toggle="tooltip" title="直客價" target="_blank"
                                    class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-file-earmark-break"></i>
                                </a>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('cms.edm.print', ['id' => $data->id, 'type' => 'dealer']) }}" 
                                    data-bs-toggle="tooltip" title="經銷價" target="_blank"
                                    class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-file-earmark-break-fill"></i>
                                </a>
                            </td>
                            <td class="text-center">
                                <a href="https://dhtml2pdf.herokuapp.com/api.php?url={{ route('cms.edm.print', ['id' => $data->id, 'type' => 'normal']) }}&result_type=show"
                                    data-bs-toggle="tooltip" title="下載" target="_blank"
                                    class="icon -copy icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-download"></i>
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
@endsection

@once
    @push('sub-scripts')
        <script>
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });

            $('tbody').on('change', 'input[name="is_public[]"]', function() {
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
