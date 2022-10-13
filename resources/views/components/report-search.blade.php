<form id="search" action="" method="GET">
    <div class="card shadow p-4 mb-4">
        <h6>搜尋條件</h6>
        <div class="row">
            {{ $slot }}
            <fieldset class="col-12 col-sm-6 mb-3">
                <legend class="col-form-label p-0 mb-2">期間</legend>
                <div class="px-1 pt-1">
                    @foreach ($type as $key => $value)
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="type" type="radio"
                                    value="{{ $key }}">
                                {{ $value }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </fieldset>
            <div class="col-12 col-sm-6 mb-3 -year">
                <label class="form-label">年度</label>
                <select class="form-select" name="year" aria-label="年度">
                    @foreach ($year as $value)
                        <option value="{{ $value }}" @if ($value == $cond['year']) selected @endif>
                            {{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-6 mb-3 -season" hidden>
                <label class="form-label">季</label>
                <select class="form-select" name="season" aria-label="季">
                    @foreach ($season as $key => $value)
                        <option value="{{ $key }}" >
                            第{{ $value }}季</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-sm-6 mb-3 -month" hidden>
                <label class="form-label">月份</label>
                <select class="form-select" name="month" aria-label="月份">
                    @for ($i = 1; $i < 13; $i++)
                        <option value="{{ $i }}" @if ($i == $cond['month']) selected @endif>
                            {{ $i }}月</option>
                    @endfor
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-12 mb-3">
                <label class="form-label">起訖日期</label>
                <div class="input-group has-validation">
                    <input type="date" class="form-control @error('sDate') is-invalid @enderror"
                           name="sDate" value="{{ $cond['sDate'] }}" aria-label="起始日期" />
                    <input type="date" class="form-control @error('eDate') is-invalid @enderror"
                           name="eDate" value="{{ $cond['eDate'] }}" aria-label="結束日期" />
                    <div class="invalid-feedback">
                        @error('sDate')
                        {{ $message }}
                        @enderror
                        @error('eDate')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <button type="submit" class="btn btn-primary px-4">搜尋</button>
        </div>
    </div>
</form>
@once
    @push('sub-styles')
        <style>
            h4 {
                color: #415583;
            }

            .negative::before {
                content: '-';
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            // 搜尋條件
            $('input[name="type"][type="radio"]').on('change', function(e) {
                const val = $(this).val();
                switch (val) {
                    case 'year': // 年度
                        $('div.-season, div.-month').prop('hidden', true);
                        break;
                    case 'season': // 季
                        $('div.-month').prop('hidden', true);
                        $('div.-season').prop('hidden', false);
                        break;
                    case 'month': // 月份
                        $('div.-season').prop('hidden', true);
                        $('div.-month').prop('hidden', false);
                        break;

                    default:
                        break;
                }
                setDate(val);
            });
            $('#search select[name="year"], #search select[name="season"], #search select[name="month"]')
            .on('change', function(e) {
                const type = this.name;
                setDate(type);
            });

            // set 起訖日
           // setDate($('input[name="type"][type="radio"]:checked').val());
            function setDate(type) {
                const sDate = $('input[name="sDate"]');
                const eDate = $('input[name="eDate"]');
                let sdate = moment();
                let edate = moment();

                const Year = $('#search select[name="year"]').val();
                switch (type) {
                    case 'year':    // 年度
                        sdate = moment().year(Year).startOf('year');
                        edate = moment().year(Year).endOf('year');
                        break;
                    case 'season':  // 季
                        const Season = $('#search select[name="season"]').val();
                        sdate = moment().quarter(Season).startOf('quarter');
                        edate = moment().quarter(Season).endOf('quarter');
                        break;
                    case 'month':   // 月份
                        const Month = $('#search select[name="month"]').val();
                        sdate = moment().month(Month - 1).startOf('month');
                        edate = moment().month(Month - 1).endOf('month');
                        break;
                
                    default:
                        break;
                }

                sDate.val(sdate.format('YYYY-MM-DD'));
                eDate.val(edate.format('YYYY-MM-DD'));
            }
        </script>
    @endpush
@endOnce