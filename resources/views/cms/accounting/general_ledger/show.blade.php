@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">會計科目代碼:
        {{ $dataList[0]->code }}
    </h2>
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.general_ledger.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回會計科目列表
        </a>
        @if($isFourthGradeExist === false && $dataList[0]->has_next_grade === 1)
            <a href="{{ Route('cms.general_ledger.create', ['nextGrade' => $nextGrade], true) }}"
               class="btn btn-outline-primary px-4">
                <i class="bi"></i> 新增下層會計科目
            </a>
        @endif
    </div>
    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox">
            <table class="table table-striped tableList">
                <thead>
                <tr>
                    <th scope="col">會計科目名稱</th>
                    <th scope="col">次科目</th>
                    <th scope="col">類別</th>
                    <th scope="col">分公司</th>
                    <th scope="col">備註一</th>
                    <th scope="col">備註二</th>
                    <th scope="col" class="text-center">編輯</th>
                    {{--                                        <th scope="col" class="text-center">刪除</th>--}}
                </tr>
                </thead>
                <tbody>
                @foreach ($dataList as $key => $data)
                    <tr>
                        <td>{{ $data->name }}</td>
                        <td>@if($data->has_next_grade) 有
                            @else 無
                            @endif
                        </td>
                        <td>{{ $data->category }}</td>
                        <td>{{ $data->company }}</td>
                        <td>{{ $data->note_1 }}</td>
                        <td>{{ $data->note_2 }}</td>
                        <td class="text-center">
                            @can('admin.general_ledger.edit')
                                <a href="{{ Route('cms.general_ledger.edit-' . $currentGrade, ['id' => $data->id], true) }}"
                                   data-bs-toggle="tooltip" title="編輯"
                                   class="icon icon-btn fs-5 text-primary rounded-circle border-0">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            @endcan
                        </td>
                        {{--                                                <td class="text-center">--}}
                        {{--                                                    @can('admin.first_grade.delete')--}}
                        {{--                                                    <a href="javascript:void(0)" data-href="{{ Route('cms.first_grade.delete', ['id' => $data->id], true) }}"--}}
                        {{--                                                       data-bs-toggle="modal" data-bs-target="#confirm-delete"--}}
                        {{--                                                       class="icon -del icon-btn fs-5 text-danger rounded-circle border-0">--}}
                        {{--                                                        <i class="bi bi-trash"></i>--}}
                        {{--                                                    </a>--}}
                        {{--                                                    @endcan--}}
                        {{--                                                </td>--}}
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <a class="btn" href="{{ Route('cms.general_ledger.create', ['currentGrade' => $currentGrade], true) }}">
            <div class="d-grid mt-3">
                <button id="addProductBtn"
                        type="button"
                        class="btn btn-outline-primary border-dashed add_ship_rule"
                        style="font-weight: 500;">
                    <i class="bi bi-plus-circle bold">
                    </i>
                    新增同層會計科目
                </button>
            </div>
        </a>
    </div>

@endsection
