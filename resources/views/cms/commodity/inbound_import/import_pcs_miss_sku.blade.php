@extends('layouts.main')
@section('sub-content')

    <ul class="nav pm_navbar">
        <li class="nav-item">
            <a class="nav-link" aria-current="page" href="{{ Route('cms.inbound_import.index', [], true) }}">上傳檔案</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ Route('cms.inbound_import.import_log', [], true) }}">匯入紀錄</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ Route('cms.inbound_import.inbound_list', [], true) }}">入庫單列表</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ Route('cms.inbound_import.inbound_log', [], true) }}">入庫單調整紀錄</a>
        </li>
        @can('cms.inbound_fix0917_import.edit')
            <li class="nav-item">
                <a class="nav-link active" href="{{ Route('cms.inbound_import.import_pcs_miss_sku', [], true) }}">個別補採購入庫</a>
            </li>
        @endcan
    </ul>
    <hr class="narbarBottomLine mb-3">

    <div class="card shadow p-4 mb-4">
        <p> 1. 匯入時，只會依原有採購單進行匯入，若無此採購單或該單已有同樣商品款式，則不會匯入</p>
        <p> 2. 匯入前請先將同一採購單商品排好</p>
        <p> 3. 若無採購單則會回傳錯誤</p>
        <p> 4. 若檢查到其中一筆有問題，則該批採購單商品都不會匯入，只會回傳錯誤</p>

        <span class="text-danger">上傳時將造成伺服器停滯，待轉檔完畢後可至匯入紀錄查看結果。</span>


        <form method="POST" id="upload-excel" enctype="multipart/form-data"
              action="{{ Route('cms.inbound_import.upload_xls_pcs_miss_sku') }}">
            @csrf
            <div class="row mb-3">
                <div class="col-12 mb-3">
                    <label class="form-label">選擇倉庫 <span class="text-danger">* 請確認EXCEL倉庫於所選倉庫相同</span></label>
                    <select name="depot_id"
                            class="form-select @error('depot_id') is-invalid @enderror"
                            aria-label="請選擇倉庫" required>
                        <option value="" selected disabled>請選擇</option>
                        @foreach ($depotList as $depotItem)
                            <option value="{{ $depotItem['id'] }}">
                                {{ $depotItem['name'] }} {{ $depotItem['can_tally'] ? '(理貨倉)' : '(非理貨倉)' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">
                        @error('depot_id')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">匯入Excel（.xls, .xlsx）<span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
                        <input id="file_name" type="text" class="form-control @error('file') is-invalid @enderror"
                                style="background-color: #fff;" placeholder="請選擇匯入喜鴻採購庫存Excel表單" required readonly>
                        <input id="formFile" type="file" name="file" hidden required aria-label="匯入Excel"
                                accept="application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                        <label class="btn btn-success" for="formFile">選擇檔案</label>
                        <div class="invalid-feedback">
                            @error('file')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                @can('cms.inbound_import.edit')
                <div class="col-auto">
                    <button id="button1" class="btn btn-primary px-4" type="submit" disabled>上傳</button>
                </div>
                @endcan
            </div>
        </form>
        @if($errors->any())
            <div class="alert alert-danger mt-3">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
        @endif
    </div>

@endsection

@once
    @push('sub-scripts')
        <script>
            // 選擇Excel檔
            if (window.File && window.FileList && window.FileReader) {
                $('#formFile').off('change').on('change', function() {
                    const xlsFile = this.files[0];
                    // console.log('選擇Excel檔', xlsFile);
                    if (xlsFile &&
                        (xlsFile.type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ||
                            xlsFile.type === 'application/vnd.ms-excel')) {
                        $('#file_name').val(xlsFile.name);

                        // 啟動上傳按鈕
                        $('#button1').prop('disabled', false);
                        $('#file_name').removeClass('is-invalid');
                        $(this).siblings('.invalid-feedback').empty();
                    } else {
                        $('#file_name').val('');
                        // 停用上傳按鈕
                        $('#button1').prop('disabled', true);
                        if (xlsFile) {
                            $('#file_name').addClass('is-invalid');
                            $(this).siblings('.invalid-feedback').text('檔案格式不符');
                        }
                    }
                });
            } else {
                console.log('該瀏覽器不支援檔案上傳');
            }

            $('#upload-excel').submit(function (e) {
                $('#button1').prop('disabled', true).html(`
                    <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                    上傳中... 請稍後
                `);
            });
        </script>
    @endpush
@endonce
