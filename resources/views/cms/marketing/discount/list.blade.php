@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">現折優惠</h2>

    <form id="search" action="" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <label class="form-label">活動名稱</label>
                    <input class="form-control" type="text" value="{{ $cond['title'] }}" name="title" placeholder="活動名稱">
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <fieldset class="col-12 mb-3">
                        <legend class="col-form-label p-0 mb-2">優惠方式</legend>
                        <div class="px-1 pt-1">
                            @foreach ($dis_methods as $key => $value)
                                <div class="form-check form-check-inline">
                                    <label class="form-check-label">
                                        <input class="form-check-input" value="{{ $key }}" name="method_code[]"
                                            type="checkbox" @if (in_array($key, $cond['method_code']))) checked @endif>
                                        {{ $value }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </fieldset>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label class="form-label" for="status">進行狀態</label>
                    <div class="input-group">
                        <select id="status" class="form-select">
                            <option value="" selected>請選擇</option>
                            @foreach ($dis_status as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach

                        </select>
                        <button id="clear_status" class="btn btn-outline-secondary" type="button" data-bs-toggle="tooltip"
                            title="清空">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <input type="hidden" name="status_code" value="{{ $cond['status_code'] }}">
                    <div id="chip-group-status" class="d-flex flex-wrap bd-highlight chipGroup"></div>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <fieldset class="col-12 mb-3">
                        <legend class="col-form-label p-0 mb-2">優惠範圍</legend>
                        <div class="px-1 pt-1">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="is_global" value="1"  @if($cond['is_global']) checked @endif type="checkbox">
                                    全館
                                </label>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">起訖日期</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate" name="start_date" value="{{ $cond['start_date'] }}" aria-label="起始日期" />
                        <input type="date" class="form-control -endDate" name="end_date" value="{{ $cond['end_date'] }}" aria-label="結束日期" />
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                        <div class="invalid-feedback">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $data_per_page }}" />

                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </div>
    </form>

    <div class="card shadow p-4 mb-4">
        <div class="row justify-content-end mb-4">
            <div class="col">
                @can('cms.purchase.create')
                    <a href="" class="btn btn-primary">
                        <i class="bi bi-plus-lg pe-1"></i> 新增現折優惠
                    </a>
                @endcan
            </div>
            <div class="col-auto">
                顯示
                <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                    @foreach (config('global.dataPerPage') as $value)
                        <option value="{{ $value }}" @if ($data_per_page == $value) selected @endif>
                            {{ $value }}</option>
                    @endforeach
                </select>
                筆
            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col">活動名稱</th>
                        <th scope="col">優惠方式</th>
                        <th scope="col">進行狀態</th>
                        <th scope="col">開始時間</th>
                        <th scope="col">結束時間</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- @foreach ($dataList as $key => $data) --}}
                    <tr>
                        <th scope="row">1</th>
                        <td>周年慶</td>
                        <td>百分比</td>
                        <td {{-- @class([
                                'text-success' => '進行中', 
                                'text-danger' => '已結束']) --}}>
                            待進行
                        </td>
                        <td>2022/10/1</td>
                        <td>2022/10/31</td>
                    </tr>
                    <tr>
                        <th scope="row">2</th>
                        <td>情人節活動</td>
                        <td>金額</td>
                        <td class="text-danger">已結束</td>
                        <td>2022/2/1</td>
                        <td>2022/2/28</td>
                    </tr>
                    <tr>
                        <th scope="row">3</th>
                        <td>婦女節優惠</td>
                        <td>優惠券</td>
                        <td class="text-success">進行中</td>
                        <td>2022/3/1</td>
                        <td>2022/3/31</td>
                    </tr>
                    {{-- @endforeach --}}
                </tbody>
            </table>
        </div>
    </div>
    <div class="row flex-column-reverse flex-sm-row">
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
            @if ($dataList)
                <div class="mx-3">共 {{ $dataList->lastPage() }} 頁(共找到 {{ $dataList->total() }} 筆資料)</div>
                {{-- 頁碼 --}}
                <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
            @endif
        </div>
    </div>
@endsection
@once
    @push('sub-styles')
        <style>
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            // 顯示筆數選擇
            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });

            // 進行狀態 region
            let selectStatus = $('input[name="status_code"]').val();
            let all_status = @json($dis_status);

            let Chips_status = new ChipElem($('#chip-group-status'));
            selectStatus = Chips_status.init(selectStatus, all_status);

            // bind
            Chips_status.onDelete = function(id) {
                selectStatus.splice(selectStatus.indexOf(id), 1);
                $('input[name="status_code"]').val(selectStatus);
            };
            $('#status').off('change.chips').on('change.chips', function(e) {
                let region = {
                    val: $(this).val(),
                    title: $(this).children(':selected').text()
                };

                if (selectStatus.indexOf(region.val) === -1) {
                    selectStatus.push(region.val);
                    Chips_status.add(region.val, region.title);
                }

                $(this).val('');
                $('input[name="status_code"]').val(selectStatus);
            });
        </script>
    @endpush
@endOnce
