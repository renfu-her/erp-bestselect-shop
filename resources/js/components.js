(function () {
    'use strict'

    /** 
     * clone 項目：
     * 重複的clone元素加 .-cloneElem
     * 新增鈕加 .-newClone
     * 新增處加 .-appendClone
     * 送出檢查鈕加 .-checkSubmit
     */
    const _CloneElem = $('.-cloneElem:first').clone();
    _CloneElem.removeClass('d-none');
    $('.-cloneElem.d-none').remove();

    if (_CloneElem.length) {
        // 新增鈕
        $('.-newClone').off('click').on('click', function () {
            let $cloneElem = getCloneElem();
            bindDelElem($cloneElem);
            $('.-appendClone').append($cloneElem);
            checkSubmitDiv();
        });

        // 取 clone element
        function getCloneElem() {
            let $c = _CloneElem.clone();
            $c.find('input').val('');
            $c.find('button').attr({
                'idx': null
            });
            return $c;
        }

        // 檢查數量
        window.Clone_checkSubmitDiv = checkSubmitDiv;
        function checkSubmitDiv() {
            const count = $('.-cloneElem').length;
            if (count > 0) {
                $('.-checkSubmit').removeClass('d-none');
            } else {
                $('.-checkSubmit').addClass('d-none');
            }
        }

        // bind 刪除
        window.Clone_bindDelElem = bindDelElem;
        function bindDelElem($elem) {
            let $button = ($elem.hasClass('-del')) ? $elem : $elem.find('.-del');
            $button.off('click.del').on('click.del', function () {
                $(this).closest('.-cloneElem').remove();
                checkSubmitDiv();
            });
        }
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
     * Combo box - select2
     */
    $.fn.select2.defaults.set('width', '100%');
    $.fn.select2.defaults.set('allowClear', true);
    $.fn.select2.defaults.set('placeholder', '請選擇');
    $.fn.select2.defaults.set('selectionCssClass', 'pillbox form-select');
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

})();
