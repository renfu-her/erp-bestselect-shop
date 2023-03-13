@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">首頁設定</h2>
    <x-b-home-navi></x-b-home-navi>

    <form method="post" action="{{ $formAction }}" enctype="multipart/form-data">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <h6>
                @if ($method === 'create')
                    新增
                @else
                    編輯
                @endif 版型區塊
            </h6>

            <div class="row">
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">顯示版型區塊 <span class="text-danger">*</span></legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline @error('is_public')is-invalid @enderror">
                            <label class="form-check-label">
                                <input class="form-check-input @error('is_public')is-invalid @enderror" name="is_public"
                                    value="1" type="radio" required @if (old('is_public', $data->is_public ?? '1') == '1') checked @endif>
                                開啟
                            </label>
                        </div>
                        <div class="form-check form-check-inline @error('is_public')is-invalid @enderror">
                            <label class="form-check-label">
                                <input class="form-check-input @error('is_public')is-invalid @enderror" name="is_public"
                                    value="0" type="radio" required
                                    @if (old('is_public', $data->is_public ?? '') == '0') checked @endif>
                                關閉
                            </label>
                        </div>
                        @error('is_public')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </fieldset>
                <fieldset class="col-12 mb-3">
                    <legend class="col-form-label p-0 mb-2">選擇版型 <span class="text-danger">*</span></legend>
                    <div class="row">
                        @foreach (App\Enums\Homepage\TemplateStyleType::asArray() as $key => $val)
                            <div class="col-12 col-sm-6 col-xl-4 col-xxl-3 mb-3">
                                <label class="d-flex flex-wrap -template">
                                    <input type="radio" name="style_type" value="{{ $val }}"
                                        class="form-check-input" required @if ($val == old('style_type', $data->style_type ?? '')) checked @endif>
                                    {{ $description = App\Enums\Homepage\TemplateStyleType::getDescription($val) }}
                                    <div class="mb-1 p-0 col-12">
                                        <div class="me-2 -preview">
                                            <img src="{{ Asset(App\Enums\Homepage\TemplateStyleType::getAsset($val)) }}"
                                                alt="{{ $description }}">
                                            <div class="mask">
                                                <i class="bi-check-circle-fill"></i>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">大標題 <span class="text-danger">*</span></label>
                    <input class="form-control" name="title" value="{{ old('title', $data->title ?? '') }}" type="text"
                        placeholder="請輸入大標題" aria-label="大標題" maxlength="12" required>
                </div>
                {{-- t1, t2 --}}
                <div class="col-12 col-sm-6 mb-3">
                    <div class="event_type">
                        <label class="form-label">商品群組<span class="text-danger">*</span></label>
                        <select name="group_id" class="form-select" required>
                            <option value="" @if ('' == old('group_id', $data->group_id ?? '')) selected @endif disabled>請選擇</option>
                            @foreach ($collectionList as $key => $collection)
                                <option value="{{ $collection->id }}" @if ($collection->id == old('group_id', $data->group_id ?? '')) selected @endif>
                                    {{ $collection->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                {{-- t3 --}}
                <div class="col-12">
                    <label class="form-label">圖片-群組（最多3個） <span class="text-danger">*</span></label>
                    @php
                        if ($method == 'create') {
                            $child = [[], [], []];
                        }
                    @endphp
                    @foreach ($child as $key => $value)
                        @php
                            $old_group_id = old('group_id' . $key, $value->group_id ?? '');
                        @endphp
                        @if ($method == 'edit')
                            @php
                                $cid = isset($value->id) ? $value->id : 0;
                            @endphp
                            <input type="hidden" name="id{{ $key }}" value="{{ $cid }}">
                        @endif
                        <div class="row pb-1 mb-2 border-bottom">
                            <div class="col-12 col-sm-6 mb-1">
                                <input class="form-control" name="file{{ $key }}" value="" type="file"
                                    placeholder="" aria-label="">
                            </div>
                            <div class="col-12 col-sm-6 mb-1">
                                <select name="group_id{{ $key }}" class="form-select">
                                    <option value="" @if ('' == $old_group_id) selected @endif disabled>請選擇
                                    </option>
                                    @foreach ($collectionList as $key => $collection)
                                        <option value="{{ $collection->id }}"
                                            @if ($collection->id == $old_group_id) selected @endif>
                                            {{ $collection->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach
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
            .-template>div {
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

            .-preview i.bi-check-circle-fill {
                font-size: 50px;
                color: #484848;
                display: none;
            }

            /* 選擇版型 */
            .-template input:checked+div .-preview .mask {
                opacity: 1;
            }

            .-template input:checked+div .-preview i.bi-check-circle-fill {
                display: block;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            // -- 顯示字數 -------------
            showWordsLength($('input[maxlength]'));

            // -- 橫幅廣告類型 -------------
            $('input[name="event_type"]').on('change', function() {
                const val = $('input[name="event_type"]:checked').val();
                $(`div.event_type:not(.-${val})`).prop('hidden', true);
                $(`div.event_type:not(.-${val})`).children('select, input').prop({
                    'required': false,
                    'disabled': true
                });

                $(`div.event_type.-${val}`).prop('hidden', false);
                $(`div.event_type.-${val}`).children('select, input').prop({
                    'required': true,
                    'disabled': false
                });
            });
        </script>
    @endpush
@endonce
