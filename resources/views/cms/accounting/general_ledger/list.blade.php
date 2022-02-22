@extends('layouts.main')
@section('sub-content')

<h2 class="mb-4">會計科目</h2>

<form id="search" action="{{ Route('cms.general_ledger.index') }}" method="GET">
        <div class="col">
            <input type="hidden" name="data_per_page" value="{{ $data_per_page }}" />
        </div>
</form>

<ul class="nav pm_navbar">
    @foreach($firstGrades as $firstGrade)
        <li class="nav-item">
            <a class="nav-link {{ isActive($firstGrade->id, $currentFirstGradeId) }} "
                                   href="{{ Route('cms.general_ledger.index', ['firstGrade' => $firstGrade->id], true) }}"
            >
                {{ $firstGrade->name }}
            </a>
        </li>
    @endforeach
</ul>
<hr class="narbarBottomLine mb-3">

<form id="actionForms">
    @csrf
    <div class="card shadow p-4 mb-4">
        <div class="row justify-content-end mb-4">
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
            <table class="table table-bordered tableList">
                <thead>
                <tr>
                    <th scope="col">子科目</th>
                    <th scope="col">子次科目</th>
                    <th scope="col">子底科目</th>
                    <th scope="col">類別</th>
                    <th scope="col">備註一</th>
                    <th scope="col">備註二</th>
                    <th scope="col">公司</th>
                </tr>
                </thead>
                <tbody>
                @foreach($secondGrades as $secondKey => $secondGrade)
                    <tr>
                        <th>
                            <a href="{{ Route('cms.general_ledger.show-2nd', ['id' => $secondGrade->id]) }}">
                                {{ $secondGrade->code . ' ' . $secondGrade->name }}
                            </a>
                        </th>
                        <td></td>
                        <td></td>
                        <td>{{ $secondGrade->category }}</td>
                        <td>{{ $secondGrade->note_1 }}</td>
                        <td>{{ $secondGrade->note_2 }}</td>
                        <td>{{ $secondGrade->company }}</td>
                    </tr>
                    @foreach($thirdGrades[$secondKey] as $thirdKey => $thirdGrade)
                        <tr>
                            <th></th>
                            <td><a href="{{ Route('cms.general_ledger.show-3rd', ['id' => $thirdGrade->id]) }}">{{ $thirdGrade->code . ' ' . $thirdGrade->name }}</td>
                            <td></td>
                            <td>{{ $thirdGrade->category }}</td>
                            <td>{{ $thirdGrade->note_1 }}</td>
                            <td>{{ $thirdGrade->note_2 }}</td>
                            <td>{{ $thirdGrade->company }}</td>
                        </tr>
                        @foreach($fourthGrades[$thirdKey] as $fourthGrade)
                            <tr>
                                <td></td>
                                <td></td>
                                <td>
                                    <a href="{{ Route('cms.general_ledger.show-4th', ['id' => $fourthGrade->id]) }}"> {{ $fourthGrade->code }}
                                        <br>
                                        {{ ' ' . $fourthGrade->name }}</a></td>
                                <td>{{ $fourthGrade->category }}</td>
                                <td>{{ $fourthGrade->note_1 }}</td>
                                <td>{{ $fourthGrade->note_2 }}</td>
                                <td>{{ $fourthGrade->company }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row flex-column-reverse flex-sm-row">
        <div class="col-auto">

        </div>
        <div class="col d-flex justify-content-end align-items-center mb-3 mb-sm-0">
        </div>
    </div>
</form>

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
            // 顯示筆數選擇
            $('#dataPerPageElem').on('change', function(e) {
                $('input[name=data_per_page]').val($(this).val());
                $('#search').submit();
            });
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
        </script>
    @endpush
@endonce
