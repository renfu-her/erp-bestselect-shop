@extends('layouts.main')
@section('sub-content')
    <div>
        <h2 class="mb-3">@if ($method == 'create') 新增商品 @else {{ $data->title }} @endif </h2>
        @if ($method == 'edit')
            <x-b-prd-navi id="{{ $data->id }}"></x-b-prd-navi>
        @endif
    </div>
    <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>基本設定</h6>
            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label">商品名稱 <span class="text-danger">*</span></label>
                    <input class="form-control @error('title')is-invalid @enderror" name="title" type="text"
                        placeholder="例：女休閒短T" maxlength="30" value="{{ old('title', $data->title ?? '') }}"
                        aria-label="商品名稱" required />
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">商品網址（發佈後若有更改網址可能會影響SEO搜尋）</label>
                    <div class="input-group">
                        <span class="input-group-text">https://demo.bestselection.com.tw/products/</span>
                        <input type="text" name="url" class="form-control" placeholder="請輸入連結路徑"
                            value="{{ old('url', $data->url ?? '') }}" aria-label="商品網址">
                    </div>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">商品簡述</label>
                    <textarea rows="3" name="feature" class="form-control" maxlength="150" placeholder="請輸入關於產品的描述"
                        aria-label="商品簡述">{{ old('feature', $data->feature ?? '') }}</textarea>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">商品標語</label>
                    <input class="form-control" value="{{ old('slogan', $data->slogan ?? '') }}" name="slogan"
                        type="text" placeholder="請輸入商品標語" aria-label="商品標語">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">商品歸類 <span class="text-danger">*</span></label>
                    <select class="form-select @error('category_id')is-invalid @enderror" required aria-label="Select"
                        name="category_id">
                        <option value="" disabled selected>請選擇商品歸類</option>
                        @foreach ($categorys as $key => $category)
                            <option value="{{ $category->id }}" @if (old('user_id', $data->category_id ?? '') == $category->id) selected @endif>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">負責人員 <span class="text-danger">*</span></label>
                    <select class="form-select @error('user_id')is-invalid @enderror" required aria-label="Select"
                        name="user_id">
                        <option value="" disabled selected>請選擇負責人員</option>
                        @foreach ($users as $key => $user)
                            <option value="{{ $user->id }}" @if (old('user_id', $data->user_id ?? '') == $user->id) selected @endif>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label" for="supplier">廠商 <span class="text-danger">*</span></label>
                    <select name="supplier[]" id="supplier" multiple="multiple" hidden
                        class="-select2 -multiple @error('supplier')is-invalid @enderror" data-placeholder="請選擇廠商" required>
                        @foreach ($suppliers as $key => $supplier)
                            <option value="{{ $supplier->id }}" @if (in_array($supplier->id, old('supplier', $current_supplier ?? []))) selected @endif>{{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">上架時間</label>
                    <input class="form-control @error('active_sdate')is-invalid @enderror" name="active_sdate" type="date"
                        aria-label="上架時間" value="{{ old('active_sdate', $data->active_sdate ?? '') }}">
                    @error('active_sdate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">下架時間</label>
                    <input class="form-control @error('active_edate')is-invalid @enderror" name="active_edate" type="date"
                        aria-label="下架時間" value="{{ old('active_edate', $data->active_edate ?? '') }}">
                    @error('active_edate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <fieldset class="col-12 col-lg-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">應稅免稅 <span class="text-danger">*</span></legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline @error('has_tax')is-invalid @enderror">
                            <input class="form-check-input @error('has_tax')is-invalid @enderror" name="has_tax" value="0"
                                type="radio" id="tax_1" required @if (old('has_tax', $data->has_tax ?? '') == '0') checked @endif>
                            <label class="form-check-label" for="tax_1">應稅</label>
                        </div>
                        <div class="form-check form-check-inline @error('has_tax')is-invalid @enderror">
                            <input class="form-check-input @error('has_tax')is-invalid @enderror" name="has_tax" value="1"
                                type="radio" id="tax_2" required @if (old('has_tax', $data->has_tax ?? '') == '1') checked @endif>
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
            <label>商品圖片（可將檔案拖拉至框中即可上傳）</label>
            <div class="upload_image_block -multiple">
                <!-- 可排序圖片集中區塊 -->
                <div class="sortabled">
                    <!-- 新增圖Box -->
                    <div class="sortabled_box" hidden>
                        <!-- /* 預覽圖 */ -->
                        <span class="browser_box box">
                            <span class="icon -move"><i class="bi bi-arrows-move"></i></span>
                            <span class="icon -x"><i class="bi bi-x"></i></span>
                            <img src="" />
                        </span>
                        <!-- /* 進度條 */ -->
                        <div class="progress" hidden>
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width: 1%"></div>
                        </div>
                        <input type="file" name="files[]" accept=".jpg,.jpeg,.png,.gif,.svg" multiple hidden>
                    </div>
                    <!-- 新增圖Box end -->

                    {{-- 舊增圖Box放這裡；sortabled_box 拿掉 hidden，不用input[type="file"] --}}

                    <!-- 按鈕 -->
                    <label for="product_img_add">
                        <span class="browser_box">
                            <i class="bi bi-plus-circle text-secondary fs-4"></i>
                        </span>
                        <input type="file" id="product_img_add" accept=".jpg,.jpeg,.png,.gif,.svg" multiple hidden>
                    </label>
                </div>
            </div>
            <p><mark>圖片限制：不超過1MB，1000×1000px，可上傳JPG/ JPEG/ PNG/ GIF/ SVG格式</mark></p>
        </div>

        <div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
                <a href="/" class="btn btn-outline-primary px-4" role="button">返回列表</a>
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
            $('#mediaSettings .upload_image_block > .sortabled > .sortabled_box[hidden]').remove();

            bindReadImageFiles();
            // 綁定事件: 選擇圖片
            function bindReadImageFiles() {
                // 支援檔案讀取
                if (window.File && window.FileList && window.FileReader) {
                    $('#mediaSettings .upload_image_block label #product_img_add')
                        .off('change')
                        .on('change', function() {
                            readerFiles(this.files);
                        });

                    // 拖曳上傳
                    bindDropFiles();
                } else {
                    console.log('該瀏覽器不支援檔案上傳');
                }
            }

            // 拖曳防止轉頁 drag 拖 | drop 放
            $('html').on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
            });

            // 綁定事件: 拖曳上傳
            function bindDropFiles() {
                // 拖曳進 / 拖曳至上方
                $('#mediaSettings .upload_image_block')
                    .off('dragenter dragover')
                    .on('dragenter dragover', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        $(this).addClass('is-dragover');
                    });

                // 拖曳出 / 放下
                $('#mediaSettings .upload_image_block')
                    .off('dragleave dragend drop.addClass')
                    .on('dragleave dragend drop.addClass', function(e) {
                        $(this).removeClass('is-dragover');
                    });

                // 放下
                $('#mediaSettings .upload_image_block')
                    .off('drop.readFile')
                    .on('drop.readFile', function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        const files = e.originalEvent.dataTransfer.files;
                        readerFiles(files);
                    });
            }

            // 讀檔案
            function readerFiles(files) {
                if (files) {
                    let alertMsg = '';
                    for (let i = 0; i < files.length; i++) {
                        const ff = files[i];

                        /*** 檢查寫這裡 ***/
                        if (!decideTypeOfImage(ff.type) || !decideSizeOfImage(ff.size)) {
                            alertMsg += '檔案[' + ff.name + ']不符合規定\n';
                            continue;
                        }

                        /*** 先上傳的話 以下就不用做 (顯示預覽圖) ***/
                        // 新增圖Box
                        let $new_box = addProductImageBox('#mediaSettings .upload_image_block > .sortabled');
                        let $progress = $new_box.children('.progress');
                        let img = $new_box.find('img')[0];
                        let input = $new_box.find('input[name="files[]"]')[0];

                        // 存檔案
                        let tempFile = new DataTransfer();
                        tempFile.items.add(ff);
                        input.files = tempFile.files;

                        const reader = new FileReader();
                        // 開始載入檔案
                        reader.onloadstart = (function(progress) {
                            return function(e) {
                                progress.children('.progress-bar').attr('aria-valuenow', 1);
                                progress.children('.progress-bar').css('width', '1%');
                                progress.prop('hidden', false);
                            }
                        })($progress);
                        // 載入中
                        reader.onprogress = (function(progress) {
                            return function(e) {
                                if (e.lengthComputable) {
                                    const percentLoaded = Math.round((e.loaded / e.total) * 100);
                                    // console.log(percentLoaded);
                                    if (percentLoaded <= 100) {
                                        progress.children('.progress-bar').attr('aria-valuenow', percentLoaded);
                                        progress.children('.progress-bar').css('width', percentLoaded + '%');
                                    }
                                }
                            }
                        })($progress);
                        // 載入成功
                        reader.onload = (function(aImg, aBox, file) {
                            return function(e) {
                                aImg.src = e.target.result;
                                aImg.file = file;
                            };
                        })(img, $new_box, ff);
                        // 載入完成
                        reader.onloadend = (function(progress) {
                            return function(e) {
                                setTimeout(function() {
                                    progress.prop('hidden', true);
                                }, 200);
                            }
                        })($progress);

                        reader.readAsDataURL(ff);
                        /*** 先上傳的話 以上就不用做 ***/
                    }

                    if (alertMsg) {
                        alert(alertMsg);
                    }
                }
            }

            // 新增圖Box
            function addProductImageBox(upload_bolck) {
                let $sortabled_box = $('<div class="sortabled_box"></div>');
                let $browser_box = $('<span class="browser_box box">' +
                    '<span class="icon -move"><i class="bi bi-arrows-move"></i></span>' +
                    '<span class="icon -x"><i class="bi bi-x"></i></span>' +
                    '<img src="" /></span>');
                let $progress = $('<div class="progress" hidden>' +
                    '<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width: 1%"></div>' +
                    '</div>');
                let $file_input = $('<input type="file" name="files[]" accept=".jpg,.jpeg,.png,.gif,.svg" multiple hidden>');
                $sortabled_box.append([$browser_box, $progress, $file_input]);
                $(upload_bolck).prepend($sortabled_box);

                // 綁定事件
                bindImageClose();
                bindImageMove();

                return $sortabled_box;
            }

            // 綁定事件: 刪除圖片
            function bindImageClose() {
                $('.browser_box.box .-x')
                    .off('click')
                    .on('click', function(e) {
                        e.stopPropagation();
                        e.preventDefault();
                        $(this).closest('.sortabled_box').remove();
                    });
            }
            // 綁定事件: 拖曳排序圖片 #mediaSettings
            function bindImageMove() {
                $('#mediaSettings .upload_image_block .sortabled.ui-sortable').sortable('destroy');

                $('#mediaSettings .upload_image_block .sortabled').sortable({
                    cursor: 'move',
                    handle: 'span.icon.-move',
                    items: 'div.sortabled_box',
                    placeholder: 'placeholder-highlight',
                    update: function() {
                        $('.upload_image_block .sortabled > label').appendTo('.upload_image_block .sortabled');
                    }
                });
            }

            // 判斷檔案類型
            function decideTypeOfImage(type) {
                // console.log('檔案類型: ' + type);
                switch (type) {
                    case "image/jpg":
                    case "image/jpeg":
                    case "image/gif":
                    case "image/png":
                    case "image/svg":
                    case "image/svg+xml":
                        return true;
                    default:
                        return false;
                }
            }
            // 判斷檔案大小
            function decideSizeOfImage(size) {
                // console.log('檔案 size: ' + size);
                let MAX_SIZE = 1024 * 1024;
                return (size <= MAX_SIZE);
            }
            // 判斷圖片尺寸
            function decideAreaOfImage(w, h) {
                // console.log('檔案 W*H: ' + w + ' * ' + h);
                return (w <= 1000) && (h <= 1000);
            }
        </script>
    @endpush
@endOnce
