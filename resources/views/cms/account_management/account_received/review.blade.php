@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a
            {{--            href="{{ Route('cms.ar.receipt', [], true) }}"--}}
            class="btn btn-primary"
            role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>

    <div class="card mb-4">
        <h2 class="mx-3 my-3">收款單入款審核</h2>
        <div class="card-body">
            <div class="col-12 mb-3">
                <label class="form-label">收款單號：</label>
                PCS0001
            </div>
            <div class="col-12 mb-3">
                <label class="form-label">承辦者：</label>
                王大明
            </div>
            <div class="col-3 mb-3">
                <div class="input-group">
                    <div><span class="text-danger">*</span>審核日期：</div>
                    <input type="date" class="form-control -startDate @error('pay_date') is-invalid @enderror"
                           name="pay_date"
                           value="{{ old('pay_date', (isset($supplierData)?($supplierData->pay_date ? explode(' ', $supplierData->pay_date)[0] : ''): '')) }}"
                           aria-label="付款日"/>
                    <div class="invalid-feedback">
                        @error('pay_date')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>
            <div class="col-3 mb-3">
                <div class="input-group">
                    <div><span class="text-danger">*</span>發票號碼：</div>
                    <input type="text" class="form-control -startDate @error('pay_date') is-invalid @enderror"
                           name="pay_date"
                           value="{{ old('pay_date', (isset($supplierData)?($supplierData->pay_date ? explode(' ', $supplierData->pay_date)[0] : ''): '')) }}"
                           aria-label="付款日"/>
                    <div class="invalid-feedback">
                        @error('pay_date')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive tableoverbox">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th scope="col"></th>
                    <th scope="col">借</th>
                    <th scope="col">借方金額</th>
                    <th scope="col">貸</th>
                    <th scope="col">貸方金額</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <th scope="row"></th>
                    <td>test</td>
                    <td>{{ number_format(12345) }}</td>
                    <td>test</td>
                    <td>{{ number_format(12345) }}</td>
                </tr>
                <tr class="table-light">
                    <td>合計：</td>
                    <td></td>
                    <td>{{ number_format(12345) }}</td>
                    <td></td>
                    <td>{{ number_format(12345) }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div>
        <button type="submit" class="btn btn-primary px-4">確認</button>
        <a onclick="history.back()"
           class="btn btn-outline-primary px-4"
           role="button">取消</a>
    </div>

@endsection

@once
@endonce
