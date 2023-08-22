(function () {
    'use strict'

    // 左側 Menu

    // 會員大頭貼
    $('#memberAvatar').text(($('#memberName').text()[0] || '').toLocaleUpperCase());

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
        let prevN = $this.attr('data-prevDay');
        let nextN = $this.attr('data-nextDay');
        const shiftQty = Number($this.attr('data-shiftQty')) || 1;
        const now = new moment($this.siblings('.-startDate').val() || new Date());
        let newDay;

        if (isNaN(prevN) && isNaN(nextN)) {
            const Key = {years: 'y', quarters: 'Q', months: 'M', weeks: 'w', days: 'd', hours: 'h', minutes: 'm', seconds: 's', milliseconds: 'ms'};
            if (Key[prevN]) {
                newDay = moment(now).subtract(shiftQty, prevN);
            }
            if (Key[nextN]) {
                newDay = moment(now).add(shiftQty, nextN);
            }
        } else {
            prevN = Math.abs(prevN) || 0;
            nextN = Math.abs(nextN) || 0;
            if (prevN > 0) {
                newDay = moment(now).subtract(prevN, 'days');
            }
            if (nextN > 0) {
                newDay = moment(now).add(nextN, 'days');
            }
        }

        $this.siblings('.-startDate').val(newDay.format('YYYY-MM-DD'));
    });
    $('button[data-clear]').off('click.clear').on('click.clear', function () {
        const $this = $(this);
        $this.siblings('input').val('');
    });

    // 月份最大值
    $('input[type="month"]').attr('max', moment().format('YYYY-MM'));

    // 禁多重點擊
    $('.-banRedo').off('submit.banRedo').on('submit.banRedo', function (e) {
        let $Btn = $();
        if ($('.-banReBtn').length > 0) {
            $Btn = $('.-banReBtn');
        } else if ($(this).find('button:submit').length > 0) {
            $Btn = $(this).find('button:submit');
        }
        $Btn.prop('disabled', true).addClass('disabled');
    });
    $('.-blockReBtn').off('click.banRedo').on('click.banRedo', function (e) {
        $(this).prop('disabled', true).addClass('disabled');
    });
    // 去抖動 -debounce
    let inDebounce;
    const formDebounce = _.debounce(() => {
        inDebounce = true;
        console.log('submit', inDebounce);
        $('form.-debounce').trigger('submit');
    }, 3000, { leading: true, trailing: false });
    $('form.-debounce').on('submit', function (e) {
        if (!inDebounce) {
            e.preventDefault();
            return false;
        }
    });
    $('button:submit, .-banReBtn', 'form.-debounce').on('click', () => {
        console.log('debounce');
        $(this).addClass('disabled').prop('disabled', true);
        inDebounce = false;
        if ($('form.-debounce')[0].reportValidity()) {
            formDebounce();
        }
    });

})();
