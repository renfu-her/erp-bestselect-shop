@extends('layouts.main')
@section('sub-content')


    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.ar.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回收款作業管理
        </a>
    </div>
    <form method="post" action="{{ $formAction }}">
        <input type="hidden" name="id[ord_orders]" value="{{ $ord_orders_id }}">
    @csrf
        <div class="row justify-content-end mb-4">
            <div class="card shadow p-4 mb-4">
                <fieldset class="col-12 mb-4 ">
                    <h6>收款方式
                        <span class="text-danger">*</span>
                    </h6>
                    @foreach($receivedMethods as $name => $receivedMethod)
                        <div class="form-check form-check-inline">
                            <label class="form-check-label transactType" data-type="{{ $receivedMethod }}">
                                <input class="form-check-input"
                                       name="acc_transact_type_fk"
                                       type="radio"
{{--                                       @if(($payableData->acc_income_type_fk ?? 0) === $receivedMethod['value'])--}}
                                       checked
{{--                                       @endif--}}
                                       value="{{ $name }}">
                                {{ $receivedMethod }}
                            </label>
                        </div>
                    @endforeach
                </fieldset>
                <x-b-form-group title="金額（台幣）" required="true" class="col-12 col-sm-6 mb-2">
                    <input class="form-control @error('tw_price') is-invalid @enderror"
                           name="tw_price"
                           required
                           type="number"
                           step="0.01"
                           value="{{ old('tw_price', $tw_price ?? '') }}"/>
                </x-b-form-group>
{{--                @foreach($defaultArray as $type => $default)--}}
{{--                    <div class="col-12 mb-3">--}}
{{--                        <label class="form-label" for="">{{$default['description']}}</label>--}}
{{--                        <select name="{{$type}}[default_grade_id][]"--}}
{{--                                id=""--}}
{{--                                multiple--}}
{{--                                class="select2 -multiple form-select @error($type . '.default_grade_id.*') is-invalid @enderror"--}}
{{--                                disabled--}}
{{--                                data-placeholder="可複選">--}}
{{--                            @foreach($totalGrades as $totalGrade)--}}
{{--                                <option--}}
{{--                                    @if(in_array($totalGrade['primary_id'], $default['default_grade_id']))--}}
{{--                                    selected--}}
{{--                                    @endif--}}
{{--                                    @if($totalGrade['grade_num'] === 1)--}}
{{--                                    class="grade_1"--}}
{{--                                    @elseif($totalGrade['grade_num'] === 2)--}}
{{--                                    class="grade_2"--}}
{{--                                    @elseif($totalGrade['grade_num'] === 3)--}}
{{--                                    class="grade_3"--}}
{{--                                    @elseif($totalGrade['grade_num'] === 4)--}}
{{--                                    class="grade_4"--}}
{{--                                    @endif--}}
{{--                                    value="{{ $totalGrade['primary_id'] }}">{{ $totalGrade['code'] . ' ' . $totalGrade['name'] }}--}}
{{--                                </option>--}}
{{--                                @error($type . '.default_grade_id.*') {{ $message }} @enderror--}}
{{--                            @endforeach--}}
{{--                        </select>--}}
{{--                    </div>--}}
{{--                @endforeach--}}
            </div>

            <div class="card shadow p-4 mb-4">
                <h6>收款設定</h6>
                <x-b-form-group name="note" title="備註" required="false">
                    <input class="form-control @error('note') is-invalid @enderror"
                           name="note"
                           type="text"
                           value="{{ old('note', $data->note ?? '') }}"/>
                </x-b-form-group>
            </div>

            <div>
                <button type="submit" class="btn btn-primary px-4">確認</button>
                <a onclick="history.back()"
                   class="btn btn-outline-primary px-4"
                   role="button">取消</a>
            </div>
        </div>
    </form>
@endsection
