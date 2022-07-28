@extends('layouts.main')
@section('sub-content')

    @if($errors->any())
        <div class="alert alert-danger mt-3">{!! implode('', $errors->all('<div>:message</div>')) !!}</div>
    @endif
    <div class="card shadow p-4 mb-4">
        <h6>上傳檔案</h6>
        <h8>重新匯入相同採購單號時，不會再次產生採購單和入庫單，請自行手動調整</h8>

        <form method="POST" id="upload-excel" enctype="multipart/form-data"
              action="{{ Route('cms.inbound_import.upload_excel') }}">
            @csrf
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label">選擇倉庫 <span class="text-danger">*</span></label>
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
                    <label class="form-label">匯入Excel（.xls, .xlsx）</label>
                    <div class="row">
                        <div class="input-group has-validation col pe-0">
                            <input id="file_name" type="text" class="form-control @error('file') is-invalid @enderror"
                                   style="background-color: #fff;" placeholder="請選擇匯入喜鴻採購庫存Excel表單" required readonly>
                            <input id="formFile" type="file" name="file" hidden required aria-label="匯入Excel"
                                   accept="application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                            <label class="btn btn-primary me-2" for="formFile">選擇檔案</label>
                            @error('file')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>
                        <div class="col-auto ps-1">
                            <button id="button1" class="btn btn-secondary me-2" type="submit" disabled>上傳</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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
                        $('#button1').prop('disabled', false).removeClass('btn-secondary').addClass('btn-success');
                    } else {
                        $('#file_name').val('');
                        // 停用上傳按鈕
                        $('#button1').prop('disabled', true).removeClass('btn-success').addClass('btn-secondary');
                        if (xlsFile) alert('檔案格式不符');
                    }
                });
            } else {
                console.log('該瀏覽器不支援檔案上傳');
            }
        </script>
    @endpush
@endonce
