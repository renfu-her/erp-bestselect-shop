@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">銷售通路管理</h2>
    <div class="card shadow p-4 mb-4">
        <div class="row mb-4">
            <div class="col-auto">
                @can('cms.sale_channel.create')
                    <a href="{{ Route('cms.sale_channel.create', null, true) }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> 新增通路
                    </a>
                @endcan
            </div>
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                    <tr>
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col">通路名稱</th>
                        <th scope="col">庫存類型</th>
                        <th scope="col">銷售類型</th>
                        <th scope="col">鴻利點數</th>
                        <th scope="col">折扣</th>
                        <th scope="col" class="text-center">同步價格</th>
                        <th scope="col" class="text-center">編輯</th>
                        <th scope="col" class="text-center">刪除</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td>{{ $data->title }}</td>
                            <td>{{ $data->is_realtime_title }}</td>
                            <td>
                                {{ App\Enums\SaleChannel\SalesType::getDescription($data->sales_type) }}
                            </td>
                            <td class="text-center">
                                @if ($data->use_coupon)
                                    <i class="bi bi-check-lg text-success fs-5"></i>
                                @else
                                    <i class="bi bi-x-lg text-danger fs-5"></i>
                                @endif
                            </td>
                            <td>{{ $data->discount }}</td>
                            <td class="text-center">
                                @can('cms.sale_channel.edit')
                                    @if ($data->is_master != 1)
                                        <a href="{{ Route('cms.sale_channel.batch-price', ['id' => $data->id], true) }}"
                                            data-bs-toggle="tooltip" title="同步價格"
                                            class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                            <i class="bi bi-tag"></i>
                                        </a>
                                    @endif
                                @endcan
                            </td>
                            <td class="text-center">
                                @can('cms.sale_channel.edit')
                                    <a href="{{ Route('cms.sale_channel.edit', ['id' => $data->id], true) }}"
                                        data-bs-toggle="tooltip" title="編輯"
                                        class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                @endcan
                            </td>
                            <td class="text-center">
                                @can('cms.sale_channel.delete')
                                    <a href="javascript:void(0)"
                                        data-href="{{ Route('cms.sale_channel.delete', ['id' => $data->id], true) }}"
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

    <div class="card shadow p-4 mb-4">
        <form action="{{ route('cms.sale_channel.update-dividend-setting') }}" method="post">
            @csrf
            <h6>鴻利點數設定</h6>
            <div class="row g-1 align-items-center mb-4">
                <div class="col-auto">
                    <label class="col-form-label">每筆鴻利有效天數：</label>
                </div>
                <div class="col-auto">
                    <input type="number" name="limit_day" value="{{ $dividend_setting->limit_day }}"
                        class="form-control short-input text-center" aria-describedby="鴻利有效天數">
                </div>
                <div class="col-auto">
                    <label class="col-form-label">天</label>
                </div>
                <div class="col-auto">
                    <span class="form-text">（設 0 則為永久有效）</span>
                </div>
            </div>
            <h6>鴻利點數、優惠劵設定</h6>
            <div class="row g-1 align-items-center mb-4">
                <div class="col-auto">
                    <label class="col-form-label">
                        自動發放天數：訂單的付款狀態為<span class="text-decoration-underline text-info">已入款</span>後
                    </label>
                </div>
                <div class="col-auto">
                    <input type="number" name="auto_active_day" value="{{ $dividend_setting->auto_active_day }}"
                        class="form-control short-input text-center" aria-describedby="自動發放鴻利天數">
                </div>
                <div class="col-auto">
                    <label class="col-form-label">天</label>
                </div>
                <div class="col-auto">
                    <span class="form-text">（可至訂單明細更改為手動發放）</span>
                </div>
            </div>

            <div class="col">
                @can('cms.sale_channel.edit')
                    <button type="submit" class="btn btn-primary px-4">儲存</button>
                @endcan
            </div>
        </form>
    </div>


    <!-- Modal -->
    <x-b-modal id="confirm-delete">
        <x-slot name="title">刪除確認</x-slot>
        <x-slot name="body">刪除後將無法復原！確認要刪除？</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
        </x-slot>
    </x-b-modal>
@endsection

@once
    @push('sub-styles')
        <style>
            input.short-input {
                width: 80px;
                min-width: 80px;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
        </script>
    @endpush
@endonce
