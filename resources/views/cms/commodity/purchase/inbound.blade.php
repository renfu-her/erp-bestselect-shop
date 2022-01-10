@extends('layouts.main')
@section('sub-content')

    <h2 class="mb-3">入庫 <a href="{{ Route('cms.purchase.edit', ['id' => $id]) }}">回採購單</a></h2>

    {{var_dump($inboundList)}}
    <BR><BR><BR>
    {{var_dump($inboundOverviewList)}}


    <form id="form1" method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        @error('id')
        <div class="alert alert-danger mt-3">{{ $message }}</div>
        @enderror

        <div class="card shadow p-4 mb-4">
            <h6>本次入庫資料</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3 ">
                    <label class="form-label">選擇倉庫 <span class="text-danger">*</span></label>
                    <select name="depot_id"
                    class="form-select -select2 -single @error('depot_id') is-invalid @enderror"
                            aria-label="請選擇倉庫" required>
                        <option value="" selected disabled>請選擇</option>
                        @foreach ($depotList as $depotItem)
                            <option value="{{ $depotItem['id'] }}">
                                {{ $depotItem['name'] }}
                            </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback">
                        @error('depot_id')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                <h6>入庫資訊</h6>
                <div class="table-responsive tableOverBox">
                    <table class="table table-hover tableList mb-1">
                        <thead>
                        <tr>
                            <th scope="col" class="text-center">刪除</th>
                            <th scope="col">入庫日期</th>
                            <th scope="col">商品</th>
                            <th scope="col">款式</th>
                            <th scope="col">SKU</th>
                            <th scope="col">應進數量</th>
                            <th scope="col">實進數量</th>
                            <th scope="col">異常數量</th>
                            <th scope="col">有效期限</th>
                            <th scope="col">狀態</th>
                            <th scope="col">備註</th>
                        </tr>
                        </thead>
                        <tbody class="-appendClone --selectedP">
                        @if(0 < count(old('product_style_id', $inboundOverviewList?? [])))
                            @foreach (old('product_style_id', $inboundOverviewList ?? []) as $styleKey => $styleVal)
                                <tr class="-cloneElem --selectedP">
                                    <th class="text-center">
                                        <button type="button"
                                                class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <input type="hidden" name="product_style_id[]" value="{{ old('product_style_id.'. $styleKey, $styleVal->product_style_id?? '') }}">
                                    </th>
                                    <td>
                                        <input type="date" class="form-control form-control-sm @error('inbound_date.' . $styleKey) is-invalid @enderror"
                                               name="inbound_date[]" value="{{ old('inbound_date.'. $styleKey, date('Y-m-d')) }}" required/>
                                    </td>
                                    <td data-td="product_title">{{ old('product_title.'. $styleKey, $styleVal->product_title?? '') }}</td>
                                    <td data-td="style_title">{{ old('style_title.'. $styleKey, $styleVal->style_title?? '') }}</td>
                                    <td data-td="sku">{{ old('sku.'. $styleKey, $styleVal->sku?? '') }}</td>
                                    <td data-td="sould_enter_num">{{ old('sould_enter_num.'. $styleKey, $styleVal->sould_enter_num?? '') }}</td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm @error('inbound_num.' . $styleKey) is-invalid @enderror"
                                               name="inbound_num[]" value="{{ old('inbound_num.'. $styleKey, '') }}" min="1" required/>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm @error('error_num.' . $styleKey) is-invalid @enderror"
                                               name="error_num[]" value="{{ old('error_num.'. $styleKey, '') }}" min="0" required/>
                                    </td>
                                    <td>
                                        <input type="date" class="form-control form-control-sm @error('expiry_date.' . $styleKey) is-invalid @enderror"
                                               name="expiry_date[]" value="{{ old('expiry_date.'. $styleKey, '') }}" required/>
                                    </td>
                                    <td>
                                        <select class="form-select @error('status') is-invalid @enderror" name="status[]">
                                            @foreach (App\Enums\Purchase\InboundStatus::asArray() as $key => $val)
                                                <option value="{{ $val }}" @if ($val == old('status', '')) selected @endif>{{ App\Enums\Purchase\InboundStatus::getDescription($val) }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="inbound_memo[]"
                                               value="{{ old('inbound_memo.'. $styleKey, '') }}"/>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
                <div class="d-grid mt-3">

                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary px-4">確認入庫</button>
                    </div>
                </div>

            </div>
        </div>


    </form>
@endsection
