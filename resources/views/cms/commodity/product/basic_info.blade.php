@extends('layouts.main')
@section('sub-content')
<div class="d-flex align-items-center mb-4">
    <h2 class="flex-grow-1 mb-0">新增商品</h2>
    <a href="/" class="btn btn-outline-primary -in-header" role="button">
        <i class="bi bi-arrow-left"></i> 返回上一頁
    </a>
</div>

<form>
    <div class="card shadow p-4 mb-4">
        <h6>基本設定</h6>
        <div class="row">
            <div class="col-12 mb-3">
                <label class="form-label">商品名稱 <span class="text-danger">*</span></label>
                <input class="form-control" type="text" placeholder="例：女休閒短T" maxlength="30" aria-label="商品名稱" required />
            </div>
            <div class="col-12 mb-3">
                <label class="form-label">商品網址（發佈後若有更改網址可能會影響SEO搜尋）</label>
                <div class="input-group">
                    <span class="input-group-text">https://demo.bestselection.com.tw/products/</span>
                    <input type="text" class="form-control"  placeholder="請輸入連結路徑" aria-label="商品網址">
                </div>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label">商品簡述</label>
                <textarea rows="3" class="form-control" maxlength="150" placeholder="請輸入關於產品的描述" aria-label="商品簡述"></textarea>
            </div>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">商品標語</label>
                <input class="form-control" type="text" placeholder="請輸入商品標語" aria-label="商品標語">
            </div>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">商品歸類</label>
                <select class="form-select" aria-label="Select" placeholder="請選擇商品歸類">
                    <option value="" selected>請選擇商品歸類</option>
                    <option value="1">type 1</option>
                    <option value="2">type 2</option>
                    <option value="3">type 3</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">負責人員</label>
                <input class="form-control" type="text" placeholder="請輸入負責人員" aria-label="負責人員">
            </div>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">廠商</label>
                <input class="form-control" type="text" placeholder="廠商列表" aria-label="廠商">
            </div>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">上架時間</label>
                <input class="form-control" type="date" aria-label="上架時間">
            </div>
            <div class="col-12 col-sm-6 mb-3">
                <label class="form-label">下架時間</label>
                <input class="form-control" type="date" aria-label="下架時間">
            </div>
            <fieldset class="col-12 col-lg-6 mb-3">
                <legend class="col-form-label p-0 mb-2">應稅免稅</legend>
                <div class="px-1 pt-1">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" name="tax" type="radio" id="tax_1" checked>
                        <label class="form-check-label" for="tax_1">應稅</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" name="tax" type="radio" id="tax_2">
                        <label class="form-check-label" for="tax_2">免稅（農林漁牧產品/免稅）</label>
                    </div>
                </div>
            </fieldset>
        </div>
        
    </div>

    <div id="mediaSettings" class="card shadow p-4 mb-4">
        <h6>媒體設定</h6>
        <label>圖片設定（圖片尺寸建議：不超過1MB，1000×1000px，可上傳JPG/ JPEG/ PNG/ GIF/ SVG格式）</label>
        <div class="upload_image_block -multiple">
            <!-- 可排序圖片集中區塊 -->
            <div class="sortabled">
                <!-- 新增圖Box -->
                <div class="sortabled_box">
                    <!-- /* 預覽圖 */ -->
                    <span class="browser_box box" hidden>
                        <span class="icon -move"><i class="bi bi-arrows-move"></i></span>
                        <span class="icon -x"><i class="bi bi-x"></i></span>
                        <img src="" />
                    </span>
                    <!-- /* 進度條 */ -->
                    <div class="progress" hidden>
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width: 1%"></div>
                    </div>
                </div>
                <!-- 新增圖Box end -->

                <!-- 按鈕 -->
                <label for="product_img_add">
                    <span class="browser_box">
                        <i class="bi bi-plus-circle text-secondary fs-4"></i>
                    </span>
                    <input type="file" name="" id="product_img_add" accept=".jpg,.jpeg,.png,.gif,.svg" multiple hidden>
                </label>
            </div>
        </div>
    </div>

    <div class="card shadow p-4 mb-4">
        <h6>通路銷售</h6>
    </div>

    <div class="card shadow p-4 mb-4">
        <h6>款式管理</h6>
    </div>

    <div class="card shadow p-4 mb-4">
        <h6>商品優惠</h6>
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
            showWordsLength($('input[maxlength],textarea[maxlength]'));
        </script>
        <script>
            /*** 媒體設定 ***/
            $('#mediaSettings .upload_image_block > .sortabled > .sortabled_box:has([hidden])').remove();
            
            bindReadImageFiles();
            // 綁定事件: 選擇圖片
            function bindReadImageFiles() {
                // 支援檔案讀取
                if (window.File && window.FileList && window.FileReader) {
                    $('#mediaSettings .upload_image_block label input[type="file"]')
                    .off('change')
                    .on('change', function () {
                        readerFiles(this.files);
                    });

                    // 拖曳上傳
                    bindDropFiles();
                } else {
                    console.log('該瀏覽器不支援檔案上傳');
                }
            }

            // 拖曳防止轉頁 drag 拖 | drop 放
            $('html').on('drag dragstart dragend dragover dragenter dragleave drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
            });

            // 綁定事件: 拖曳上傳
            function bindDropFiles() {
                // 拖曳進 / 拖曳至上方
                $('#mediaSettings .upload_image_block')
                .off('dragenter dragover')  
                .on('dragenter dragover', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).addClass('is-dragover');
                });

                // 拖曳出 / 放下
                $('#mediaSettings .upload_image_block')
                .off('dragleave dragend drop.addClass')
                .on('dragleave dragend drop.addClass', function (e) {
                    $(this).removeClass('is-dragover');
                });

                // 放下
                $('#mediaSettings .upload_image_block')
                .off('drop.readFile')
                .on('drop.readFile', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    var files = e.originalEvent.dataTransfer.files;
                    readerFiles(files);
                });
            }

            // 讀檔案
            function readerFiles(files) {
                if (files) {
                    /*** 上傳物件 ***/
                    var fd = new FormData();

                    for (var i = 0; i < files.length; i++) {
                        var ff = files[i];

                        /*** 檢查寫這裡 ***/

                        /*** 上傳物件 ***/
                        fd.append('files[]', ff);
                        
                        /*** 先上傳的話 以下就不用做 (顯示預覽圖) ***/
                        // 新增圖Box
                        var $new_box = addProductImageBox('#mediaSettings .upload_image_block > .sortabled');
                        var $progress = $new_box.children('.progress');
                        var img = $new_box.find('img')[0];

                        var reader = new FileReader();
                        // 開始載入檔案
                        reader.onloadstart = (function (progress) {
                            return function (e) {
                                progress.children('.progress-bar').attr('aria-valuenow', 1);
                                progress.children('.progress-bar').css('width', '1%');
                                progress.prop('hidden', false);
                            }
                        })($progress);
                        // 載入中
                        reader.onprogress = (function (progress) {
                            return function (e) {
                                if (e.lengthComputable) {
                                    var percentLoaded = Math.round((e.loaded / e.total) * 100);
                                    // console.log(percentLoaded);
                                    if (percentLoaded <= 100) {
                                        progress.children('.progress-bar').attr('aria-valuenow', percentLoaded);
                                        progress.children('.progress-bar').css('width', percentLoaded + '%');
                                    }
                                }
                            }
                        })($progress);
                        // 載入成功
                        reader.onload = (function (aImg, aBox, file) {
                            return function (e) {
                                aImg.src = e.target.result;
                                aImg.file = file;
                            };
                        })(img, $new_box, ff);
                        // 載入完成
                        reader.onloadend = (function (progress) {
                            return function (e) {
                                setTimeout(function () {
                                    progress.prop('hidden', true);
                                }, 200);
                            }
                        })($progress);
                        
                        reader.readAsDataURL(ff);
                        /*** 先上傳的話 以上就不用做 ***/
                    }

                    /*** call 上傳API ***/
                    // uploadData(fd);
                }
            }

            // 新增圖Box
            function addProductImageBox(upload_bolck) {
                var $sortabled_box = $('<div class="sortabled_box"></div>');
                var $browser_box = $('<span class="browser_box box">'
                    + '<span class="icon -move"><i class="bi bi-arrows-move"></i></span>'
                    + '<span class="icon -x"><i class="bi bi-x"></i></span>'
                    + '<img src="" /></span>');
                var $progress = $('<div class="progress" hidden>'
                    + '<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width: 1%"></div>'
                    + '</div>');
                $sortabled_box.append([$browser_box, $progress]);
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
                .on('click', function (e) {
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
                    update: function () {
                        $('.upload_image_block .sortabled > label').appendTo('.upload_image_block .sortabled');
                    }
                });
            }

            // 上傳檔案
            function uploadData(formdata) {
                $.ajax({
                    url: '',
                    type: 'POST',
                    data: formdata,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    beforeSend: function () {
                        // 上傳前
                    },
                    complete: function () {
                        // 上傳完成
                    },
                    error: function (err) {
                        // 回傳錯誤
                    },
                    success: function (res) {
                        // 回傳成功
                        // 顯示預覽圖
                    }
                });
            }
        </script>
    @endpush
@endOnce