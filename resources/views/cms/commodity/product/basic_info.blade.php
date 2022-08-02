@extends('layouts.main')
@section('sub-content')
    <div>
        <h2 class="mb-3">
            @if ($method == 'create')
                新增商品
            @else
                {{ $product->title }}
            @endif
        </h2>
        @if ($method === 'edit')
            <x-b-prd-navi :product="$product"></x-b-prd-navi>
        @endif
    </div>

    <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" novalidate>
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>基本設定</h6>
            <fieldset class="col-12 mb-3">
                <div class="px-1 pt-1">
                    <div class="form-check form-check-inline @error('type') is-invalid @enderror">
                        <label class="form-check-label">
                            <input class="form-check-input @error('type') is-invalid @enderror" name="type" value="p"
                                type="radio" @if ($method == 'edit') disabled @endif required
                                @if (old('type', $product->type ?? 'p') == 'p') checked @endif>
                            一般商品
                        </label>
                    </div>
                    <div class="form-check form-check-inline @error('type') is-invalid @enderror">
                        <label class="form-check-label" for="type_2">
                            <input class="form-check-input @error('type') is-invalid @enderror" name="type" value="c"
                                type="radio" @if ($method == 'edit') disabled @endif id="type_2" required
                                @if (old('type', $product->type ?? '') == 'c') checked @endif>
                            組合包商品
                        </label>
                    </div>
                    <div class="form-check-inline ms-3 ps-3 border-start border-3 border-secondary">
                        <div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input" value="1" name="consume" type="checkbox"
                                       @if (old('consume', $product->consume ?? 0)) checked @endif
                                       @if (old('type', $product->type ?? '') == 'c') disabled @endif>
                                屬於耗材 <span class="text-danger">*</span>
                            </label>
                            <i class="bi bi-question-circle" data-bs-toggle="tooltip" title="若屬於耗材請打勾"></i>
                        </div>
                    </div>

                </div>
                @error('type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </fieldset>
            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label">商品名稱（發佈後若有更改網址可能會影響SEO搜尋）<span class="text-danger">*</span></label>
                    <input class="form-control @error('title') is-invalid @enderror" name="title" type="text"
                        placeholder="例：女休閒短T" maxlength="60" value="{{ old('title', $product->title ?? '') }}"
                        aria-label="商品名稱" required />
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- <div class="col-12 mb-3">
                    <label class="form-label">商品網址</label>
                    <div class="input-group has-validation">
                        <span class="input-group-text">https://demo.bestselection.com.tw/products/</span>
                        <input type="text" name="url" class="form-control @error('url') is-invalid @enderror"
                            placeholder="請輸入連結路徑" value="{{ old('url', $product->url ?? '') }}" aria-label="商品網址">
                        <div class="invalid-feedback">
                            @error('url')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div> --}}

                <div class="col-12 mb-3">
                    <label class="form-label">商品簡述</label>
                    <textarea rows="3" name="feature" class="form-control" maxlength="150" placeholder="請輸入關於產品的描述"
                        aria-label="商品簡述">{{ old('feature', $product->feature ?? '') }}</textarea>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">商品標語</label>
                    <input class="form-control" value="{{ old('slogan', $product->slogan ?? '') }}" name="slogan"
                        type="text" placeholder="請輸入商品標語" aria-label="商品標語">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">商品歸類 <span class="text-danger">*</span></label>
                    <select class="form-select @error('category_id') is-invalid @enderror" required aria-label="Select"
                        name="category_id">
                        <option value="" disabled selected>請選擇商品歸類</option>
                        @foreach ($categorys as $key => $category)
                            <option value="{{ $category->id }}" @if (old('category_id', $product->category_id ?? '') == $category->id) selected @endif>
                                {{ $category->category }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">負責人員 <span class="text-danger">*</span></label>
                    <select class="form-select @error('user_id') is-invalid @enderror" required aria-label="Select"
                        name="user_id">
                        <option value="" disabled selected>請選擇負責人員</option>
                        @foreach ($users as $key => $user)
                            <option value="{{ $user->id }}" @if (old('user_id', $product->user_id ?? $current_user) == $user->id) selected @endif>
                                {{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label" for="supplier">廠商 <span class="text-danger">*</span>(有設定才可採購)</label>
                    <select name="supplier[]" id="supplier" multiple hidden
                        class="-select2 -multiple form-select @error('supplier') is-invalid @enderror"
                        data-placeholder="請選擇廠商" required>
                        @foreach ($suppliers as $key => $supplier)
                            <option value="{{ $supplier->id }}" @if (in_array($supplier->id, old('supplier', $current_supplier ?? []))) selected @endif>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">上架時間</label>
                    <div class="input-group has-validation">
                        <input class="form-control @error('active_sdate') is-invalid @enderror" name="active_sdate"
                            type="date" aria-label="上架時間"
                            value="{{ old('active_sdate', $product->active_sdate ?? '') }}">
                        <button class="btn btn-outline-secondary icon" type="button" data-clear data-bs-toggle="tooltip"
                            title="清空日期"><i class="bi bi-calendar-x"></i>
                        </button>
                        <div class="invalid-feedback">
                            @error('active_sdate')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">下架時間</label>
                    <div class="input-group has-validation">
                        <input class="form-control @error('active_edate') is-invalid @enderror" name="active_edate"
                            type="date" aria-label="下架時間"
                            value="{{ old('active_edate', $product->active_edate ?? '') }}">
                        <button class="btn btn-outline-secondary icon" type="button" data-clear data-bs-toggle="tooltip"
                            title="清空日期"><i class="bi bi-calendar-x"></i>
                        </button>
                        <div class="invalid-feedback">
                            @error('active_edate')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
                <fieldset class="col-12 col-lg-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">公開 <span class="text-danger">*</span></legend>
                    <div class="form-check form-switch form-switch-lg">
                        <input class="form-check-input" type="checkbox" name="public" value="1"
                            @if (old('public', $product->public ?? 1)) checked @endif>
                    </div>
                </fieldset>
                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">開放通路 <span class="text-danger">*</span></legend>
                    <div class="px-1 pt-1">
                        {{-- @foreach ($publics as $key => $public) --}}
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="online" type="checkbox" value="1"
                                    @if (old('online', $product->online ?? 1)) checked @endif>
                                線上 (對外網站)
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="offline" type="checkbox" value="1"
                                    @if (old('offline', $product->offline ?? 1)) checked @endif>
                                線下 (ERP)
                            </label>
                        </div>
                        {{-- @endforeach --}}
                    </div>
                </fieldset>
                <fieldset class="col-12 col-lg-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">應稅免稅 <span class="text-danger">*</span></legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline @error('has_tax') is-invalid @enderror">
                            <input class="form-check-input @error('has_tax') is-invalid @enderror" name="has_tax" value="1"
                                type="radio" id="tax_1" required @if (old('has_tax', $product->has_tax ?? '1') == '1') checked @endif>
                            <label class="form-check-label" for="tax_1">應稅</label>
                        </div>
                        <div class="form-check form-check-inline @error('has_tax') is-invalid @enderror">
                            <input class="form-check-input @error('has_tax') is-invalid @enderror" name="has_tax" value="0"
                                type="radio" id="tax_2" required @if (old('has_tax', $product->has_tax ?? '') == '0') checked @endif>
                            <label class="form-check-label" for="tax_2">免稅（農林漁牧產品/免稅）</label>
                        </div>
                        @error('has_tax')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </fieldset>
            </div>

        </div>

        <div id="mediaSettings" class="card shadow p-4 mb-4">
            <h6>媒體設定</h6>
            <label class="form-label">商品圖片（可將檔案拖拉至框中上傳）</label>
            <div class="upload_image_block -multiple">
                <!-- 可排序圖片集中區塊 -->
                <div class="sortabled">
                    <!-- 新增圖Box -->
                    <div class="sortabled_box" hidden>
                        <!-- /* 預覽圖 */ -->
                        <span class="browser_box box">
                            <span class="icon -move" hidden><i class="bi bi-arrows-move"></i></span>
                            <span class="icon -x"><i class="bi bi-x"></i></span>
                            <img src="" />
                        </span>
                        <!-- /* 進度條 */ -->
                        <div class="progress" hidden>
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width: 1%"></div>
                        </div>
                        <input type="file" name="files[]" accept=".jpg,.jpeg,.png,.gif" multiple hidden>
                    </div>
                    <!-- 新增圖Box end -->

                    {{-- 舊增圖Box放這裡；sortabled_box 拿掉 hidden，不用input[type="file"] --}}
                    @foreach ($images as $key => $image)
                        <div class="sortabled_box" data-id="{{ $image->id }}">
                            <!-- /* 預覽圖 */ --
                            <span class="browser_box box">
                                <span class="icon -move" hidden><i class="bi bi-arrows-move"></i></span>
                                <span class="icon -x"><i class="bi bi-x"></i></span>
                                @if(\Illuminate\Support\Facades\App::environment(\App\Enums\Globals\AppEnvClass::Release) ||
                                    \Illuminate\Support\Facades\App::environment(\App\Enums\Globals\AppEnvClass::Development)
                                    )
                                    <img src="{{ \App\Enums\Globals\ImageDomain::CDN . $image->url }}" />
                                @else
                                    <img src="{{ asset($image->url) }}" />
                                @endif
                            </span>
                            <!-- /* 進度條 */ -->
                            <div class="progress" hidden>
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                    aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width: 1%"></div>
                            </div>
                        </div>
                    @endforeach
                    <!-- 按鈕 -->
                    <label for="product_img_add">
                        <span class="browser_box -plusBtn">
                            <i class="bi bi-plus-circle text-secondary fs-4"></i>
                        </span>
                        <input type="file" id="product_img_add" accept=".jpg,.jpeg,.png,.gif" multiple hidden>
                    </label>
                </div>
            </div>
            <p><mark>圖片限制：不超過1MB，1000×1000px，可上傳JPG/ JPEG/ PNG/ GIF格式</mark></p>
            <input type="hidden" name="del_image">
            @error('files')
                <div class="alert alert-danger" role="alert">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
                <a href="{{ Route('cms.product.index') }}" class="btn btn-outline-primary px-4" role="button">返回列表</a>
            </div>
        </div>

    </form>
@endsection

@once
    @push('sub-styles')
        <style>
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            // 顯示字數
            showWordsLength($('input[maxlength],textarea[maxlength]'));
        </script>
        <script>
            /*** 媒體設定 ***/
            let del_image = [];
            $('#mediaSettings .upload_image_block > .sortabled > .sortabled_box[hidden]').remove();

            // 綁定事件 init
            let moveOpt = {
                update: function() {
                    $('.upload_image_block .sortabled > label').appendTo('.upload_image_block .sortabled');
                }
            };

            function delImage($x) {
                const id = $x.closest('.sortabled_box').attr('data-id');
                console.log(id);
                if (id) {
                    del_image.push(id);
                    $('input[name="del_image"]').val(del_image.toString());
                }
                $x.closest('.sortabled_box').remove();
            }

            bindReadImageFile($('#mediaSettings .upload_image_block label #product_img_add'), {
                num: 'multiple',
                fileInputName: 'files[]',
                delFn: delImage,
                movable: false, // 暫時無法排序
                moveOpt: moveOpt
            });
            bindSortableMove($('#mediaSettings .upload_image_block > .sortabled'), moveOpt);

            //若為組合包商品 不可勾選屬於耗材
            $( 'input[name="type"]' ).change(function() {
                let bool = $( 'input[name="type"]' ).prop("checked");
                if (false == bool) {
                    $( 'input[name="consume"]' ).prop('checked', false).prop('disabled', true);
                } else {
                    $( 'input[name="consume"]' ).prop('disabled', false);
                }
            });
        </script>
    @endpush
@endOnce
