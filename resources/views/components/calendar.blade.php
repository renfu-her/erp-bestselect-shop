<div class="cus-calendar {{ $classes }}">
    <div class="calendarMenu">
        <span class="menu-navi">
            <button type="button" class="btn btn-outline-secondary btn-sm move-today rounded-pill">Today</button>
            <button type="button" class="btn btn-outline-secondary btn-sm move-day rounded-circle" data-action="move-prev">
                <i class="bi bi-caret-left-fill calendar-icon"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm move-day rounded-circle" data-action="move-next">
                <i class="bi bi-caret-right-fill calendar-icon"></i>
            </button>
        </span>
        <span class="renderRange"></span>
    </div>
    <div class="calendarDiv">
        <div id="{{ $id }}"></div>
    </div>
</div>

@once
    @push('sub-styles')
    <style>
        
    </style>
    @endpush
    @push('sub-scripts')
    <script>
        // Calendar
        let calendar = Calendar.createCalendar('#' + @json($id), {
            readOnly: @json($readOnly),
            create: @json($create),
            calendars: [
                {
                    id: '1',
                    name: '個人',
                    // color: '#282828',
                    bgColor: '#F6CA00',
                    borderColor: '#F6CA00',
                    dragBgColor: '#F6CA00',
                },
                {
                    id: '2',
                    name: '公司',
                    color: '#282828',
                    bgColor: '#dc9656',
                    borderColor: '#dc9656',
                    dragBgColor: '#dc9656',
                },
                {
                    id: '3',
                    name: 'Boss',
                    color: '#FFFFFF',
                    bgColor: '#a1b56c',
                    borderColor: '#a1b56c',
                    dragBgColor: '#a1b56c',
                },
            ]
        });
        // 新增行程
        calendar.createSchedules([
            {
                id: '1',
                calendarId: '1',
                title: 'my schedule allday',
                start: '2021-10-18',
                end: '2021-10-19',
                isAllDay: true,
                category: 'allday',
                location: '7F',
                attendees: ['Boss', 'Richart', 'Amy'],
                state: 'busy',
                isReadOnly: true,    // schedule is read-only
            },
            {
                id: '2',
                calendarId: '2',
                title: 'task schedule',
                start: '2021-10-18',
                end: '2021-10-18',
                isAllDay: true,
                category: 'allday',
                recurrenceRule: '每月',
                state: 'free',
            },
            {
                id: '3',
                calendarId: '1',
                title: '測試多行time',
                category: 'time',
                dueDateClass: '',
                start: '2021-10-18T17:30:00+09:00',
                end: '2021-10-18T17:31:00+09:00',
            },
            {
                id: '4',
                calendarId: '2',
                title: '第四個time',
                start: '2021-10-18T17:30:00+09:00',
                end: '2021-10-18T17:31:00+09:00',
                category: 'time',
                state: 'free',
            },
            {
                id: '5',
                calendarId: '3',
                title: 'xxxxxx schedule',
                category: 'time',
                dueDateClass: '',
                start: '2021-10-28T17:30:00+09:00',
                end: '2021-10-28T17:31:00+09:00',
            },
            {
                id: '6',
                calendarId: '3',
                title: 'yyyyyyyyyy',
                category: 'time',
                dueDateClass: '',
                start: '2021-10-28T17:30:00+09:00',
                end: '2021-10-29T17:31:00+09:00',
            },
            {
                id: '7',
                calendarId: '1',
                title: 'zzzz',
                category: 'time',
                dueDateClass: '',
                start: '2021-10-28T17:30:00+09:00',
                end: '2021-10-28T17:31:00+09:00',
            },
        ]);

        calendar.on('clickMore', function (e) {
            // console.log('clickMore', e.date, e.target);
        });
    </script>
    @endpush
@endonce