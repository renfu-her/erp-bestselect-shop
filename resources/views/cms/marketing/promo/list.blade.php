@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">
        優惠劵 / 代碼</h2>

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
                                            type="checkbox" @if (in_array($key, $cond['method_code'])) ) checked @endif>
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
                                    <input class="form-check-input" name="is_global" value="1"
                                        @if ($cond['is_global']) checked @endif type="checkbox">
                                    全館
                                </label>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">起訖日期</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate" name="start_date"
                            value="{{ $cond['start_date'] }}" aria-label="起始日期" />
                        <input type="date" class="form-control -endDate" name="end_date" value="{{ $cond['end_date'] }}"
                            aria-label="結束日期" />
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
                        <i class="bi bi-plus-lg pe-1"></i> 新增優惠劵 / 代碼
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
                        <th scope="col">#</th>
                        <th scope="col">優惠劵活動名稱</th>
                        <th scope="col">類別</th>
                        <th scope="col">優惠券序號</th>
                        <th scope="col">優惠方式</th>
                        <th scope="col">優惠內容</th>
                        <th scope="col">最低消費限制</th>
                        <th scope="col">進行狀態</th>
                        <th scope="col">全館</th>
                        <th scope="col">併用限制</th>
                        <th scope="col">開始日期</th>
                        <th scope="col">結束日期</th>
                        <th scope="col">數量</th>
                        <th scope="col" class="text-center">編輯</th>
                        <th scope="col" class="text-center">啟用</th>
                        <th scope="col" class="text-center">刪除</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>{{ $data->title }}</td>
                            <td>{{ $data->category_title }}</td>
                            <td>{{ $data->sn }}</td>
                            <td>{{ $data->method_title }}</td>
                            <td>
                                @if ($data->method_code == 'cash')
                                    ${{ number_format($data->discount_value) }}
                                @elseif($data->method_code == 'percent')
                                    {{ $data->discount_value }}%
                                @endif
                            </td>
                            <td>${{ number_format($data->min_consume) }}</td>
                            <td data-td="status" @class([
                                'text-success' => $data->status === '進行中', 
                                'text-danger' => $data->status === '結束' || $data->status === '暫停'
                            ])>
                                {{ $data->status }}
                            </td>
                            <td>
                                @if ($data->is_global == '1')
                                    <i class="bi bi-check-lg text-success fs-5"></i>
                                @endif
                            </td>
                            <td>無</td>
                            <td>{{ date('Y/m/d', strtotime($data->start_date)) }}</td>
                            <td>{{ date('Y/m/d', strtotime($data->end_date)) }}</td>
                            <td>{{ number_format($data->max_usage) }}</td>
                            <td>
                                <a href="{{ Route('cms.promo.edit', ['id' => $data->id], true) }}"
                                    data-bs-toggle="tooltip" title="編輯"
                                    class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch form-switch-lg mb-0 mt-1">
                                    <input class="form-check-input active-switch" data-id="{{ $data->id }}"
                                        type="checkbox" @if ($data->active == '1') checked @endif name="">
                                </div>
                            </td>
                            <td>
                                <a href="javascript:void(0)"
                                    data-href="{{ Route('cms.promo.delete', ['id' => $data->id], true) }}"
                                    data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                    class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
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

    <!-- Modal -->
    <x-b-modal id="confirm-delete">
        <x-slot name="title">刪除確認</x-slot>
        <x-slot name="body">確認要刪除此優惠劵？</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
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
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });


            let changeActiveUrl = @json(route('api.cms.discount.change-active'));

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

            // 啟用
            const statusClass = {
                '進行中': 'text-success',
                '已結束': 'text-danger',
                '暫停': 'text-danger'
            };
            $('.active-switch').on('change', function() {
                const $switch = $(this);
                const active = $switch.prop('checked') ? 1 : 0;
                const dataId = $switch.data('id');

                axios.post(changeActiveUrl, {
                        'id': dataId,
                        'active': active
                    })
                    .then((result) => {
                        console.log(result.data);
                        $switch.closest('tr').find('td[data-td="status"]').text(result.data.data)
                            .removeClass('text-success text-danger')
                            .addClass(statusClass[result.data.data]);
                        if (active) {
                            toast.show('活動已啟用');
                        } else {
                            toast.show('活動已暫停', { type: 'warning' });
                        }
                    }).catch((err) => {
                        console.error(err);
                        toast.show('發生錯誤', { type: 'danger' });
                    });

            })
        </script>
    @endpush
@endOnce
