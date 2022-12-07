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
        <div class="row justify-content-end mb-2">
            <div class="col">
                <label class="form-label">標頭底色</label>
                <select name="header_color" class="form-select form-select-sm">
                    <option value="b" style="background:#008BC6;color:#FFF;">藍</option>
                    <option value="r" style="background:#DB5741;color:#FFF;">紅</option>
                    <option value="g" style="background:#8EBC42;color:#FFF;">綠</option>
                    <option value="y" style="background:#F9C841;">黃</option>
                    <option value="p" style="background:#FDA7A9;">粉紅</option>
                </select>
            </div>
            <fieldset class="col">
                <legend class="col-form-label p-0 mb-2">顯示產品 QR code</legend>
                <div class="px-1">
                    <div class="form-check form-check-inline form-switch form-switch-lg">
                        <input name="qr_show" class="form-check-input" type="checkbox" value="1" checked>
                    </div>
                </div>
            </fieldset>
        </div>

        <div class="table-responsive tableOverBox">
            <div class="text-end small pe-2">
                <mark><i class="bi bi-exclamation-diamond-fill text-warning"></i> 網頁預覽列印僅支援 Chrome / Edge 瀏覽器</mark>
            </div>
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col">商品群組</th>
                        <th scope="col" class="text-center" style="width:20%">直客價EDM</th>
                        <th scope="col" class="text-center" style="width:20%">經銷價EDM</th>
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
                                <button  type="button" 
                                    data-href="{{ route('print-edm', ['id' => $data->id, 'type' => 'normal','mcode'=>$mcode]) }}"
                                    data-bs-toggle="tooltip" title="網頁預覽"
                                    class="-web icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-file-earmark-break-fill"></i>
                                </button>
                                <button type="button" 
                                    data-href="{{ route('print-edm', ['id' => $data->id, 'type' => 'normal','mcode'=>$mcode]) }}"
                                    data-bs-toggle="tooltip" title="圖片下載"
                                    class="-toImg icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-download"></i>
                                </button>
                            </td>
                            <td class="text-center">
                                <button  type="button" 
                                    data-href="{{ route('print-edm', ['id' => $data->id, 'type' => 'dealer','mcode'=>$mcode]) }}" 
                                    data-bs-toggle="tooltip" title="網頁預覽"
                                    class="-web icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-file-earmark-break-fill"></i>
                                </button>
                                <button type="button" 
                                    data-href="{{ route('print-edm', ['id' => $data->id, 'type' => 'dealer','mcode'=>$mcode]) }}"
                                    data-bs-toggle="tooltip" title="圖片下載"
                                    class="-toImg icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-download"></i>
                                </button>
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

    <x-b-modal id="loading" size="modal-dialog-centered" cancelBtn="false">
        <x-slot name="body">
            <p class="-title text-center">圖片生產中，請稍後...</p>
            <div class="progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" 
                    style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    0%
                </div>
            </div>
            <p class="-note text-secondary text-center my-2 small"></p>
        </x-slot>
        <x-slot name="foot">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
        </x-slot>
    </x-b-modal>
@endsection

@once
    @push('sub-scripts')
        <script src="{{ Asset('dist/js/screenshot.js') }}"></script>
        <script>
            const loading = new bootstrap.Modal(document.getElementById('loading'), { 
                backdrop: 'static',
                // keyboard: false
            });

            $('.-web').on('click', function(e) {
                const bg = $('select[name="header_color"]').val();
                const qr = $('input[name="qr_show"]').prop('checked') ? '1' : '0';
                const url = $(this).data('href') + `?bg=${bg}&qr=${qr}&paginate=0&btn=1&x=1`;
                window.open(url, '_blank');
            });
            $('.-toImg').on('click', function(e) {
                const bg = $('select[name="header_color"]').val();
                const qr = $('input[name="qr_show"]').prop('checked') ? '1' : '0';
                const url = $(this).data('href') + `?bg=${bg}&qr=${qr}&paginate=1&btn=0&x=2`;
                // window.open(url, '_blank');
                const $bar = $('#loading .progress-bar');
                const $title = $('#loading .-title');
                const $note = $('#loading .-note');
                const $footer = $('#loading .modal-footer');
                        // loading.show();
                        // return;
                console.log(url);
                Screenshot(url, {
                    pages: 2,
                    start: (e) => {
                        $bar.css('width', '0%').attr('aria-valuenow', 0).text('');
                        $title.text('圖片生產中，請稍後...');
                        $note.text('');
                        $footer.prop('hidden', true);
                        loading.show();
                    },
                    process: (data) => {
                        $bar.css('width', `${data.rate}%`).attr('aria-valuenow', data.rate).text(`${data.rate}%`);
                        $note.text(`（${data.task}/${data.totalTask}）${data.name}`);
                        if (data.rate === 100) {
                            $title.text('圖片生產完成：' + data.name);
                        }
                    },
                    error: (msg) => {
                        switch (msg) {
                            case 'closed':
                                $note.text('作業結束');
                                break;
                            case 'connecting':
                                $note.text('連線中...');
                                break;
                        
                            default:
                                console.error(msg);
                                break;
                        }
                        $footer.prop('hidden', false);
                    }
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
