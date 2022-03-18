@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">優惠劵 / 序號</h2>

    <form id="search" action="" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <label class="form-label">活動名稱 / 序號</label>
                    <input class="form-control" type="text" name="title" placeholder="請輸入活動名稱或序號">
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <fieldset class="col-12 mb-3">
                        <legend class="col-form-label p-0 mb-2">優惠方式</legend>
                        <div class="px-1 pt-1">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="method_code" type="checkbox" checked>
                                    金額
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="method_code" type="checkbox">
                                    百分比
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="method_code" type="checkbox">
                                    優惠劵
                                </label>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label class="form-label" for="status">進行狀態</label>
                    <div class="input-group">
                        <select id="status" class="form-select">
                            <option value="" selected>請選擇</option>
                            <option value="1">待進行</option>
                            <option value="2">進行中</option>
                            <option value="3">已結束</option>
                        </select>
                        <button id="clear_status" class="btn btn-outline-secondary" type="button" data-bs-toggle="tooltip" title="清空">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <input type="hidden" name="status_code" value="1,2">
                    <div id="chip-group-status" class="d-flex flex-wrap bd-highlight chipGroup"></div>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <fieldset class="col-12 mb-3">
                        <legend class="col-form-label p-0 mb-2">優惠範圍</legend>
                        <div class="px-1 pt-1">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="is_global" value="1" type="checkbox">
                                    全館
                                </label>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">起訖日期</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate"
                            name="start_date" value="" aria-label="起始日期" />
                        <input type="date" class="form-control -endDate"
                            name="end_date" value="" aria-label="結束日期" />
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
                @can('cms.promo.create')
                    <a href="{{ Route('cms.promo.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg pe-1"></i> 新增優惠劵 / 序號
                    </a>
                @endcan
            </div>
            <div class="col-auto">
                顯示
                <select class="form-select d-inline-block w-auto" id="dataPerPageElem" aria-label="表格顯示筆數">
                    @foreach (config('global.dataPerPage') as $value)
                        <option value="{{ $value }}" @if ($data_per_page == $value) selected @endif>{{ $value }}</option>
                    @endforeach
                </select>
                筆
            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">優惠劵活動名稱</th>
                        <th scope="col">優惠券序號</th>
                        <th scope="col">優惠方式</th>
                        <th scope="col">折價金額/百分比</th>
                        <th scope="col">最低消費限制</th>
                        <th scope="col">限用商品群組</th>
                        <th scope="col">與其他行銷活動併用限制</th>
                        <th scope="col">進行狀態</th>
                        <th scope="col">開始日期</th>
                        <th scope="col">結束日期</th>
                        <th scope="col">數量</th>
                        <th scope="col" class="text-center">編輯</th>
                        <th scope="col" class="text-center">啟用</th>
                        <th scope="col" class="text-center">暫停</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- @foreach ($dataList as $key => $data) --}}
                        <tr>
                            <th scope="row">1</th>
                            <td>周年慶</td>
                            <td>YEAR50</td>
                            <td>百分比</td>
                            <td>88%</td>
                            <td>$0</td>
                            <td>全館</td>
                            <td>無</td>
                            <td {{-- @class([
                                'text-success' => '進行中', 
                                'text-danger' => '已結束']) --}}>
                                待進行
                            </td>
                            <td>2022/10/1</td>
                            <td>2022/10/31</td>
                            <td>50,000</td>
                            <td>
                                <a href="{{ Route('cms.promo.edit', ['id' => '1'], true) }}"
                                    data-bs-toggle="tooltip" title="編輯"
                                    class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                     <i class="bi bi-pencil-square"></i>
                                 </a>
                            </td>
                            <td>
                                <a href="javascript:void(0)" data-href="#"
                                    data-bs-toggle="modal" data-bs-target="#confirm-start"
                                    class="icon -del icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-play-circle"></i>
                                </a>
                            </td>
                            <td>
                                <a href="javascript:void(0)" data-href="#"
                                    data-bs-toggle="modal" data-bs-target="#confirm-pause"
                                    class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                    <i class="bi bi-pause-circle"></i>
                                </a>
                            </td>
                        </tr>
                    {{-- @endforeach --}}
                </tbody>
            </table>
        </div>
    </div>
    <div class="row flex-column-reverse flex-sm-row">
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
            @if($dataList)
                <div class="mx-3">共 {{ $dataList->lastPage() }} 頁(共找到 {{ $dataList->total() }} 筆資料)</div>
                {{-- 頁碼 --}}
                <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
            @endif
        </div>
    </div>

<!-- Modal -->
<x-b-modal id="confirm-start">
    <x-slot name="title">強制啟用確認</x-slot>
    <x-slot name="body">確認要強制啟用此優惠劵？</x-slot>
    <x-slot name="foot">
        <a class="btn btn-primary btn-ok" href="#">確認並啟用</a>
    </x-slot>
</x-b-modal>
<!-- Modal -->
<x-b-modal id="confirm-pause">
    <x-slot name="title">強制暫停確認</x-slot>
    <x-slot name="body">確認要強制暫停此優惠劵？</x-slot>
    <x-slot name="foot">
        <a class="btn btn-danger btn-ok" href="#">確認並暫停</a>
    </x-slot>
</x-b-modal>
@endsection
@once
    @push('sub-scripts')
        <script>
            // 顯示筆數選擇
            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });
            
            // Modal Control
            $('#confirm-start, #confirm-pause').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
            
            // 進行狀態 region
            let selectStatus = $('input[name="status_code"]').val();
            let all_status = {'1': '待進行', '2': '進行中', '3': '已結束'};
            let Chips_status = new ChipElem($('#chip-group-status'));
            selectStatus = Chips_status.init(selectStatus, all_status);
            
            // bind
            Chips_status.onDelete = function(id) {
                selectStatus.splice(selectStatus.indexOf(id), 1);
                $('input[name="status_code"]').val(selectStatus);
            };
            $('#region').off('change.chips').on('change.chips', function(e) {
                let region = { val: $(this).val(), title: $(this).children(':selected').text()};
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
