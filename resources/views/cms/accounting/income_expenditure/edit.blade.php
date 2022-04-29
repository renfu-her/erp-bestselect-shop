@extends('layouts.main')
@section('sub-content')
    <style>
        .grade_1 {
            padding-left: 1ch;
        }

        .grade_2 {
            padding-left: 2ch;
        }

        .grade_3 {
            padding-left: 4ch;
        }

        .grade_4 {
            padding-left: 8ch;
        }
    </style>

    <h2 class="mb-3">付款單科目</h2>
    <form  method="{{ $formMethod }}" action="{{ $formAction }}">
        @csrf
        <div class="card shadow p-4 mb-4">
            <h4 class="mb-3">付款管理預設</h4>
            <div class="col-12 mb-3">
                <label class="form-label" for="select1-multiple">現金</label>
                <select name="income_type[{{ $selectedResult['現金']['acc_income_type_fk'] }}][]"
                        id="select1-multiple"
                        multiple
                        class="select2 -select2 -multiple form-select"
                        @if($isViewMode === true)
                            disabled
                        @endif
                        data-placeholder="可複選">
                    @foreach($totalGrades as $value)
                        <option
                            @if(in_array($value['primary_id'], $selectedResult['現金']['grade_id_fk_arr']))
                            selected
                            @endif

                            @if($value['grade_num'] === 1)
                            class="grade_1"
                            @elseif($value['grade_num'] === 2)
                            class="grade_2"
                            @elseif($value['grade_num'] === 3)
                            class="grade_3"
                            @elseif($value['grade_num'] === 4)
                            class="grade_4"
                            @endif

                            value="{{ $value['primary_id'] }}">{{ $value['code'] . ' ' . $value['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="select2-multiple">支票</label>
                <select name="income_type[{{ $selectedResult['支票']['acc_income_type_fk'] }}][]"
                        id="select2-multiple"
                        multiple
                        class="select2 -select2 -multiple form-select"
                        @if($isViewMode === true)
                        disabled
                        @endif
                        data-placeholder="可複選">
                    @foreach($totalGrades as $value)
                        <option
                            @if(in_array($value['primary_id'], $selectedResult['支票']['grade_id_fk_arr']))
                            selected
                            @endif

                            @if($value['grade_num'] === 1)
                            class="grade_1"
                            @elseif($value['grade_num'] === 2)
                            class="grade_2"
                            @elseif($value['grade_num'] === 3)
                            class="grade_3"
                            @elseif($value['grade_num'] === 4)
                            class="grade_4"
                            @endif

                            value="{{ $value['primary_id'] }}">{{ $value['code'] . ' ' . $value['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="select3-multiple">匯款</label>
                <select name="income_type[{{ $selectedResult['匯款']['acc_income_type_fk'] }}][]"
                        id="select3-multiple"
                        multiple
                        class="select2 -select2 -multiple form-select"
                        @if($isViewMode === true)
                        disabled
                        @endif
                        data-placeholder="可複選">
                    @foreach($totalGrades as $value)
                        <option
                            @if(in_array($value['primary_id'], $selectedResult['匯款']['grade_id_fk_arr']))
                            selected
                            @endif

                            @if($value['grade_num'] === 1)
                            class="grade_1"
                            @elseif($value['grade_num'] === 2)
                            class="grade_2"
                            @elseif($value['grade_num'] === 3)
                            class="grade_3"
                            @elseif($value['grade_num'] === 4)
                            class="grade_4"
                            @endif

                            value="{{ $value['primary_id'] }}">{{ $value['code'] . ' ' . $value['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="select4-multiple">應付帳款</label>
                <select name="income_type[{{ $selectedResult['應付帳款']['acc_income_type_fk'] }}][]"
                        id="select4-multiple"
                        multiple
                        class="select2 -select2 -multiple form-select"
                        @if($isViewMode === true)
                        disabled
                        @endif
                        data-placeholder="可複選">
                    @foreach($totalGrades as $value)
                        <option
                            @if(in_array($value['primary_id'], $selectedResult['應付帳款']['grade_id_fk_arr']))
                            selected
                            @endif

                            @if($value['grade_num'] === 1)
                            class="grade_1"
                            @elseif($value['grade_num'] === 2)
                            class="grade_2"
                            @elseif($value['grade_num'] === 3)
                            class="grade_3"
                            @elseif($value['grade_num'] === 4)
                            class="grade_4"
                            @endif

                            value="{{ $value['primary_id'] }}">{{ $value['code'] . ' ' . $value['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 mb-3 d-none">
                <label class="form-label" for="select5-multiple">其它</label>
                <select name="income_type[{{ $selectedResult['其它']['acc_income_type_fk'] }}][]"
                        id="select5-multiple"
                        multiple
                        class="select2 -select2 -multiple form-select"
                        @if($isViewMode === true)
                        disabled
                        @endif
                        data-placeholder="可複選">
                    @foreach($totalGrades as $value)
                        <option
                            selected
                        {{--
                            @if(in_array($value['primary_id'], $selectedResult['其它']['grade_id_fk_arr']))
                            selected
                            @endif
                        --}}

                            @if($value['grade_num'] === 1)
                            class="grade_1"
                            @elseif($value['grade_num'] === 2)
                            class="grade_2"
                            @elseif($value['grade_num'] === 3)
                            class="grade_3"
                            @elseif($value['grade_num'] === 4)
                            class="grade_4"
                            @endif

                            value="{{ $value['primary_id'] }}">{{ $value['code'] . ' ' . $value['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <h6 class="flex-grow-1 mb-3">外匯</h6>
            <div class="table-responsive tableOverBox">
                <table class="table table-hover tableList">
                    <thead>
                    <tr>
                        <th scope="col">外幣名稱</th>
                        <th scope="col">匯率（兌成台幣）</th>
                        <th scope="col">科目</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($currencyData['selectedCurrencyResult'] as $currencyOption)
                        <tr>
                            <td>{{ $currencyOption->name }}</td>
                            <td>
                                <div class="col-12 col-sm-4">
                                    <input name="currency[{{ $currencyOption->acc_currency_fk }}][rate]"
                                           class="form-control @error('currency.' . $currencyOption->acc_currency_fk . '.rate') is-invalid @enderror"
                                           @if($isViewMode === true)
                                           disabled
                                           @endif
                                           type="number"
                                           step="0.01"
                                           value="{{ $currencyOption->rate }}"
                                           placeholder=""
                                           aria-label="Input">
                                </div>
                                @error('currency.' . $currencyOption->acc_currency_fk . '.rate') {{ $message }} @enderror
                            </td>
                            <td>
                                <select name="currency[{{ $currencyOption->acc_currency_fk }}][gradeOption]"
                                        id="select-data-{{ $currencyOption->acc_currency_fk }}-single"
                                        class="select3 -select2 -single form-select @error('currency.' . $currencyOption->acc_currency_fk . '.gradeOption') is-invalid @enderror"
                                        @if($isViewMode === true)
                                        disabled
                                        @endif
                                        data-placeholder="單選">
                                <option disabled selected value> -- select an option -- </option>

                                @foreach($totalGrades as $value)
                                    <option
                                        @if($value['primary_id'] === $currencyOption->grade_id_fk)
                                        selected
                                        @endif

                                        @if($value['grade_num'] === 1)
                                        class="grade_1"
                                        @elseif($value['grade_num'] === 2)
                                        class="grade_2"
                                        @elseif($value['grade_num'] === 3)
                                        class="grade_3"
                                        @elseif($value['grade_num'] === 4)
                                        class="grade_4"
                                        @endif

                                        value="{{ $value['primary_id'] }}">{{ $value['code'] . ' ' . $value['name'] }}
                                    </option>
                                @endforeach
                                </select>
                                @error('currency.' . $currencyOption->acc_currency_fk . '.gradeOption') {{ $message }} @enderror
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>
        </div>
        <div class="card shadow p-4 mb-4">
            <h4 class="mb-3">付款單預設</h4>
            <div class="col-12 mb-3">
                <label class="form-label" for="">商品</label>
                <select
                    name="orderDefault[product]"
                    required
                    @if($isViewMode === true)
                        disabled
                    @endif
                    class="select3 -select2 -single form-select col-12 @error('orderDefault[product]') is-invalid @enderror"
                    data-placeholder="請選擇">
                    <option disabled selected value> -- select an option --</option>
                    @foreach($totalGrades as $value)
                        <option
                            @if(!is_null($productGradeDefaultArray) && $value['primary_id'] === $productGradeDefaultArray['default_grade_id'])
                            selected
                            @endif

                            @if($value['grade_num'] === 1)
                            class="grade_1"
                            @elseif($value['grade_num'] === 2)
                            class="grade_2"
                            @elseif($value['grade_num'] === 3)
                            class="grade_3"
                            @elseif($value['grade_num'] === 4)
                            class="grade_4"
                            @endif

                            value="{{ $value['primary_id'] }}">{{ $value['code'] . ' ' . $value['name'] }}
                        </option>
                    @endforeach
                </select>

            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="">物流費用</label>
                <select
                    name="orderDefault[logistics]"
                    required
                    @if($isViewMode === true)
                    disabled
                    @endif
                    class="select3 -select2 -single form-select col-12 @error('orderDefault[logistics]') is-invalid @enderror"
                    data-placeholder="請選擇">
                    <option disabled selected value> -- select an option --</option>
                    @foreach($totalGrades as $value)
                        <option
                            @if(!is_null($logisticsGradeDefaultArray) && $value['primary_id'] === $logisticsGradeDefaultArray['default_grade_id'])
                            selected
                            @endif

                            @if($value['grade_num'] === 1)
                            class="grade_1"
                            @elseif($value['grade_num'] === 2)
                            class="grade_2"
                            @elseif($value['grade_num'] === 3)
                            class="grade_3"
                            @elseif($value['grade_num'] === 4)
                            class="grade_4"
                            @endif

                            value="{{ $value['primary_id'] }}">{{ $value['code'] . ' ' . $value['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            @if($isViewMode === true)
                <a href="{{ Route('cms.income_expenditure.edit', [], true) }}">
                    <button type="button" class="btn btn-primary px-4">編輯</button>
                </a>
            @else
                <button type="submit" class="btn btn-primary px-4">儲存</button>
            @endif
            @if($isViewMode === false)
                <a href="{{ Route('cms.income_expenditure.index', [], true) }}">
                    <button type="button" class="btn btn-outline-primary px-4">取消</button>
                </a>
            @endif
        </div>
    </form>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

@endsection

@once
    @push('sub-scripts')
        <script>
            // 會計科目樹狀排版
            $('.select2, .select3').select2({
                templateResult: function (data) {
                    // We only really care if there is an element to pull classes from
                    if (!data.element) {
                        return data.text;
                    }

                    var $element = $(data.element);

                    var $wrapper = $('<span></span>');
                    $wrapper.addClass($element[0].className);

                    $wrapper.text(data.text);

                    return $wrapper;
                }
            });
        </script>
    @endpush
@endonce
