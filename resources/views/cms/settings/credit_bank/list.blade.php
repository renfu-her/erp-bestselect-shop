@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">銀行列表</h2>

    <div class="card shadow p-4 mb-4">
        <div class="col mb-4">
            @can('cms.credit_bank.create')
                <a href="{{ Route('cms.credit_bank.create', null, true) }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> 新增銀行
                </a>
            @endcan
        </div>

        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                <tr>
                    <th scope="col" style="width:10%">#</th>
                    <th scope="col">銀行名稱</th>
                    <th scope="col">會計科目</th>
                    <th scope="col">會計科目代碼</th>
                    @can('cms.credit_bank.edit')
                        <th scope="col" class="text-center">編輯</th>
                    @endcan
                </tr>
                </thead>
                <tbody>
                @foreach ($dataList as $key => $data)
                    <tr>
                        <th scope="row">{{ $key + 1 }}</th>
                        <td>{{ $data->title }}</td>
                        <td>{{ $data->code }}</td>
                        <td>{{ $data->name }}</td>
                        <td class="text-center">
                            @can('cms.credit_bank.edit')
                                <a href="{{ Route('cms.credit_bank.edit', ['id' => $data->id], true) }}"
                                   data-bs-toggle="tooltip" title="編輯"
                                   class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-pencil-square"></i>
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
            {{-- 頁碼 --}}
            <div class="d-flex justify-content-center">{{ $dataList->links() }}</div>
        </div>
    </div>

@endsection

@once
    @push('sub-scripts')
    @endpush
@endonce
