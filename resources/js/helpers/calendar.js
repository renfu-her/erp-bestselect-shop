const TCalendar = require("tui-calendar");

module.exports = class Calendar {
    
    static createCalendar(elem, options) {
        options = {
            readOnly: true,
            create: false,
            calendars: [],
            renderRange: '.renderRange',
            moveToday: '.menu-navi .move-today',
            moveDay: '.menu-navi .move-day',
            ...options
        };

        const calendar = new TCalendar(elem, {
            defaultView: 'month',
            useCreationPopup: options.create,
            useDetailPopup: true,
            isReadOnly: options.readOnly,
            disableClick: true,
            disableDblClick: options.readOnly | options.create,
            calendars: options.calendars,
            month: {
                daynames: ['日', '一', '二', '三', '四', '五', '六'],
                narrowWeekend: true
            },
            theme: {
                'month.dayname.textAlign': 'center',
                'month.weekend.backgroundColor': '#fafafa',
                'common.saturday.color': '#9CBE20',
            },
            template: {
                popupIsAllDay: function () {
                    return '全天';
                },
                popupStateFree: function () {
                    return '空閒';
                },
                popupStateBusy: function () {
                    return '忙碌';
                },
                titlePlaceholder: function () {
                    return '新增標題';
                },
                locationPlaceholder: function () {
                    return '新增地點';
                },
                startDatePlaceholder: function () {
                    return '開始';
                },
                endDatePlaceholder: function () {
                    return '結束';
                },
                popupSave: function () {
                    return '儲存';
                },
                popupUpdate: function () {
                    return '更新';
                },
                popupEdit: function () {
                    return '編輯';
                },
                popupDelete: function () {
                    return '刪除';
                },
                popupDetailState: function (schedule) {
                    return (schedule.state === 'Free') ? '空閒' : '忙碌';
                },
            }
        });

        // render menu
        renderRange();
        $(elem).closest('.cus-calendar').find(options.moveToday).off('click').on('click', function () {
            calendar.today();
            renderRange();
        });
        $(elem).closest('.cus-calendar').find(options.moveDay).off('click').on('click', function () {
            switch ($(this).attr('data-action')) {
                case 'move-prev':
                    calendar.prev();
                    break;
                case 'move-next':
                    calendar.next();
                    break;
            }
            renderRange();
        });

        // 顯示標題月份
        function renderRange() {
            $(elem).closest('.cus-calendar').find(options.renderRange).text(moment(calendar.getDate().toDate()).format('YYYY . M') + '月');
        }

        return calendar;
    }
}