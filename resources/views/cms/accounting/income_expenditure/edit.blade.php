@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">付款單科目</h2>
    <form  method="{{ $formMethod }}" action="{{ $formAction }}">
{{--        @method('POST')--}}
        @csrf
        <div class="card shadow p-4 mb-4">
            <h4 class="mb-3">付款管理預設</h4>
            <div class="col-12 mb-3">
                <label class="form-label" for="select1-multiple">現金</label>
                <select name="income_type[{{ $thirdGradesDataList['selectedResult']['現金']['acc_income_type_fk'] }}][]"
                        id="select1-multiple"
                        multiple
                        class="-select2 -multiple form-select"
                        @if($isViewMode === true)
                            disabled
                        @endif
                        data-placeholder="可複選">
                    @foreach($thirdGradesDataList['allGradeOptions'] as $thirdGrade)
                        <option
                            @if(in_array($thirdGrade['id'], $thirdGradesDataList['selectedResult']['現金']['grade_id_fk_arr']))
                            selected
                            @endif
                            value="{{ $thirdGrade['id'] }}">{{ $thirdGrade['code'] . ' ' . $thirdGrade['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="select2-multiple">支票（支付銀行）</label>
                <select name="income_type[{{ $thirdGradesDataList['selectedResult']['支票']['acc_income_type_fk'] }}][]"
                        id="select2-multiple"
                        multiple
                        class="-select2 -multiple form-select"
                        @if($isViewMode === true)
                        disabled
                        @endif
                        data-placeholder="可複選">
                    @foreach($fourthGradesDataList['allGradeOptions'] as $fourthGrade)
                        <option
                            @if(in_array($fourthGrade['id'], $fourthGradesDataList['selectedResult']['支票']['grade_id_fk_arr']))
                            selected
                            @endif
                            value="{{ $fourthGrade['id'] }}">{{ $fourthGrade['code'] . ' ' . $fourthGrade['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="select3-multiple">匯款（匯款銀行）</label>
                <select name="income_type[{{ $thirdGradesDataList['selectedResult']['匯款']['acc_income_type_fk'] }}][]"
                        id="select3-multiple"
                        multiple
                        class="-select2 -multiple form-select"
                        @if($isViewMode === true)
                        disabled
                        @endif
                        data-placeholder="可複選">
                    @foreach($fourthGradesDataList['allGradeOptions'] as $fourthGrade)
                        <option
                            @if(in_array($fourthGrade['id'], $fourthGradesDataList['selectedResult']['匯款']['grade_id_fk_arr']))
                            selected
                            @endif
                            value="{{ $fourthGrade['id'] }}">{{ $fourthGrade['code'] . ' ' . $fourthGrade['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 mb-3">
                <label class="form-label" for="select4-multiple">應付帳款</label>
                <select name="income_type[{{ $thirdGradesDataList['selectedResult']['應付帳款']['acc_income_type_fk'] }}][]"
                        id="select4-multiple"
                        multiple
                        class="-select2 -multiple form-select"
                        @if($isViewMode === true)
                        disabled
                        @endif
                        data-placeholder="可複選">
                    @foreach($fourthGradesDataList['allGradeOptions'] as $fourthGrade)
                        <option
                            @if(in_array($fourthGrade['id'], $fourthGradesDataList['selectedResult']['應付帳款']['grade_id_fk_arr']))
                            selected
                            @endif
                            value="{{ $fourthGrade['id'] }}">{{ $fourthGrade['code'] . ' ' . $fourthGrade['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 mb-3 d-none">
                <label class="form-label" for="select5-multiple">其它</label>
                <select name="income_type[{{ $thirdGradesDataList['selectedResult']['其它']['acc_income_type_fk'] }}][]"
                        id="select5-multiple"
                        multiple
                        class="-select2 -multiple form-select"
                        @if($isViewMode === true)
                        disabled
                        @endif
                        data-placeholder="可複選">
                    @foreach($thirdGradesDataList['allGradeOptions'] as $thirdGrade)
                        <option
                            selected
                        {{--
                            @if(in_array($thirdGrade['id'], $thirdGradesDataList['selectedResult']['其它']['grade_id_fk_arr']))
                            selected
                            @endif
                        --}}
                            value="{{ $thirdGrade['id'] }}">{{ $thirdGrade['code'] . ' ' . $thirdGrade['name'] }}
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
                        <th scope="col">子底科目</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($currencyData['selectedCurrencyResult'] as $currencyOption)
                        <tr>
                            <td>{{ $currencyOption->name }}</td>
                            <td>
                                <div class="col-12 col-sm-4 mb-3">
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
                                        class="-select2 -single form-select @error('currency.' . $currencyOption->acc_currency_fk . '.gradeOption') is-invalid @enderror"
                                        @if($isViewMode === true)
                                        disabled
                                        @endif
                                        data-placeholder="單選">
                                <option disabled selected value> -- select an option -- </option>

                                @foreach($currencyData['allGradeOptions'] as $allGradeOption)
                                        <option
                                            @if($allGradeOption['id'] === $currencyOption->grade_id_fk)
                                            selected
                                            @endif
                                            value="{{ $allGradeOption['id'] }}">{{ $allGradeOption['code'] . ' ' . $allGradeOption['name'] }}
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
                <label class="form-label" for="">商品支出</label>
                <select
                    name="orderDefault[product]"
                    required
                    @if($isViewMode === true)
                        disabled
                    @endif
                    class="-select2 -single form-select col-12 @error('orderDefault[product]') is-invalid @enderror"
                    data-placeholder="請選擇">
                    <option disabled selected value> -- select an option --</option>
                    @foreach($allThirdGrades as $thirdGrade)
                        <option
                            @if(!is_null($productGradeDefaultArray) &&
                                $productGradeDefaultArray['id'] === $thirdGrade['id'])
                                selected
                            @endif
                            value="{{ $thirdGrade['id'] }}">{{ $thirdGrade['code'] . ' ' . $thirdGrade['name'] }}
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
                    class="-select2 -single form-select col-12 @error('orderDefault[logistics]') is-invalid @enderror"
                    data-placeholder="請選擇">
                    <option disabled selected value> -- select an option --</option>
                    @foreach($allThirdGrades as $thirdGrade)
                        <option
                            @if(!is_null($logisticsGradeDefaultArray) &&
                                $logisticsGradeDefaultArray['id'] === $thirdGrade['id'])
                                selected
                            @endif
                            value="{{ $thirdGrade['id'] }}">{{ $thirdGrade['code'] . ' ' . $thirdGrade['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="">
            <button type="submit" class="btn btn-primary px-4">
                @if($isViewMode === true)
                    編輯
                @else
                    儲存
                @endif
            </button>
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
