@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.purchase.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>

    <h2 class="mb-4">@if ($method === 'create') 新增@else 編輯@endif採購單</h2>

    <form method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        @if ($method === 'edit')
            <input type='hidden' name='id' value="{{ old('id', $id) }}"/>
        @endif
        @error('id')
        <div class="alert alert-danger mt-3">{{ $message }}</div>
        @enderror
        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div class="col-12 col-sm-6 mb-3 ">
                    <label class="form-label">採購廠商</label>
                    <select name="supplier" id="supplier"
                            class="form-select @error('supplier') is-invalid @enderror"
                            aria-label="採購廠商" required>
                        <option value="" selected disabled>請選擇</option>
                        @foreach ($supplierList as $supplierItem)
                            <option value="{{ $supplierItem->id }}"
                                    @if ($supplierItem->id == old('supplier', $data->supplier_id ?? '')) selected @endif>
                                {{ $supplierItem->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 col-sm-6 mb-3 ">
                    <label class="form-label">廠商預計進貨日期</label>
                    <div class="input-group has-validation">
                        <input type="date" id="date" name="scheduled_date" value="{{ old('scheduled_date', $data->scheduled_date  ?? '') }}"
                               class="form-control @error('scheduled_date') is-invalid @enderror" aria-label="廠商預計進貨日期"
                               required/>
                        <button class="btn btn-outline-secondary icon" type="button" id="resetDate"
                                data-bs-toggle="tooltip"
                                title="清空日期"><i class="bi bi-calendar-x"></i></button>
                        <div class="invalid-feedback">
                            @error('date')
                            {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow p-4 mb-4">
            <h6>採購清單</h6>
            <div class="table-responsive tableOverBox">
                <table class="table table-hover table-borderless tableList">
                    <thead class="border-bottom border-dark">
                    <tr>
                        <th scope="col" class="text-center">刪除</th>
                        <th scope="col">商品名稱</th>
                        <th scope="col">SKU</th>
                        <th scope="col">採購數量</th>
                        <th scope="col">採購價錢</th>
                    </tr>
                    </thead>
                    <tbody class="-appendClone">
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="6">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary -newClone"
                                        style="border-style: dashed; font-weight: 500;">
                                    <i class="bi bi-plus-circle bold"></i> 加入商品
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card shadow p-4 mb-4">
            <h6>代墊單</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3 ">
                    <label class="form-label">訂金採購單</label>
                    <input class="form-control" type="text" name="deposit_pay_num" placeholder="請輸入訂金採購單"
                           value="{{ old('deposit_pay_num', $depositPayData->order_num ?? '') }}" aria-label="訂金採購單">
                </div>
                <div class="col-12 col-sm-6 mb-3 ">
                    <label class="form-label">尾款採購單</label>
                    <input class="form-control" type="text" name="final_pay_num" placeholder="請輸入尾款採購單"
                           value="{{ old('final_pay_num', $finalPayData->order_num ?? '') }}" aria-label="尾款採購單">
                </div>
            </div>
        </div>

        <div class="card shadow p-4 mb-4">
            <h6>付款資訊</h6>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3 ">
                    <label class="form-label">匯款銀行</label>
                    <input class="form-control @error('bank_cname') is-invalid @enderror" type="text" name="bank_cname" placeholder="請輸入匯款銀行"
                           value="{{ old('bank_cname', $data->bank_cname ?? '') }}" aria-label="匯款銀行">
                    <div class="invalid-feedback">
                        @error('bank_cname')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3 ">
                    <label class="form-label">匯款銀行代碼</label>
                    <input class="form-control @error('bank_code') is-invalid @enderror" type="text" name="bank_code" placeholder="請輸入匯款銀行代碼"
                           value="{{ old('bank_code', $data->bank_code ?? '') }}" aria-label="匯款銀行代碼">
                    <div class="invalid-feedback">
                        @error('bank_code')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3 ">
                    <label class="form-label">匯款戶名</label>
                    <input class="form-control @error('bank_acount') is-invalid @enderror" type="text" name="bank_acount" placeholder="請輸入匯款戶名"
                           value="{{ old('bank_acount', $data->bank_acount ?? '') }}" aria-label="匯款戶名">
                    <div class="invalid-feedback">
                        @error('bank_acount')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                <div class="col-12 col-sm-6 mb-3 ">
                    <label class="form-label">匯款帳號</label>
                    <input class="form-control @error('bank_numer') is-invalid @enderror" type="text" name="bank_numer" placeholder="請輸入匯款帳號"
                           value="{{ old('bank_numer', $data->bank_numer ?? '') }}" aria-label="匯款帳號">
                    <div class="invalid-feedback">
                        @error('bank_numer')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
                <fieldset class="col-12 mb-3">
                    <legend class="col-form-label p-0 mb-2">付款方式</legend>
                    <div class="px-1 pt-1">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="pay_type" id="pay_type1" value="0"
                                   @if (old('pay_type', $data->pay_type ?? '') == '0') checked @endif required aria-label="付款方式">
                            <label class="form-check-label" for="pay_type1">先付(訂金)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="pay_type" id="pay_type2" value="1"
                                   @if (old('pay_type', $data->pay_type ?? '') == '1') checked @endif required aria-label="付款方式">
                            <label class="form-check-label" for="pay_type2">先付(一次付清)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="pay_type" id="pay_type3" value="2"
                                   @if (old('pay_type', $data->pay_type ?? '') == '2') checked @endif required aria-label="付款方式">
                            <label class="form-check-label" for="pay_type3">貨到付款</label>
                        </div>
                    </div>
                </fieldset>
            </div>
            <div class="row">
                <div class="col-12 col-sm-3 mb-3 ">
                    <label class="form-label">訂金金額</label>
                    <input class="form-control" type="text" name="deposit_pay_price" placeholder="請輸入訂金金額"
                           value="{{ old('deposit_pay_price', $depositPayData->price ?? '') }}" aria-label="訂金金額">
                </div>
                <div class="col-12 col-sm-3 mb-3 ">
                    <label class="form-label">訂金付款日期</label>
                    <input type="date" class="form-control @error('deposit_pay_date') is-invalid @enderror"
                           name="deposit_pay_date" placeholder="訂金付款日期"
                           value="{{ old('deposit_pay_price', $depositPayData->pay_date ?? '') }}">
                    @error('deposit_pay_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12 col-sm-3 mb-3 ">
                    <label class="form-label">尾款金額</label>
                    <input class="form-control" type="text" name="final_pay_price" placeholder="請輸入尾款金額"
                           value="{{ old('final_pay_price', $finalPayData->price ?? '') }}" aria-label="尾款金額">
                </div>
                <div class="col-12 col-sm-3 mb-3 ">
                    <label class="form-label">尾款付款日期(尾款日不可小於訂金日)</label>
                    <input type="date" class="form-control @error('final_pay_date') is-invalid @enderror"
                           name="final_pay_date" placeholder="尾款付款日期"
                           value="{{ old('final_pay_date', $finalPayData->pay_date ?? '') }}">
                    @error('final_pay_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-sm-6 mb-3 ">
                    <label class="form-label">運費(無外加運費填0)</label>
                    <input class="form-control" type="text" name="logistic_price" placeholder="請輸入運費金額"
                           value="{{ old('logistic_price', $data->logistic_price ?? '') }}" aria-label="運費">
                </div>
            </div>
        </div>

        <div id="submitDiv">
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
                <button type="reset" class="btn btn-outline-primary px-4">返回列表</button>
            </div>
        </div>
    </form>
@endsection
@once
    @push('sub-scripts')
        <script>
            let supplierList = @json($supplierList);
            $('#supplier').on('change', function (e) {
                if ("" != $('input[name=bank_cname]').val()
                    || "" != $('input[name=bank_code]').val()
                    || "" != $('input[name=bank_acount]').val()
                    || "" != $('input[name=bank_numer]').val()) {
                    if (confirm('下方已設定匯款資訊 是否根據所選廠商做變更?')) {
                        changeRemittance();
                    }
                } else {
                    changeRemittance();
                }
            });

            //變更匯款資料
            let changeRemittance = function () {
                let supplierID = $("#supplier").val();

                let supplierItem = null;
                for (i = 0; i < supplierList.length; i++) {
                    if (supplierList[i].id == supplierID) {
                        supplierItem = supplierList[i];
                        break;
                    }
                }

                if (null != supplierItem) {
                    $('input[name=bank_cname]').val(supplierItem.bank_cname);
                    $('input[name=bank_code]').val(supplierItem.bank_code);
                    $('input[name=bank_acount]').val(supplierItem.bank_acount);
                    $('input[name=bank_numer]').val(supplierItem.bank_numer);
                }
            };
        </script>
    @endpush
@endonce
