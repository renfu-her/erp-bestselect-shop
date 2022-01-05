const moment = require("moment");

(function () {
    'use strict'

    // // 左側 Menu 事件
    $('#sidebarMenu a.nav-link').off('click.nav').on('click.nav', function (e) {
      // class 'active'
      $('#sidebarMenu ul.btn-toggle-nav li').removeClass('active');
      $(this).parent('li').addClass('active');
    });

    // 會員大頭貼
    $('#memberAvatar').text(($('#memberName').text() || '')[0].toLocaleUpperCase());

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
    $('button[data-clear]').off('click.clear').on('click.clear', function () {
        const $this = $(this);
        $this.siblings('input').val('');
    });

    // 月份最大值
    $('input[type="month"]').attr('max', moment().format('YYYY-MM'));

})();
