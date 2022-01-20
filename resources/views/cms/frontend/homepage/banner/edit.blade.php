@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">
        @if ($method === 'create') 新增 @else 編輯 @endif 橫幅廣告
    </h2>
    <form class="card-body" method="post" action="{{ $formAction }}" enctype="multipart/form-data">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">橫幅廣告主標題</label>
                    <input class="form-control" value="{{ old('title', $data->title ?? '') }}" name="title"
                           type="text" placeholder="請輸入橫幅廣告主標題" aria-label="橫幅廣告主標題">
                </div>
                <fieldset class="col-12 col-lg-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">顯示橫幅廣告區塊 <span class="text-danger">*</span></legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline @error('is_public')is-invalid @enderror">
                            <input class="form-check-input @error('is_public')is-invalid @enderror" name="is_public"
                                   value="0"
                                   type="radio" id="is_public_1" required
                                   @if (old('is_public', $data->is_public ?? '1') == '1') checked @endif>
                            <label class="form-check-label" for="is_public_1">開啟</label>
                        </div>
                        <div class="form-check form-check-inline @error('is_public')is-invalid @enderror">
                            <input class="form-check-input @error('is_public')is-invalid @enderror" name="is_public"
                                   value="1"
                                   type="radio" id="is_public_0" required
                                   @if (old('is_public', $data->is_public ?? '') == '0') checked @endif>
                            <label class="form-check-label" for="is_public_0">關閉</label>
                        </div>
                        @error('is_public')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </fieldset>

                <div id="mediaSettings" class="card shadow p-4 mb-4">
                    <h6>橫幅廣告</h6>
                    <label>商品圖片（可將檔案拖拉至框中即可上傳）</label>
                    <div class="upload_image_block">
                        <!-- 可排序圖片集中區塊 -->
                        <div class="sortabled">
                            <!-- 新增圖Box -->
                            <div class="sortabled_box" hidden>
                                <!-- /* 預覽圖 */ -->
                                <span class="browser_box box">
                                    <span class="icon -move"><i class="bi bi-arrows-move"></i></span>
                                    <span class="icon -x"><i class="bi bi-x"></i></span>
                                    <img src=""/>
                                </span>
                                <!-- /* 進度條 */ -->
                                <div class="progress" hidden>
                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                         role="progressbar"
                                         aria-valuenow="1" aria-valuemin="0" aria-valuemax="100"
                                         style="width: 1%"></div>
                                </div>
                                <input type="file" name="files" accept=".jpg,.jpeg,.png,.gif,.svg" multiple hidden>
                            </div>
                            <!-- 新增圖Box end -->

                            <!-- 按鈕 -->
                            <label for="img_pc_add">
                                <span class="browser_box">
                                    <i class="bi bi-plus-circle text-secondary fs-4"></i>
                                </span>
                                <input type="file" id="img_pc_add" accept=".jpg,.jpeg,.png,.gif,.svg" multiple
                                       hidden>
                            </label>
                        </div>
                    </div>
                    <p>
                        <mark>圖片尺寸建議：不超過1MB，可上傳JPG/ JPEG/ PNG/ GIF格式</mark>
                    </p>
                    <input type="hidden" name="del_image">
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
            /*** 媒體設定 ***/
            let del_image = [];
            $('#mediaSettings .upload_image_block > .sortabled > .sortabled_box[hidden]').remove();

            // 綁定事件 init
            bindReadImageFiles();
            bindImageClose();
            bindImageMove();
            // 綁定事件: 選擇圖片
            function bindReadImageFiles() {
                // 支援檔案讀取
                if (window.File && window.FileList && window.FileReader) {
                    $('#mediaSettings .upload_image_block label #img_pc_add')
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
                        let input = $new_box.find('input[name="files"]')[0];

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
                let $file_input = $('<input type="file" name="files" accept=".jpg,.jpeg,.png,.gif,.svg" multiple hidden>');
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

                        const id = $(this).closest('.sortabled_box').attr('data-id');
                        console.log(id);
                        if (id) {
                            del_image.push(id);
                            $('input[name="del_image"]').val(del_image.toString());
                        }
                        $(this).closest('.sortabled_box').remove();
                    });
            }
            // 綁定事件: 拖曳排序圖片 #mediaSettings
            function bindImageMove() {
                bindSortableMove($('#mediaSettings .upload_image_block .sortabled'), {
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
@endonce
