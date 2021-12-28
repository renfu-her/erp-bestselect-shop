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
        checkFn = null
    } = {}) {
        let $cloneElem = getCloneElem(initFn, $clone);
        bindDelElem($cloneElem, { appendClone, cloneElem, delElem, beforeDelFn, checkFn });
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
        checkFn = null
    } = {}) {
        let $button = ($elem.hasClass('-del')) ? $elem : $elem.find(delElem);
        $button.off('click.del').on('click.del', function () {
            if (typeof beforeDelFn === 'function') {
                beforeDelFn({ appendClone, cloneElem, delElem, $this: $(this) });
            }
            $(this).closest(cloneElem).remove();
            if (typeof checkFn === 'function') {
                checkFn({ appendClone, cloneElem, delElem, $append: $(this).closest(appendClone) });
            }
        });
    }

    /**
     * 顯示字數長度
     */
    window.showWordsLength = showWordsLength;
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

    /** 
     * bind 拖曳項目
     */
    window.bindSortableMove = bindSortableMove;
    function bindSortableMove($elems, {
        destroy = true,
        axis = false,
        handle = '.icon.-move',
        items = '.sortabled_box',
        placeholder = 'placeholder-highlight',
        activate = function (e, ui) {  },
        stop = function (e, ui) {  },
        update = function (e, ui) {  }
    } = {}) {
        if (destroy) {
            $elems.filter('.ui-sortable').sortable('destroy');
        }
        $elems.sortable({
            axis,
            cursor: 'move',
            handle,
            items,
            placeholder,
            activate,
            stop,
            update
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

})();
