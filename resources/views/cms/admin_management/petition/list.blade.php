@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">申議書</h2>

    <div class="card shadow p-4 mb-4">
        <div class="row mb-4">
            <div class="col">
                <a href="{{ Route('cms.petition.create', null, true) }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> 新增申議書
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
                        <th scope="col" style="width:10%">#</th>
                        <th scope="col" class="text-center">編輯</th>
                        <th scope="col">序號</th>
                        <th scope="col">申請人</th>
                        <th scope="col">主旨</th>
                        <th scope="col">內容</th>
                        <th scope="col">簽核狀態</th>
                        <th scope="col">新增日期</th>


                        <th scope="col" class="text-center">刪除</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dataList ?? [] as $key => $data)
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td class="text-center">
                                @if (\Illuminate\Support\Facades\Auth::user()->id === $data->user_id)
                                    <a href="{{ Route('cms.petition.edit', ['id' => $data->id], true) }}"
                                        data-bs-toggle="tooltip" title="編輯"
                                        class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                @endif
                            </td>
                            <td>
                                {{ $data->sn }}
                            </td>
                            <td>
                                {{ $data->user_name }}
                            </td>
                            <td>
                                <a href="{{ Route('cms.petition.show', ['id' => $data->id], true) }}">
                                    {{ $data->title ?? '' }}
                                </a>
                            </td>
                            <td>{{ $data->content }}</td>
                            <td>
                                @foreach ($data->users as $key => $user)
                                    <div> {{ $user->user_title }} {{ $user->user_name }}
                                        <span>{{ $user->checked_at }}</span>
                                    </div>
                                @endforeach

                            </td>
                            <td>
                                {{ date('Y/m/d', strtotime($data->created_at ?? '')) }}
                            </td>


                            <td class="text-center">
                                <a href="javascript:void(0)"
                                    data-href="{{ Route('cms.petition.delete', ['id' => $data->id], true) }}"
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
