const moment = require("moment");

(function () {
    'use strict'

    // // 左側 Menu 事件
    // $('#sidebarMenu a.nav-link').off('click.nav').on('click.nav', function (e) {
    //   // class 'active'
    //   $('#sidebarMenu ul.btn-toggle-nav li').removeClass('active');
    //   $(this).parent('li').addClass('active');
    // });

    // 會員大頭貼
    // $('#memberAvatar').text($('#memberName').text()[0].toLocaleUpperCase());

    // datepicker 快速鍵
    $('button[data-daysBefore]').off('click.days').on('click.days', function () {
        const $this = $(this);
        const days = $this.attr('data-daysBefore') || 0;
        const today = new moment();
        let sDay, eDay;

        let format = 'YYYY-MM-DD';
        if ($this.siblings('.-startDate').attr('type') === 'month') {
            format = 'YYYY-MM';
        }

        if (isFinite(days)) {
            // 天數
            sDay = moment(today).subtract(Number(days), 'days');
            eDay = moment(today);
        } else {
            switch (days) {
                case 'yesterday':
                    sDay = moment(today).subtract(1, 'days');
                    eDay = moment(today).subtract(1, 'days');
                    break;
                case 'tomorrow':
                    sDay = moment(today).add(1, 'days');
                    eDay = moment(today).add(1, 'days');
                    break;
                case 'lastmonth':
                    sDay = moment(today).subtract(1, 'months').startOf('month');
                    eDay = moment(today).subtract(1, 'months').endOf('month');
                    break;

                default:
                    // days: year 今年 | month 本月 | quarter 本季 | week 本周 | day 今日
                    sDay = moment(today).startOf(days);
                    eDay = moment(today).endOf(days);
                    break;
            }
        }

        if (sDay < eDay) {
            $this.siblings('.-startDate').val(sDay.format(format));
            $this.siblings('.-endDate').val(eDay.format(format));
        } else {
            $this.siblings('.-startDate').val(eDay.format(format));
            $this.siblings('.-endDate').val(sDay.format(format));
        }
    });
    $('button[data-prevDay], button[data-nextDay]').off('click.days').on('click.days', function () {
        const $this = $(this);
        const prevN = Math.abs($this.attr('data-prevDay')) || 0;
        const nextN = Math.abs($this.attr('data-nextDay')) || 0;
        const now = new moment($this.siblings('.-startDate').val());
        let newDay;

        if (prevN > 0) {
            newDay = moment(now).subtract(prevN, 'days');
        }
        if (nextN > 0) {
            newDay = moment(now).add(nextN, 'days');
        }

        $this.siblings('.-startDate').val(newDay.format('YYYY-MM-DD'));
    });

    // 月份最大值
    $('input[type="month"]').attr('max', moment().format('YYYY-MM'));

    /* clone 項目：
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

})();
