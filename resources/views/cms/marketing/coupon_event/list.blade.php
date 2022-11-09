@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">通關優惠券</h2>

    <form id="search" action="" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <label class="form-label">活動名稱</label>
                    <input class="form-control" type="text" value="{{ $cond['title'] }}" name="title" placeholder="活動名稱">
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
                @can('cms.coupon-event.create')
                    <a href="{{ Route('cms.coupon-event.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg pe-1"></i> 新增活動
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
                <thead class="small">
                    <tr>
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col" class="text-center">啟用</th>
                        <th scope="col" class="text-center">編輯</th>
                        <th scope="col">活動名稱</th>
                        <th scope="col">密語</th>
                        <th scope="col">優惠券</th>
                        <th scope="col" class="wrap lh-1">已領張數</th>
                        <th scope="col" class="wrap lh-1">總張數</th>
                        <th scope="col">開始時間</th>
                        <th scope="col">結束時間</th>
                        <th scope="col" class="text-center">刪除</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td class="text-center">
                                <div class="form-check form-switch form-switch-lg mb-0 mt-1">
                                    <input class="form-check-input active-switch" data-id="{{ $data->id }}"
                                        type="checkbox" @if ($data->active == '1') checked @endif name=""
                                        @cannot('cms.coupon-event.edit') disabled @endcannot>
                                </div>
                            </td>
                            <td class="text-center">
                                @can('cms.coupon-event.edit')
                                    <a href="{{ Route('cms.coupon-event.edit', ['id' => $data->id], true) }}"
                                        data-bs-toggle="tooltip" title="編輯"
                                        class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                @endcan
                            </td>
                            <td>{{ $data->title }}</td>
                            <td>{{ $data->sn }}</td>
                            <td>{{ $data->discount_title }}</td>
                            <td class="text-center">{{ $data->total_qty }}</td>
                            <td class="text-center">{{ $data->qty_limit }}</td>
                            <td>{{ date('Y/m/d H:i:s', strtotime($data->start_date)) }}</td>
                            <td>{{ date('Y/m/d H:i:s', strtotime($data->end_date)) }}</td>
                            <td class="text-center">
                                @can('cms.coupon-event.delete')
                                    <a href="javascript:void(0)"
                                        data-href="{{ Route('cms.coupon-event.delete', ['id' => $data->id], true) }}"
                                        data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                        class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                @endcan
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


            let changeActiveUrl = @json(route('api.cms.discount.change-coupon-event-active'));

            // 進行狀態 region
            let selectStatus = $('input[name="status_code"]').val();
            let all_status = [];

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
            $('.active-switch').off('change').on('change', function() {
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
                            toast.show('活動已暫停', {
                                type: 'warning'
                            });
                        }
                    }).catch((err) => {
                        console.error(err);
                        toast.show('發生錯誤', {
                            type: 'danger'
                        });
                    });

            })
        </script>
    @endpush
@endOnce
