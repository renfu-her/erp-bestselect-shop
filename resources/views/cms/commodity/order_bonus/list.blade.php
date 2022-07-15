@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">分潤報表</h2>

    <form id="search" action="" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">關鍵字</label>
                    <input type="text" name="keyword" class="form-control" placeholder="姓名或mcode">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">報表月份</label>
                    <input type="date" name="report_month" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-sm-6  mb-3">
                    <label class="form-label">確認狀態</label>
                    @foreach ($check_status as $key => $value)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="check_status"
                                id="check_{{ $key }}" value="{{ $key }}"
                                @if ($cond['check_status'] == strval($key)) checked @endif>
                            <label class="form-check-label" for="check_{{ $key }}">{{ $value }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $data_per_page }}" />

                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </div>
    </form>

    <div class="card shadow p-4 mb-4">
        <form method="POST" action="{{ route('cms.order-bonus.create') }}">
            @csrf
            <div class="row justify-content-end mb-4">
                <div class="col">
                    @can('cms.order-bonus.create')
                        <div class="input-group mb-3">
                            <input type="date" name="date" class="form-control">
                            <button class="btn btn-outline-secondary" type="submit"> <i class="bi bi-plus-lg pe-1"></i>
                                新增報表</button>
                        </div>
                    @endcan
                </div>

            </div>
        </form>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col">報表月份</th>
                        <th scope="col">推薦碼</th>
                        <th scope="col">筆數</th>
                        <th scope="col">銷售獎金</th>
                        <th scope="col">匯款日期</th>
                        <th scope="col">銀行</th>
                        <th scope="col">確認日期</th>
                        <th scope="col">建立日期</th>
                        <th scope="col" class="text-center">刪除</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>{{ $data->report_at }}</td>
                            <td> <a href="{{ route('cms.order-bonus.detail', ['id' => $data->id]) }}">
                                    {{ $data->name }}_{{ $data->mcode }}
                                </a>
                            </td>
                            <td>{{ $data->qty }}</td>
                            <td>{{ $data->bonus }}</td>
                            <td></td>
                            <td></td>
                            <td>{{ $data->checked_at }}</td>
                            <td>{{ $data->created_at }}</td>
                            <td class="text-center">
                                @if (!$data->checked_at)
                                    <a href="javascript:void(0)"
                                        data-href="{{ Route('cms.order-bonus.delete', ['id' => $data->id], true) }}"
                                        data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                        class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                @endif
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
        </script>
    @endpush
@endOnce
