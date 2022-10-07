@extends('layouts.main')
@section('sub-content')

    <ul class="nav pm_navbar">
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="{{ Route('cms.inbound_fix0917_import.index', [], true) }}">上傳檔案</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ Route('cms.inbound_fix0917_import.import_no_delivery', [], true) }}">0917前採購單尚未出貨</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ Route('cms.inbound_fix0917_import.import_has_delivery', [], true) }}">0917前採購單已出貨</a>
        </li>
    </ul>
    <hr class="narbarBottomLine mb-3">

    <div class="card shadow p-4 mb-4">
        <p> {{ $discription }}</p>

        <form method="POST" id="upload-excel" enctype="multipart/form-data"
              action="{{ $formAction }}">
            @csrf
            <div class="row mb-3">
                <div class="col-12 mb-3">
                    <label class="form-label">匯入Excel（.xls, .xlsx）<span class="text-danger">*</span></label>
                    <div class="input-group has-validation">
                        <input id="file_name" type="text" class="form-control @error('file') is-invalid @enderror"
                               style="background-color: #fff;" placeholder="請選擇Excel表單" required readonly>
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
