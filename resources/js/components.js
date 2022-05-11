
(function () {
    'use strict'

    /** 
     * clone 項目2.0：改為手動各自新增，檢查fn改為參數帶入
     * 重複的clone元素加 .-cloneElem
     * 新增鈕加 .-newClone
     * 新增處加 .-appendClone
     */

    // bind 新增
    window.Clone_bindCloneBtn = bindCloneBtn;
    function bindCloneBtn($clone, initFn, {
        appendClone = '.-appendClone', 
        cloneElem = '.-cloneElem',
        delElem = '.-del',
        $thisAppend = [],
        beforeDelFn = null,
        checkFn = null,
        autoRemove = true
    } = {}) {
        let $cloneElem = getCloneElem(initFn, $clone);
        bindDelElem($cloneElem, { appendClone, cloneElem, delElem, beforeDelFn, checkFn, autoRemove });
        let $append = $thisAppend.length ? $thisAppend : $(appendClone);
        $append.append($cloneElem);
        if (typeof checkFn === 'function') {
            checkFn({ appendClone, cloneElem, delElem, $append });
        }
    }

    // 取 clone element
    function getCloneElem(initFn, $clone) {
        let $c = $clone.clone();
        $c.removeClass('d-none');
        
        if (typeof initFn === 'function') {
            initFn($c);
        } else {
            $c.find('input, select').val('');
            $c.find('input, select, button').prop('disabled', false);
            $c.find('button').attr({
                'idx': null
            });
        }
        return $c;
    }

    // bind 刪除
    window.Clone_bindDelElem = bindDelElem;
    function bindDelElem($elem, {
        appendClone = '.-appendClone',
        cloneElem = '.-cloneElem',
        delElem = '.-del',
        beforeDelFn = null,
        checkFn = null,
        autoRemove = true
    } = {}) {
        let $button = ($elem.hasClass('-del')) ? $elem : $elem.find(delElem);
        $button.off('click.del').on('click.del', function () {
            if (typeof beforeDelFn === 'function') {
                beforeDelFn({ appendClone, cloneElem, delElem, $this: $(this) });
            }
            if (autoRemove) {
                $(this).closest(cloneElem).remove();
            }
            if (typeof checkFn === 'function') {
                checkFn({ appendClone, cloneElem, delElem, $append: $(this).closest(appendClone) });
            }
        });
    }

    window.showWordsLength = showWordsLength;
    /**
     * 顯示字數長度
     * @param {*} $elems 顯示目標
     * @param {*} callback 綁定後執行 fn
     * @example showWordsLength($('input[maxlength],textarea[maxlength]'));
     */
    function showWordsLength($elems, callback = false) {
        $elems.each(function () {
            let num = $(this).val().length;
            let max = $(this).attr('maxlength');
            let $DIV = $('<div></div>').addClass('show_words_length')
                .attr('data-after', num + '/' + max);
            $(this).after($DIV);
        });
        bindCountWordsLength($elems);
        
        if (typeof callback === 'function') {
            callback();
        }
    }
    // 綁定事件: 計算字數
    function bindCountWordsLength($elems) {
        $elems.off('change.count keydown.count keyup.count')
        .on('change.count keydown.count keyup.count', function () {
            let num = $(this).val().length;
            let max = $(this).attr('maxlength');
            // console.log(num, max);
            let $DIV = $(this).next('.show_words_length');
            $DIV.attr('data-after', num + '/' + max);
        });
    }

    window.bindSortableMove = bindSortableMove;
    /**
     * 綁定事件: 拖曳項目
     * @param {*} $elems 拖曳區塊
     * @param {*} param1 options
     * @example bindSortableMove($('.sortabled'), {
     *  axis: 'y',
     *  placeholder: 'placeholder-highlight mb-2',
     * });
     */
    function bindSortableMove($elems, options) {
        options = {
            needDestroy: true,
            cursor: 'move',
            destroy: true,
            axis: false,
            handle: '.icon.-move',
            items: '.sortabled_box',
            placeholder: 'placeholder-highlight',
            ...options
        };
        if (options.needDestroy) {
            $elems.filter('.ui-sortable').sortable('destroy');
        }
        $elems.sortable({
            ...options
        });
    }

    /** 
     * Combo box - select2
     */
    $.fn.select2.defaults.set('width', '100%');
    $.fn.select2.defaults.set('allowClear', true);
    $.fn.select2.defaults.set('placeholder', '請選擇');
    $.fn.select2.defaults.set('selectionCssClass', 'pillbox :all:');
    $.fn.select2.defaults.set('language', {
        removeItem: function () {
            return '刪除項目';
        },
        inputTooLong: function (args) {
            let overChars = args.input.length - args.maximum;
            let message = '請刪掉' + overChars + '個字元';
            return message;
        },
        inputTooShort: function (args) {
            let remainingChars = args.minimum - args.input.length;
            let message = '請再輸入' + remainingChars + '個字元';
            return message;
        },
        loadingMore: function () {
            return '載入中…';
        },
        maximumSelected: function (args) {
            let message = '你只能選擇最多' + args.maximum + '項';
            return message;
        },
        noResults: function () {
            return '沒有找到相符的項目';
        },
        searching: function () {
            return '搜尋中…';
        },
        removeAllItems: function () {     
            return '刪除所有項目';
        }
    });
    $('.-select2').select2();

    
    /** 
     * 圖片上傳
     */
    window.bindReadImageFile = bindReadImageFile;
    /**
     * 綁定事件: 選擇圖片 (包含 init刪除事件)
     * @param {*} $elems upload iamge input
     * @param {*} param1 options
     * @example bindReadImageFile($('input'), {
     *  num: 'single',
     *  fileInputName: 'logo',
     *  delFn: function ($that) {}
     * });
     */
    function bindReadImageFile($elems, {
        num = 'single',     // 多檔 multiple
        fileInputName = '',  // saved iamge input's name
        maxSize = 1024,    // (單位 KB) 圖片最大容量
        delFn = null,   // 刪除圖片 fn
        movable = false, // 可拖曳排序的 (多檔 only)
        moveOpt = {},   // 拖曳排序 option (多檔 only)
        addImageBoxFn = addImageBox    // 新增的 image box fn (多檔 only)
    } = {}) {
        if (!fileInputName) {
            fileInputName = $elems.attr('name');
        }
        // init bind del btn
        bindImageClose(delFn, $elems.closest('.upload_image_block').find('.box .-x'));

        // 支援檔案讀取
        if (window.File && window.FileList && window.FileReader) {
            $elems.off('change').on('change', function() {
                readerFiles(this.files);
            });

            // bind 拖曳上傳
            bindDropFile($elems.closest('.upload_image_block'));
        } else {
            console.log('該瀏覽器不支援檔案上傳');
        }
    
        // 綁定事件: 拖曳上傳
        function bindDropFile($uploadElem) {
            // 拖曳防止轉頁 drag 拖 | drop 放
            $('html').on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
            });

            // 拖曳進 / 拖曳至上方
            $uploadElem.off('dragenter dragover')
            .on('dragenter dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('is-dragover');
            });

            // 拖曳出 / 放下
            $uploadElem.off('dragleave dragend drop.addClass')
            .on('dragleave dragend drop.addClass', function(e) {
                $(this).removeClass('is-dragover');
            });

            // 放下
            $uploadElem.off('drop.readFile').on('drop.readFile', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const files = e.originalEvent.dataTransfer.files;
                readerFiles(files);
            });
        }

        // 讀檔案
        function readerFiles(files) {
            const $upload_image_block = $elems.closest('.upload_image_block');
            console.log(files);
            if (files.length) {
                let alertMsg = '';
                for (let i = 0; i < files.length; i++) {
                    const ff = files[i];

                    /*** 檢查寫這裡 ***/
                    if (!decideTypeOfImage(ff.type) || !decideSizeOfImage(ff.size)) {
                        alertMsg += '檔案[' + ff.name + ']不符合規定\n';
                        continue;
                    }

                    /*** 先上傳的話 以下就不用做 (顯示預覽圖) ***/
                    let $img_box;
                    if (num === 'single' || num === 's') {
                        // 上傳圖Box
                        $img_box = $upload_image_block.children('label');
                    } else {
                        // 新增圖Box
                        $img_box = addImageBoxFn($upload_image_block.children('.sortabled'), delFn, movable, moveOpt, num, fileInputName);
                    }
                    
                    // 存檔案
                    if (fileInputName) {
                        let input = $img_box.find(`input[name="${fileInputName}"]`)[0];
                        let tempFile = new DataTransfer();
                        tempFile.items.add(ff);
                        input.files = tempFile.files;
                    }
                    
                    readEvents(ff, $img_box);
                    /*** 先上傳的話 以上就不用做 ***/
                }

                if (alertMsg) {
                    alert(alertMsg);
                }
            } else if (num === 'single' || num === 's') {
                delFn($upload_image_block.find('.-x'));
            }

            // 檔案操作事件
            function readEvents(file, $img_box) {
                let $progress = $img_box.children('.progress');
                let img = $img_box.find('img')[0];
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
                reader.onload = (function(aImg, file, img_box) {
                    return function(e) {
                        aImg.src = e.target.result;
                        aImg.file = file;
                        if (num === 'single' || num === 's') {
                            img_box.children('.browser_box.box').prop('hidden', false);
                            img_box.children('.browser_box.-plusBtn').prop('hidden', true);
                            bindImageClose(delFn, img_box.find('.-x'));
                        }
                    };
                })(img, file, $img_box);

                // 載入完成
                reader.onloadend = (function(progress) {
                    return function(e) {
                        setTimeout(function() {
                            progress.prop('hidden', true);
                        }, 200);
                    }
                })($progress);

                reader.readAsDataURL(file);
            }
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
            // console.log('檔案 size: ' + size + '位元組');
            let MAX_SIZE = 1024 * maxSize;
            return (size <= MAX_SIZE);
        }
        // 判斷圖片尺寸
        function decideAreaOfImage(w, h) {
            // console.log('檔案 W*H: ' + w + ' * ' + h);
            return (w <= 1000) && (h <= 1000);
        }
    }

    // 新增圖Box
    function addImageBox($upload_bolck, delFn, movable, moveOpt, num, fileInputName) {
        const moveBtn = (movable) ? '<span class="icon -move"><i class="bi bi-arrows-move"></i></span>' : '';

        let $sortabled_box = $('<div class="sortabled_box"></div>');
        let $browser_box = $(`<span class="browser_box box">
            ${moveBtn}
            <span class="icon -x"><i class="bi bi-x"></i></span>
            <img src="" /></span>`);
        let $progress = $(`<div class="progress" hidden>
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                aria-valuenow="1" aria-valuemin="0" aria-valuemax="100" style="width: 1%"></div>
            </div>`);
        let $file_input = $(`<input type="file" name="${fileInputName}" accept=".jpg,.jpeg,.png,.gif" ${num} hidden>`);
        $sortabled_box.append([$browser_box, $progress, $file_input]);
        $upload_bolck.prepend($sortabled_box);

        // 綁定事件
        bindImageClose(delFn, $upload_bolck.find('.-x'));
        if (movable) {
            bindSortableMove($upload_bolck, moveOpt);
        }
        
        return $sortabled_box;
    }

    window.bindImageClose = bindImageClose;
    /**
     * 綁定事件: 刪除圖片
     * @param {*} fn 刪除 fn
     * @param {*} $target [可略]事件綁定目標
     * @example bindImageClose(delFn, $('.-x'));
     */
    function bindImageClose(fn, $target) {
        const $x = $target || $('.browser_box.box .-x');
        $x.off('click').on('click', function(e) {
            e.stopPropagation();
            e.preventDefault();

            if (typeof fn === 'function') {
                fn($(this));
            }
        });
    }

    /* 數字千分位 */
    window.formatNumber = function (n) {
        n += '';
        let arr = n.split('.');
        let re = /(\d{1,3})(?=(\d{3})+$)/g;
        return arr[0].replace(re, '$1,') + (arr.length > 1 ? '.' + arr[1] : '');
    }

})();
