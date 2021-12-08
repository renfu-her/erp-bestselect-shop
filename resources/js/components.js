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
            var overChars = args.input.length - args.maximum;
            var message = '請刪掉' + overChars + '個字元';
            return message;
        },
        inputTooShort: function (args) {
            var remainingChars = args.minimum - args.input.length;
            var message = '請再輸入' + remainingChars + '個字元';
            return message;
        },
        loadingMore: function () {
            return '載入中…';
        },
        maximumSelected: function (args) {
            var message = '你只能選擇最多' + args.maximum + '項';
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
