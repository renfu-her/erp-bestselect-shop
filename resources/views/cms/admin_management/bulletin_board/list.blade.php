@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">公佈欄</h2>
    <form id="search" action="{{ Route('cms.bulletin_board.index') }}" method="GET">
        <div class="card shadow p-4 mb-4">
            <h6>搜尋條件</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">主旨</label>
                    <input class="form-control" type="text" name="title" value="{{ $cond['title'] }}"
                           placeholder="輸入主旨">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">公告內容</label>
                    <input class="form-control" type="text" name="content" value="{{ $cond['content'] }}"
                           placeholder="輸入公告內容">
                </div>
                <div class="col-12 col-sm-6 mb-3">
                    <label class="form-label">負責人</label>
                    <select class="form-select -select2 -multiple" multiple name="user[]" aria-label="負責人"
                            data-placeholder="多選">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @if (in_array($user->id, $cond['user'])) selected @endif>
                                {{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col">
                <input type="hidden" name="data_per_page" value="{{ $data_per_page }}" />
                <button type="submit" class="btn btn-primary px-4">搜尋</button>
            </div>
        </div>
    </form>

    <div class="card shadow p-4 mb-4">
        <div class="row mb-4">
            <div class="col">
                <a href="{{ Route('cms.bulletin_board.create', null, true) }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> 新增公佈欄
                </a>
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
                    <th scope="col" style="width:10%">#</th>
                    <th scope="col">主旨</th>
                    <th scope="col">重要性</th>
                    <th scope="col">公告日期</th>
                    <th scope="col">公告者</th>
                    <th scope="col" class="text-center">編輯</th>
                    <th scope="col" class="text-center">刪除</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($dataList ?? [] as $key => $data)
                    <tr>
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>
                            <a href="{{ Route('cms.bulletin_board.show', ['id' => $data->id], true) }}">
                                {{ $data->title ?? '' }}
                            </a>
                        </td>
                        <td @class(['fw-bold text-danger' => $data->weight === '3',
                                            'text-success' => $data->weight === '2'])>
                            {{ \App\Enums\AdminManagement\Weight::getDescription($data->weight) }}
                        </td>
                        <td>
                            {{ date('Y/m/d', strtotime($data->created_at ?? '')) }}
                        </td>
                        <td>{{ $data->user_name }}</td>
                            <td class="text-center">
                                @if(\Illuminate\Support\Facades\Auth::user()->id === $data->user_id)
                                    <a href="{{ Route('cms.bulletin_board.edit', ['id' => $data->id], true) }}"
                                       data-bs-toggle="tooltip" title="編輯"
                                       class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(\Illuminate\Support\Facades\Auth::user()->id === $data->user_id)
                                    <a href="javascript:void(0)"
                                       data-href="{{ Route('cms.bulletin_board.delete', ['id' => $data->id], true) }}"
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
            {{-- 頁碼 --}}
            {{--        <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>--}}
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
