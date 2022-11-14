@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">
        @if (isset($type))
            審核
        @endif 支出憑單
    </h2>
    <form id="search" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">申請人</label>
                    <select class="form-select -select2" name="user" aria-label="負責人">
                        <option></option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">主旨</label>
                    <input class="form-control" type="text" name="title" value="" placeholder="輸入主旨">
                </div>

                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">序號</label>
                    <input class="form-control" type="text" name="sn" value="" placeholder="輸入序號">
                </div>
            </div>
            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label">起訖日期</label>
                    <div class="input-group has-validation">
                        <input type="date" class="form-control" name="sdate" value="" aria-label="起始日期" />
                        <input type="date" class="form-control" name="edate" value="" aria-label="結束日期" />
                    </div>
                </div>
            </div>
            <div class="col">
                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </div>
    </form>
    <div class="card shadow p-4 mb-4">
        <div class="row mb-4">
            <div class="col">
                @if (!isset($type))
                    <a href="{{ Route('cms.expenditure.create', null, true) }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i> 新增支出憑單
                    </a>
                @endif
                @php
                    $bTitle = '審核支出憑單';
                    $bTarget = 'audit-list';
                    if (isset($type)) {
                        $bTitle = '支出憑單列表';
                        $bTarget = 'index';
                    }
                    
                @endphp
                <a href="{{ Route('cms.expenditure.' . $bTarget, null) }}" class="btn btn-success">
                    {{ $bTitle }}
                </a>
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
                        <th scope="col" style="width:40px">#</th>
                        @if (!isset($type))
                            <th scope="col" style="width:40px" class="text-center">編輯</th>
                        @endif
                        <th scope="col">序號</th>
                        <th scope="col">申請人</th>
                        <th scope="col" style="min-width: 100px">主旨</th>
                        <th scope="col">內容</th>
                        <th scope="col">金額</th>
                        <th scope="col">新增日期</th>
                        @if (!isset($type))
                            <th scope="col" class="text-center">刪除</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php
                        $target = isset($type) ? 'audit-confirm' : 'show';
                        $permision = auth()
                            ->user()
                            ->can('cms.expenditure.admin');
                        $user_id = auth()->user()->id;
                    @endphp
                    @foreach ($dataList ?? [] as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            @if (!isset($type))
                                <td class="text-center">
                                    @if (($user_id == $data->user_id && $data->checked_at == null) || $permision)
                                        <a href="{{ Route('cms.expenditure.edit', ['id' => $data->id], true) }}"
                                            data-bs-toggle="tooltip" title="編輯"
                                            class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                    @endif
                                </td>
                            @endif
                            <td class="small">
                                {{ $data->sn }}
                            </td>
                            <td>
                                {{ $data->user_name }}
                            </td>
                            <td class="wrap">
                                <a href="{{ Route('cms.expenditure.' . $target, ['id' => $data->id], true) }}">
                                    {{ $data->title ?? '' }}
                                </a>
                            </td>
                            <td class="wrap small">
                                <div class="multiline-ellipsis">{!! nl2br($data->content) !!}</div>
                            </td>
                            <td class="small">
                                {{ $data->amount }}
                            </td>
                            <td class="small">
                                {{ date('Y/m/d', strtotime($data->created_at ?? '')) }}
                            </td>
                            @if (!isset($type))
                                <td class="text-center">
                                    @if ($user_id == $data->user_id || $permision)
                                        @if ($data->checked_at == null)
                                            <a href="javascript:void(0)"
                                                data-href="{{ Route('cms.expenditure.delete', ['id' => $data->id], true) }}"
                                                data-bs-toggle="modal" data-bs-target="#confirm-delete"
                                                class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        @endif
                                    @endif
                                </td>
                            @endif
                        </tr>
                        <tr>
                            <th></th>
                            <td colspan="8" class="pt-0 ps-0">
                                <table class="table table-sm border border-top-0 m-0">
                                    <tbody>
                                        <tr>
                                            <th rowspan="{{ count($data->users) + 1 }}"
                                                style="writing-mode: vertical-lr; width:40px;"
                                                class="text-center border-end">簽核狀態</th>
                                            <th class="border-secondary">主管</th>
                                            <th class="border-secondary">職稱</th>
                                            <th class="border-secondary">簽核時間</th>
                                        </tr>
                                        @foreach ($data->users as $key => $user)
                                            <tr>
                                                <td>{{ $user->user_name }}</td>
                                                <td>{{ $user->user_title }}</td>
                                                <td>{{ $user->checked_at ? date('Y/m/d H:i:s', strtotime($user->checked_at)) : '' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="row flex-column-reverse flex-sm-row">
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
            {{-- 頁碼 --}}
            <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
        </div>
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
            .multiline-ellipsis {
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 4;
                overflow: hidden;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });

            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });
        </script>
    @endpush
@endonce
