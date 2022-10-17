@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">付款單科目</h2>

    <form method="{{ $formMethod }}" action="{{ $formAction }}">
        @csrf
        <div class="card shadow p-4 mb-4">
            <h6>付款管理預設</h6>
            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label" for="select1-multiple">現金</label>
                    <select name="income_type[cash][]"
                            id="select1-multiple"
                            multiple
                            class="select2 -select2 -multiple form-select"
                            @if($isViewMode === true)
                                disabled
                            @endif
                            data-placeholder="可複選">
                        @foreach($total_grades as $value)
                            <option
                                @if(in_array($value['primary_id'], $cash_data))
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
                    <select name="income_type[cheque][]"
                            id="select2-multiple"
                            multiple
                            class="select2 -select2 -multiple form-select"
                            @if($isViewMode === true)
                            disabled
                            @endif
                            data-placeholder="可複選">
                        @foreach($total_grades as $value)
                            <option
                                @if(in_array($value['primary_id'], $cheque_data))
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
                    <select name="income_type[remittance][]"
                            id="select3-multiple"
                            multiple
                            class="select2 -select2 -multiple form-select"
                            @if($isViewMode === true)
                            disabled
                            @endif
                            data-placeholder="可複選">
                        @foreach($total_grades as $value)
                            <option
                                @if(in_array($value['primary_id'], $remittance_data))
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
                    <select name="income_type[accounts_payable][]"
                            id="select4-multiple"
                            multiple
                            class="select2 -select2 -multiple form-select"
                            @if($isViewMode === true)
                            disabled
                            @endif
                            data-placeholder="可複選">
                        @foreach($total_grades as $value)
                            <option
                                @if(in_array($value['primary_id'], $accounts_payable_data))
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
                    <select name="income_type[other][]"
                            id="select5-multiple"
                            multiple
                            class="select2 -select2 -multiple form-select"
                            @if($isViewMode === true)
                            disabled
                            @endif
                            data-placeholder="可複選">
                        @foreach($total_grades as $value)
                            <option
                                selected
                            {{--
                                @if(in_array($value['primary_id'], $other_data))
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
            </div>
           
            <h6>外匯</h6>
            <div class="table-responsive tableOverBox">
                <table class="table table-sm table-hover tableList">
                    <thead class="small">
                        <tr>
                            <th scope="col" style="width: 10%">外幣名稱</th>
                            <th scope="col" style="width: 10%">匯率（兌成台幣）</th>
                            <th scope="col">科目</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($currencyData['selectedCurrencyResult'] as $currencyOption)
                        <tr>
                            <td>{{ $currencyOption->name }}</td>
                            <td>
                                <input name="currency[{{ $currencyOption->currency_id }}][rate]"
                                        class="form-control form-control-sm @error('currency.' . $currencyOption->currency_id . '.rate') is-invalid @enderror"
                                        @if($isViewMode === true)
                                        disabled
                                        @endif
                                        type="number"
                                        step="0.01"
                                        value="{{ $currencyOption->rate }}"
                                        placeholder=""
                                        aria-label="Input">
                                @error('currency.' . $currencyOption->currency_id . '.rate') 
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                            <td>
                                <select name="currency[{{ $currencyOption->currency_id }}][gradeOption]"
                                        id="select-data-{{ $currencyOption->currency_id }}-single"
                                        class="select3 -select2 -single form-select form-select-sm @error('currency.' . $currencyOption->currency_id . '.gradeOption') is-invalid @enderror"
                                        @if($isViewMode === true)
                                        disabled
                                        @endif
                                        data-placeholder="單選">
                                <option disabled selected value>請選擇</option>

                                @foreach($total_grades as $value)
                                    <option
                                        @if($value['primary_id'] === $currencyOption->default_grade_id)
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
                                @error('currency.' . $currencyOption->currency_id . '.gradeOption')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow p-4 mb-4">
            <h6>付款單預設</h6>

            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label" for="">商品</label>
                    <select
                        name="orderDefault[product]"
                        required
                        @if($isViewMode === true)
                            disabled
                        @endif
                        class="select3 -select2 -single form-select @error('orderDefault[product]') is-invalid @enderror"
                        data-placeholder="請選擇">
                        <option disabled selected value>請選擇</option>
                        @foreach($total_grades as $value)
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
                        class="select3 -select2 -single form-select @error('orderDefault[logistics]') is-invalid @enderror"
                        data-placeholder="請選擇">
                        <option disabled selected value>請選擇</option>
                        @foreach($total_grades as $value)
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
            
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @can('cms.payable_default.edit')
        <div class="col-auto">
            @if($isViewMode === true)
                <a href="{{ Route('cms.payable_default.edit', [], true) }}" class="btn btn-primary px-4" role="button">
                    編輯
                </a>
            @else
                <button type="submit" class="btn btn-primary px-4">儲存</button>
            @endif
            @if($isViewMode === false)
                <a href="{{ Route('cms.payable_default.index', [], true) }}" class="btn btn-outline-primary px-4" role="button">
                    取消
                </a>
            @endif
        </div>
        @endcan
    </form>
@endsection

@once
    @push('sub-styles')
        <style>
            /*
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
            */
        </style>
    @endpush
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
