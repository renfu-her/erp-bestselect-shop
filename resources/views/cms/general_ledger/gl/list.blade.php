@extends('layouts.main')

@section('sub-content')

<h2 class="mb-4">會計科目</h2>
<form id="actionForms">
    @csrf
    <div class=" p-4 mb-4">
        <div class="table-responsive tableOverBox">
            <table class="table table-bordered border-secondary tableList">
                <thead>
                <tr>
                    <th scope="col">會計分類</th>
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
                @foreach($totalGrades ?? [] as $firstGrade)
                    <tr>
                        <td>
                            <a href="{{ Route('cms.general_ledger.show', ['id' => $firstGrade['id'], 'type'=>'1st']) }}">{{ $firstGrade['code'] . ' ' . $firstGrade['name'] }}</a>
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>{{ $firstGrade['category'] }}</td>
                        <td>{{ $firstGrade['note_1'] }}</td>
                        <td>{{ $firstGrade['note_2'] }}</td>
                        <td>{{ $firstGrade['company'] }}</td>
                    </tr>
                    @foreach($firstGrade['second'] ?? [] as $secondKey => $secondGrade)
                        <tr>
                            <th></th>
                            <td>
                                <a href="{{ Route('cms.general_ledger.show', ['id' => $secondGrade['id'], 'type'=>'2nd']) }}">
                                    {{ $secondGrade['code'] . ' ' . $secondGrade['name'] }}
                                </a>
                            </td>
                            <td></td>
                            <td></td>
                            <td>{{ $secondGrade['category'] }}</td>
                            <td>{{ $secondGrade['note_1'] }}</td>
                            <td>{{ $secondGrade['note_2'] }}</td>
                            <td>{{ $secondGrade['company'] }}</td>
                        </tr>
                        @foreach($secondGrade['third'] ?? [] as $thirdKey => $thirdGrade)
                            <tr>
                                <th></th>
                                <td></td>
                                <td>
                                    <a href="{{ Route('cms.general_ledger.show', ['id' => $thirdGrade['id'], 'type'=>'3rd']) }}">{{ $thirdGrade['code'] . ' ' . $thirdGrade['name'] }}
                                </td>
                                <td></td>
                                <td>{{ $thirdGrade['category'] }}</td>
                                <td>{{ $thirdGrade['note_1'] }}</td>
                                <td>{{ $thirdGrade['note_2'] }}</td>
                                <td>{{ $thirdGrade['company'] }}</td>
                            </tr>
                            @foreach($thirdGrade['fourth'] ?? [] as $fourthGrade)
                                <tr>
                                    <th></th>
                                    <td></td>
                                    <td></td>
                                    <td>
                                        <a href="{{ Route('cms.general_ledger.show', ['id' => $fourthGrade['id'], 'type'=>'4th']) }}"> {{ $fourthGrade['code'] }}
                                            <br>
                                            {{ ' ' . $fourthGrade['name'] }}</a></td>
                                    <td>{{ $fourthGrade['category'] }}</td>
                                    <td>{{ $fourthGrade['note_1'] }}</td>
                                    <td>{{ $fourthGrade['note_2'] }}</td>
                                    <td>{{ $fourthGrade['company'] }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    @endforeach
                @endforeach
                </tbody>
            </table>
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
