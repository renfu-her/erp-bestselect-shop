@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">優惠到期通知</h2>

    <form id="search" action="" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <label class="form-label">活動名稱</label>
                    <input class="form-control" type="text" value="{{ $cond['title'] }}" name="title" placeholder="活動名稱">
                </div>

                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">優惠方式</legend>
                    <div class="px-1 pt-1">
                        @foreach ($dis_methods as $key => $value)
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" value="{{ $key }}" name="method_code[]" type="checkbox" @if (in_array($key, $cond['method_code'])) ) checked @endif>
                                    {{ $value }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>

                <div class="col-12 col-md-6 mb-3">
                    <label class="form-label" for="status">進行狀態</label>
                    <div class="input-group">
                        <select id="status" class="form-select">
                            <option value="" selected>請選擇</option>
                            @foreach ($dis_status as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>

                        <button id="clear_status" class="btn btn-outline-secondary" type="button" data-bs-toggle="tooltip" title="清空">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <input type="hidden" name="status_code" value="{{ $cond['status_code'] }}">
                    <div id="chip-group-status" class="d-flex flex-wrap bd-highlight chipGroup"></div>
                </div>

                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">優惠範圍</legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="is_global" value="1" @if ($cond['is_global']) checked @endif type="checkbox">
                                全館
                            </label>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="col-12 col-sm-6 mb-3">
                    <legend class="col-form-label p-0 mb-2">是否寄送</legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="mail_sended" type="radio" value="all" @if ($cond['mail_sended'] == 'all') checked @endif>
                                不限
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="mail_sended" type="radio" value="0" @if ($cond['mail_sended'] == '0') checked @endif>
                                未寄送
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="mail_sended" type="radio" value="1" @if ($cond['mail_sended'] == '1') checked @endif>
                                已寄送
                            </label>
                        </div>
                    </div>
                </fieldset>

                <div class="col-12 mb-3">
                    <label class="form-label">優惠券到期日</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control -startDate" name="start_date" value="{{ $cond['start_date'] }}" aria-label="起始日期">
                        <input type="date" class="form-control -endDate" name="end_date" value="{{ $cond['end_date'] }}" aria-label="結束日期">
                        <button class="btn px-2" data-daysBefore="yesterday" type="button">昨天</button>
                        <button class="btn px-2" data-daysBefore="day" type="button">今天</button>
                        <button class="btn px-2" data-daysBefore="tomorrow" type="button">明天</button>
                        <button class="btn px-2" data-daysBefore="6" type="button">近7日</button>
                        <button class="btn" data-daysBefore="month" type="button">本月</button>
                    </div>
                </div>
            </div>

            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $data_per_page }}" />

                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </div>
    </form>

    <form method="POST" action="{{ $form_action }}">
        @csrf
        <div class="card shadow p-4 mb-4">
            <div class="row justify-content-end mb-4">
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

            <div class="table-responsive tableOverBox mb-3">
                <table class="table table-hover tableList mb-1">
                    <thead class="table-primary">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col" class="text-center"><input class="form-check-input" type="checkbox" id="checkAll"></th>
                            <th scope="col" class="text-center">編輯</th>
                            <th scope="col">姓名</th>
                            <th scope="col">信箱</th>
                            <th scope="col">優惠券活動名稱</th>
                            <th scope="col">優惠類型</th>
                            <th scope="col">優惠方式</th>
                            <th scope="col">優惠內容</th>
                            <th scope="col">進行狀態</th>
                            <th scope="col">全館</th>

                            <th scope="col">優惠券來源訂單</th>

                            {{--
                            <th scope="col">優惠券發送日</th>
                            --}}
                            <th scope="col">優惠券到期日</th>
                            <th scope="col">Email寄送日</th>
                        </tr>
                    </thead>

                    <tbody class="pool">
                        @foreach ($data_list as $key => $data)
                            <tr>
                                <th scope="row">{{ $key + 1 }}</th>
                                <td class="text-center">
                                    <input class="form-check-input single_select" type="checkbox" name="selected[{{ $key }}]" value="{{ $data->id }}">
                                    <input type="hidden" name="id[{{ $key }}]" class="select_input" value="{{ $data->id }}" disabled>
                                </td>

                                <td class="text-center">
                                    @can('cms.discount.edit')
                                        <a href="{{ route('cms.discount_expiring.edit', ['id' => $data->id], true) }}"
                                            data-bs-toggle="tooltip" title="編輯"
                                            class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endcan
                                </td>

                                <td>{!! $data->name !!}</td>
                                <td>{{ $data->email }}</td>

                                <td>{{ $data->title }}</td>
                                <td>{{ $data->category_title }}</td>

                                <td>{{ $data->method_title }}</td>
                                <td>
                                    @if ($data->method_code == 'cash')
                                        ${{ number_format($data->discount_value) }}
                                    @elseif($data->method_code == 'percent')
                                        {{ $data->discount_value }}%
                                    @elseif($data->method_code == 'coupon')
                                        <a href="{{ route('cms.promo.edit', ['id' => $data->coupon_id]) }}">{{ $data->coupon_title }}</a>
                                    @endif
                                </td>

                                <td data-td="status" @class([
                                    'text-success' => $data->status === '進行中',
                                    'text-danger' => $data->status === '已結束' || $data->status === '暫停',
                                ])>{{ $data->status }}</td>
                                <td>
                                    @if ($data->is_global == '1')
                                        <i class="bi bi-check-lg text-success fs-5"></i>
                                    @endif
                                </td>

                                <td>
                                    @if($data->from_order_id)
                                        <a href="{{ Route('cms.order.detail', ['id' => $data->from_order_id]) }}" data-bs-toggle="tooltip" title="明細">{{ $data->from_order_sn }}</a>
                                    @else
                                        {{ $data->from_order_sn }}
                                    @endif
                                </td>

                                {{--
                                <td>{{ $data->active_sdate ? date('Y/m/d', strtotime($data->active_sdate)) : '-' }}</td>
                                --}}
                                <td>{{ $data->active_edate ? date('Y/m/d', strtotime($data->active_edate)) : '-' }}</td>
                                <td>{{ $data->mail_sended_at ? date('Y/m/d', strtotime($data->mail_sended_at)) : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row flex-column-reverse flex-sm-row">
            <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
                @if ($data_list)
                    <div class="mx-3">共 {{ $data_list->lastPage() }} 頁(共找到 {{ $data_list->total() }} 筆資料)</div>
                    {{-- 頁碼 --}}
                    <div class="d-flex justify-content-center">{{ $data_list->links() }}</div>
                @endif
            </div>
        </div>

        <div class="col-auto">
            <button type="submit" id="button1" class="btn btn-primary px-4 submit" disabled="disabled">確認</button>
        </div>
    </form>

    <x-b-modal id="loading" size="modal-dialog-centered" cancelBtn="false">
        <x-slot name="body">
            <p class="-title text-center">處理中，請稍後...</p>
            <div class="progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" 
                    style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
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



            $(function() {
                $('#checkAll').change(function(){
                    $all = $(this)[0];
                    $('.pool tr').each(function( index ) {
                        if($(this).is(':visible')){
                            $(this).find('td input.single_select').prop('checked', $all.checked);

                            $('.submit').prop('disabled', $('input.single_select:checked').length == 0);

                            $(this).find('input.select_input').prop('disabled', $(this).find('td input.single_select:checked').length == 0);
                        }
                    });
                });

                $('.single_select').click(function(){
                    $('.submit').prop('disabled', $('input.single_select:checked').length == 0);
                    $(this).parents('tr').find('input.select_input').prop('disabled', !this.checked);
                });


                const loading = new bootstrap.Modal(document.getElementById('loading'), {
                    backdrop: 'static',
                    keyboard: false
                });

                $('#button1').on('click.loading', function () {
                    loading.show();
                });
            });
        </script>
    @endpush
@endOnce
