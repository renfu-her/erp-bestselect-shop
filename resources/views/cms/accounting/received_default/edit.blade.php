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
    <h2 class="mb-3">收款單科目</h2>
    <form method="{{ $formMethod }}" action="{{ $formAction }}">
        @csrf
        <div class="card shadow p-4 mb-4">
            <h4 class="mb-3">收款管理預設</h4>
            @foreach($received_method as $key => $value)
                <div class="col-12 mb-3 {{$key == 'other' ? 'd-none' : ''}}">
                    <label class="form-label" for="">{{$value}}</label>
                    <select name="{{$key}}[]"
                            id=""
                            multiple
                            class="select2 -multiple form-select @error($key . '.*') is-invalid @enderror"
                            disabled
                            data-placeholder="可複選">
                        @foreach($total_grades as $grade_value)
                            <option
                                @if(in_array($grade_value['primary_id'], $default_received_grade[$key]) || $key == 'other')
                                selected
                                @endif
                                @if($grade_value['grade_num'] === 1)
                                class="grade_1"
                                @elseif($grade_value['grade_num'] === 2)
                                class="grade_2"
                                @elseif($grade_value['grade_num'] === 3)
                                class="grade_3"
                                @elseif($grade_value['grade_num'] === 4)
                                class="grade_4"
                                @endif
                                value="{{ $grade_value['primary_id'] }}">{{ $grade_value['code'] . ' ' . $grade_value['name'] }}
                            </option>
                            @error($key . '.*') {{ $message }} @enderror
                        @endforeach
                    </select>
                </div>
            @endforeach

            <h6 class="flex-grow-1 mb-3">外匯</h6>
            <div class="table-responsive tableOverBox">
                <table class="table table-hover tableList">
                    <thead>
                    <tr>
                        <th scope="col">外幣名稱</th>
                        <th scope="col">匯率（兌成台幣）</th>
                        <th scope="col" class="col-6">科目</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($currencyDefaultArray ?? [] as $type => $currencyDefaultList)
                        @foreach($currencyDefaultList as $currencyDefault)
                            <tr>
                                <td>{{ $currencyDefault['currency_name'] ?? ''}}</td>
                                <td>
                                    <div class="col-12 col-sm-4">
                                        <input name="{{ $type }}[rate][{{$currencyDefault['currency_id']}}]"
                                               class="form-control @error($type . '.[rate].' . $currencyDefault['currency_id']) is-invalid @enderror"
                                               disabled
                                               type="number"
                                               step="0.01"
                                               value="{{ $currencyDefault['rate'] ?? '' }}"
                                               placeholder=""
                                               aria-label="Input">
                                    </div>
                                    @error($type . '.[rate].' . $currencyDefault['currency_id']) {{ $message }} @enderror
                                </td>
                                <td>
                                    <select name="{{ $type }}[grade_id_fk][{{$currencyDefault['currency_id']}}]"
                                            class="select3 -single form-select @error($type . '.[grade_id_fk].' . $currencyDefault['currency_id']) is-invalid @enderror"
                                            disabled
                                            data-placeholder="單選">
                                        <option disabled selected value>請選擇</option>

                                        @foreach($total_grades as $grade_value)
                                            <option
                                                @if($grade_value['primary_id'] === $currencyDefault['default_grade_id'])
                                                selected
                                                @endif
                                                @if($grade_value['grade_num'] === 1)
                                                class="grade_1"
                                                @elseif($grade_value['grade_num'] === 2)
                                                class="grade_2"
                                                @elseif($grade_value['grade_num'] === 3)
                                                class="grade_3"
                                                @elseif($grade_value['grade_num'] === 4)
                                                class="grade_4"
                                                @endif
                                                value="{{ $grade_value['primary_id'] }}">{{ $grade_value['code'] . ' ' . $grade_value['name'] }}
                                            </option>
                                            @error($type . '.[grade_id_fk].' . $currencyDefault['currency_id']) {{ $message }} @enderror
                                        @endforeach
                                    </select>

                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card shadow p-4 mb-4">
            <h4 class="mb-3">收款單預設</h4>
            <div class="col-12 mb-3">
                <label class="form-label" for="">商品</label>
                <select name="product" required {{ $isViewMode === true ? 'disabled' : '' }} class="select3 -select2 -single form-select col-12 @error('product') is-invalid @enderror" data-placeholder="請選擇">
                    <option disabled selected value>-- select an option --</option>
                    @foreach($total_grades as $value)
                        <option
                            @if(!is_null($default_product_grade) && $value['primary_id'] === $default_product_grade)
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
                <select name="logistics" required {{ $isViewMode === true ? 'disabled' : '' }} class="select3 -select2 -single form-select col-12 @error('logistics') is-invalid @enderror" data-placeholder="請選擇">
                    <option disabled selected value>-- select an option --</option>
                    @foreach($total_grades as $value)
                        <option
                            @if(!is_null($default_logistics_grade) && $value['primary_id'] === $default_logistics_grade)
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

        <div class="card shadow p-4 mb-4">
            <h4 class="mb-3">折扣預設</h4>
            @foreach($discount_type as $key => $dt_value)
            <div class="col-12 mb-3">
                <label class="form-label" for="">{{$dt_value}}</label>
                <select name="{{$key}}" required {{ $isViewMode === true ? 'disabled' : '' }} class="select3 -select2 -single form-select col-12 @error('{{$key}}') is-invalid @enderror" data-placeholder="請選擇">
                    <option disabled selected value>-- select an option --</option>
                    @foreach($total_grades as $value)
                        <option
                            @if($default_discount_grade[$key] == $value['primary_id'])
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
            @endforeach
        </div>

        <div>
            <button type="button" class="btn btn-primary px-4" id="editBtn">編輯</button>
            <button type="submit" class="btn btn-primary px-4" id="submitBtn">儲存</button>
            <a class="btn btn-outline-primary px-4" href="{{ Route('cms.received_default.index', [], true) }}" role="button" id="cancelBtn">取消</a>
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

            //「編輯」「儲存」、「取消」按鈕初始狀態
            $(document).ready(function () {
                $('#editBtn').show();
                $('#submitBtn').hide();
                $('#cancelBtn').hide();
            });

            //點擊「編輯」按鈕後，所有表單變成可編輯狀態
            $('#editBtn').click(function () {
                if ($('input, select').prop('disabled') === false){
                    $('html, body').animate({scrollTop: '0px'}, 300);
                    $('input, select').prop('disabled', false);
                    $('#editBtn').hide();
                    $('#submitBtn').show();
                    $('#cancelBtn').show();
                }
            })
        </script>
    @endpush
@endonce