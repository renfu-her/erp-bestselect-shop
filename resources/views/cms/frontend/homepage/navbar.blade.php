@extends('layouts.main')
@section('sub-content')
<h2 class="mb-3">首頁設定</h2>
<x-b-home-navi></x-b-home-navi>

<form method="POST" action="">
    <div class="card shadow p-4 mb-4">
        <!-- Logo -->
        <h6>Logo 圖片</h6>
        <div class="upload_image_block">
            <label for="logo_img">
                <!-- 按鈕 -->
                <span class="browser_box -plusBtn">
                    <i class="bi bi-plus-circle text-secondary fs-4"></i>
                </span>
                <!-- 預覽圖 -->
                <span class="browser_box box" hidden>
                    <span class="icon -x"><i class="bi bi-x"></i></span>
                    <img src="" />
                </span>
                <!-- 進度條 -->
                <div class="progress" hidden>
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                        aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width: 1%"></div>
                </div>
                <input type="file" id="logo_img" name="logo" accept=".jpg,.jpeg,.png,.gif" hidden>
            </label>
        </div>
        <p><mark>圖片尺寸建議：165×50px，可上傳JPG/ JPEG/ PNG/ GIF格式</mark></p>
        <!-- Logo end -->

        <!-- 關鍵字 -->
        <h6>熱門關鍵字</h6>
        <fieldset class="col-12 col-lg-6 mb-3">
            <div class="px-1 pt-1">
                <div class="form-check form-check-inline">
                    <label class="form-check-label">
                        <input class="form-check-input" name="radio1" value="1" type="radio" >
                        開啟
                    </label>
                </div>
                <div class="form-check form-check-inline">
                    <label class="form-check-label">
                        <input class="form-check-input" name="radio1" value="0" type="radio" checked>
                        關閉
                    </label>
                </div>
                @error('radio1')
                    <div class="invalid-feedback"></div>
                @enderror
            </div>
        </fieldset>
        <div id="keywords" class="sortabled col-12 col-lg-6">
            <div class="col-12 d-flex mb-2 sortabled_box">
                <input type="text" class="form-control col" name="keyword[]" maxlength="8" disabled placeholder="請輸入關鍵字" aria-label="關鍵字">
                <span type="button" class="icon -move icon-btn fs-5 text-primary rounded-circle border-0 p-0 col-auto">
                    <i class="bi bi-arrows-move"></i>
                </span>
            </div>
            <div class="col-12 d-flex mb-2 sortabled_box">
                <input type="text" class="form-control col" name="keyword[]" maxlength="8" disabled placeholder="請輸入關鍵字" aria-label="關鍵字">
                <span type="button" class="icon -move icon-btn fs-5 text-primary rounded-circle border-0 p-0 col-auto">
                    <i class="bi bi-arrows-move"></i>
                </span>
            </div>
            <div class="col-12 d-flex mb-2 sortabled_box">
                <input type="text" class="form-control col" name="keyword[]" maxlength="8" disabled placeholder="請輸入關鍵字" aria-label="關鍵字">
                <span type="button" class="icon -move icon-btn fs-5 text-primary rounded-circle border-0 p-0 col-auto">
                    <i class="bi bi-arrows-move"></i>
                </span>
            </div>
            <div class="col-12 d-flex mb-2 sortabled_box">
                <input type="text" class="form-control col" name="keyword[]" maxlength="8" disabled placeholder="請輸入關鍵字" aria-label="關鍵字">
                <span type="button" class="icon -move icon-btn fs-5 text-primary rounded-circle border-0 p-0 col-auto">
                    <i class="bi bi-arrows-move"></i>
                </span>
            </div>
            <div class="col-12 d-flex mb-2 sortabled_box">
                <input type="text" class="form-control col" name="keyword[]" maxlength="8" disabled placeholder="請輸入關鍵字" aria-label="關鍵字">
                <span type="button" class="icon -move icon-btn fs-5 text-primary rounded-circle border-0 p-0 col-auto">
                    <i class="bi bi-arrows-move"></i>
                </span>
            </div>
            <div class="col-12 d-flex mb-2 sortabled_box">
                <input type="text" class="form-control col" name="keyword[]" maxlength="8" disabled placeholder="請輸入關鍵字" aria-label="關鍵字">
                <span type="button" class="icon -move icon-btn fs-5 text-primary rounded-circle border-0 p-0 col-auto">
                    <i class="bi bi-arrows-move"></i>
                </span>
            </div>
            <div class="col-12 d-flex mb-2 sortabled_box">
                <input type="text" class="form-control col" name="keyword[]" maxlength="8" disabled placeholder="請輸入關鍵字" aria-label="關鍵字">
                <span type="button" class="icon -move icon-btn fs-5 text-primary rounded-circle border-0 p-0 col-auto">
                    <i class="bi bi-arrows-move"></i>
                </span>
            </div>
            <div class="col-12 d-flex mb-2 sortabled_box">
                <input type="text" class="form-control col" name="keyword[]" maxlength="8" disabled placeholder="請輸入關鍵字" aria-label="關鍵字">
                <span type="button" class="icon -move icon-btn fs-5 text-primary rounded-circle border-0 p-0 col-auto">
                    <i class="bi bi-arrows-move"></i>
                </span>
            </div>
        </div>
        <!-- 關鍵字 end -->
    </div>
    <div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">套用發佈</button>
        </div>
    </div>
</form>
@endsection
@once
    @push('sub-scripts')
        <script>
            // -- Logo -------------
            bindReadImageFile($('#logo_img'), {
                num: 'single',
                fileInputName: 'logo',
                delFn: function ($that) {
                    $that.siblings('img').attr('src', '');
                    let img_box = $that.closest('.box');
                    img_box.prop('hidden', true);
                    img_box.siblings('.browser_box.-plusBtn').prop('hidden', false)
                    img_box.siblings('input[name="logo"]').val('');
                }
            });

            // -- 顯示字數 -------------
            showWordsLength($('input[maxlength]'));

            // -- 熱門關鍵字 -------------
            $('input[name="radio1"]').off('change')
            .on('change', function () {
                var sw = $('input[name="radio1"]:checked').val();
                $('#keywords.sortabled input[name="keyword[]"]').prop('disabled', sw == '0');
            });

            // 綁定拖曳功能
            bindSortableMove($('#keywords.sortabled'), {
                axis: 'y',
                placeholder: 'placeholder-highlight mb-2',
            });
            // -- 熱門關鍵字 end ---------
        </script>
    @endpush
@endonce