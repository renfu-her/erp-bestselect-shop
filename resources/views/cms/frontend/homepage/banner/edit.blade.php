@extends('layouts.main')
@section('sub-content')
<h2 class="mb-3">首頁設定</h2>
<x-b-home-navi></x-b-home-navi>

<form method="post" action="{{ $formAction }}" enctype="multipart/form-data">
    @method('POST')
    @csrf

    <div class="card shadow p-4 mb-4">
        <h6>@if ($method === 'create') 新增 @else 編輯 @endif 橫幅廣告 Banner</h6>

        <div class="row">
            <fieldset class="col-12 col-sm-6 mb-3">
                <legend class="col-form-label p-0 mb-2">顯示橫幅廣告區塊 <span class="text-danger">*</span></legend>
                <div class="px-1 pt-1">
                    <div class="form-check form-check-inline @error('is_public')is-invalid @enderror">
                        <label class="form-check-label">
                            <input class="form-check-input @error('is_public')is-invalid @enderror" name="is_public"
                                value="0" type="radio" required
                                @if (old('is_public', $data->is_public ?? '1') == '1') checked @endif>
                            開啟
                        </label>
                    </div>
                    <div class="form-check form-check-inline @error('is_public')is-invalid @enderror">
                        <label class="form-check-label">
                            <input class="form-check-input @error('is_public')is-invalid @enderror" name="is_public"
                                value="1" type="radio" required
                                @if (old('is_public', $data->is_public ?? '') == '0') checked @endif>
                            關閉
                        </label>
                    </div>
                    @error('is_public')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </fieldset>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">橫幅廣告主標題 <span class="text-danger">*</span></label>
                <input class="form-control" value="{{ old('title', $data->title ?? '') }}" name="title"
                    type="text" placeholder="請輸入橫幅廣告主標題" aria-label="橫幅廣告主標題" maxlength="12">
            </div>
            <fieldset class="col-12 col-sm-6 mb-3">
                <legend class="col-form-label p-0 mb-2">橫幅廣告類型 <span class="text-danger">*</span></legend>
                <div class="px-1 pt-1">
                    <div class="form-check form-check-inline @error('event_type')is-invalid @enderror">
                        <label class="form-check-label">
                            <input class="form-check-input @error('event_type')is-invalid @enderror" name="event_type"
                                value="group" type="radio" required
                                @if (old('event_type', $data->event_type ?? '1') == '1') checked @endif>
                            群組
                        </label>
                    </div>
                    <div class="form-check form-check-inline @error('event_type')is-invalid @enderror">
                        <label class="form-check-label">
                            <input class="form-check-input @error('event_type')is-invalid @enderror" name="event_type"
                                value="url" type="radio" required
                                @if (old('event_type', $data->event_type ?? '') == '0') checked @endif>
                            連結
                        </label>
                    </div>
                    @error('event_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </fieldset>
            <div class="col-12 col-sm-6 mb-3">
                <div class="event_type -group">
                    <label class="form-label">橫幅廣告群組 <span class="text-danger">*</span></label>
                    {{-- @if ($event_type === 'group') <select> 加 required @else 加 disabled --}}
                    <select name="event_id" class="form-select" required>
                        <option value="" selected disabled>請選擇</option>
                        <option value="1">item 1</option>
                        <option value="2">item 2</option>
                        <option value="3">item 3</option>
                    </select>
                </div>
                <div class="event_type -url" hidden>
                    <label class="form-label">橫幅廣告連結 <span class="text-danger">*</span></label>
                    {{-- @if ($event_type === 'url') <input> 加 required @else 加 disabled --}}
                    <input type="url" name="event_url" class="form-control" placeholder="請輸入連結" disabled>
                </div>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label">橫幅廣告圖片（可將檔案拖拉至框中上傳）</label>
                <div class="upload_image_block">
                    <label>
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
                        <input type="file" name="img_pc" accept=".jpg,.jpeg,.png,.gif" hidden>
                    </label>
                </div>
                <p><mark>圖片尺寸建議：1200x400px，不超過300KB，可上傳JPG/ JPEG/ PNG/ GIF格式</mark></p>
                @error('files')
                <div class="alert alert-danger" role="alert">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="col-auto">
        <button type="submit" class="btn btn-primary px-4">儲存</button>
        <a href="{{ Route('cms.homepage.banner.index', [], true) }}" class="btn btn-outline-primary px-4"
            role="button">返回列表</a>
    </div>
</form>

@endsection
@once
    @push('sub-scripts')
        <script>
            // -- 顯示字數 -------------
            showWordsLength($('input[maxlength]'));

            // -- 橫幅廣告類型 -------------
            $('input[name="event_type"]').on('change', function () {
                const val = $('input[name="event_type"]:checked').val();
                $(`div.event_type:not(.-${val})`).prop('hidden', true);
                $(`div.event_type:not(.-${val})`).children('select, input').prop({
                    'required': false, 'disabled': true
                });

                $(`div.event_type.-${val}`).prop('hidden', false);
                $(`div.event_type.-${val}`).children('select, input').prop({
                    'required': true, 'disabled': false
                });
            });

            /*** 圖片 ***/
            bindReadImageFile($('input[name="img_pc"]'), {
                num: 'single',
                fileInputName: 'img_pc',
                maxSize: 300,
                delFn: function ($x) {
                    $x.siblings('img').attr('src', '');
                    let img_box = $x.closest('.box');
                    img_box.prop('hidden', true);
                    img_box.siblings('.browser_box.-plusBtn').prop('hidden', false)
                    img_box.siblings('input[name="img_pc"]').val('');
                }
            });

        </script>
    @endpush
@endonce
