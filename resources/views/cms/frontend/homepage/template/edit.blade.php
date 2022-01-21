@extends('layouts.main')
@section('sub-content')
<h2 class="mb-3">首頁設定</h2>
<x-b-home-navi></x-b-home-navi>

<form method="post" action="{{ $formAction }}" enctype="multipart/form-data">
    @method('POST')
    @csrf

    <div class="card shadow p-4 mb-4">
        <h6>@if ($method === 'create') 新增 @else 編輯 @endif 版型區塊</h6>

        <div class="row">
            <fieldset class="col-12 mb-3">
                <legend class="col-form-label p-0 mb-2">選擇版型 <span class="text-danger">*</span></legend>
                <div class="row">
                    <div class="col-12 col-sm-6 col-xl-4 mb-3">
                        <label class="d-flex flex-wrap -template">
                            <input type="radio" name="radio1" class="form-check-input" required>
                            樣式一（左右滑動）
                            <div class="mb-1 p-0 col-12">
                                <div class="me-2 -preview">
                                    <img src="{{ Asset('images/frontend/template_1.svg') }}" alt="樣式一（左右滑動）">
                                    <div class="mask">
                                        <i class="bi-check-circle-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                    <div class="col-12 col-sm-6 col-xl-4 mb-3">
                        <label class="d-flex flex-wrap -template">
                            <input type="radio" name="radio1" class="form-check-input" required>
                            樣式二（瀑布式）
                            <div class="mb-1 p-0 col-12">
                                <div class="me-2 -preview">
                                    <img src="{{ Asset('images/frontend/template_2.svg') }}" alt="樣式一（左右滑動）">
                                    <div class="mask">
                                        <i class="bi-check-circle-fill"></i>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            </fieldset>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">大標題 <span class="text-danger">*</span></label>
                <input class="form-control" value="" name=""
                    type="text" placeholder="請輸入大標題" aria-label="大標題" maxlength="12">
            </div>
            <div class="col-12 col-sm-6 mb-3">
                <div class="event_type">
                    <label class="form-label">商品群組 <span class="text-danger">*</span></label>
                    <select name="" class="form-select" required>
                        <option value="" selected disabled>請選擇</option>
                        <option value="1">item 1</option>
                        <option value="2">item 2</option>
                        <option value="3">item 3</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="col-auto">
        <button type="submit" class="btn btn-primary px-4">儲存</button>
        <a href="{{ Route('cms.homepage.template.index', [], true) }}" class="btn btn-outline-primary px-4"
            role="button">返回列表</a>
    </div>
</form>

@endsection
@once
    @push('sub-styles')
    <style>
        /* 版型預覽圖 */
        .-template > div {
            order: -1;
        }
        label.-template .form-check-input {
            margin-right: 8px;
        }
        .-preview {
            position: relative;
            display: block;
            /* max-width: 320px; */
        }
        .-preview img {
            width: 100%;
            height: auto;
        }
        .-preview .mask {
            position: absolute;
            width: 100%;
            height: 100%;
            background-color: rgba(153, 153, 153, 0.4);
            opacity: 0;
            top: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
        }
        .-preview .mask:hover {
            opacity: 1;
        }
        .-preview i.bi-check-circle-fill{
            font-size: 50px;
            color: #484848;
            display: none;
        }
        /* 選擇版型 */
        .-template input:checked + div .-preview .mask {
            opacity: 1;
        }
        .-template input:checked + div .-preview i.bi-check-circle-fill {
            display: block;
        }
    </style>
    @endpush
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
