@extends('layouts.main')
@section('sub-content')
    {{--    @if ($method === 'edit')--}}
    <h2 class="mb-3">採購單
        {{--            {{ $purchaseData->purchase_sn }}--}}
    </h2>
    <x-b-pch-navi :id="$id"></x-b-pch-navi>
    {{--    @else--}}
    {{--        <h2 class="mb-3">新增採購單</h2>--}}
    {{--    @endif--}}

{{--    <button type="submit" class="btn btn-primary">修改</button>--}}
{{--    <button type="submit" class="btn btn-primary">修改備註</button>--}}
{{--    <button type="submit" class="btn btn-primary">新增細項</button>--}}
{{--    <button type="submit" class="btn btn-primary">變更支付對象</button>--}}
{{--    <button type="submit" class="btn btn-primary">取消訂金折抵</button>--}}
    @if($pay_off)
    {{--
    <a href="{{ Route('cms.ap.edit', ['payOrdId' => $payOrdId,
                                        'id' => $accountPayableId,
                                        'payOrdType' => 'pcs',
                                        'isFinalPay' => ($type === 'final' ? 1 : 0),
                                        'purchaseId' => $id], true) }}"
        class="btn btn-primary" role="button">編輯付款</a>
    --}}
    @else
        <a href="{{ Route('cms.ap.create', [
            'payOrdId' => $payOrdId,
            'payOrdType' => 'pcs',
            'isFinalPay' => ($type === 'final' ? 1 : 0),
            'purchaseId' => $id
        ], true) }}" class="btn btn-primary" role="button">付款</a>
    @endif
    <button type="submit" class="btn btn-danger">中一刀列印畫面</button>
    <button type="submit" class="btn btn-danger">A4列印畫面</button>
    <button type="submit" class="btn btn-danger">圖片管理</button>
    <br>
    <form id="" method="POST" action="{{ $formAction }}">
        @csrf

        @error('id')
        <div class="alert alert-danger mt-3">{{ $message }}</div>
        @enderror

        <div class="card shadow mb-4 -detail -detail-primary">
            <div class="card-body px-4">
                <h2>{{ $type === 'deposit' ? '訂金' : '尾款'}}付款單</h2>
                <dl class="row">
                    <div class="col">
                        <dt>{{ $appliedCompanyData->company }}</dt>
                        <dd></dd>
                    </div>
                </dl>
                <dl class="row">
                    <div class="col">
                        <dt>地址：{{ $appliedCompanyData->address }}</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>電話：{{ $appliedCompanyData->phone }}</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>傳真：{{ $appliedCompanyData->fax }}</dt>
                        <dd></dd>
                    </div>
                </dl>
                <dl class="row mb-0 border-top">
                    <div class="col">
                        <dt>付款單號：{{ $payingOrderData->sn }}</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>製表日期：{{ $payingOrderData->created_at }}</dt>
                        <dd></dd>
                    </div>
                </dl>
                <dl class="row mb-0">
                    <div class="col">
                        <dt>單據編號：<a href="{{ Route('cms.purchase.edit', ['id' => $id], true) }}">{{ $purchaseData->purchase_sn }}</a></dt>
                        <dd></dd>
                    </div>
                    @if($pay_off)
                    <div class="col">
                        <dt>付款日期：{{ $pay_off_date }}</dt>
                        <dd></dd>
                    </div>
                    @endif
                </dl>
                <dl class="row mb-0">
                    <div class="col">
                        <dt>支付對象：
                            <a href="{{ $supplierUrl }}"
                               target="_blank">
                                {{ $supplier->name }}
                                <span class="icon">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </span>
                            </a>
                        </dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>承辦人：{{ $undertaker }}</dt>
                        <dd></dd>
                    </div>
                </dl>
            </div>
            <div class="card-body px-4 py-2">
                <div class="table-responsive tableoverbox">
                    <table class="table tablelist table-sm mb-0">
                        <thead class="table-light text-secondary">
                        <tr>
                            <th scope="col">付款項目</th>
                            <th scope="col">數量</th>
                            <th scope="col">單價</th>
                            <th scope="col">應付金額</th>
                            <th scope="col">備註</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($type === 'deposit')
                            <tr>
                                <td>{{ $productGradeName . '-' . $depositPaymentData->summary }}</td>
                                <td>1</td>
                                <td>{{ number_format($depositPaymentData->price, 2) }}</td>
                                <td>{{ number_format($depositPaymentData->price) }}</td>
                                <td>{{ $depositPaymentData->memo }}</td>
                            </tr>
                            <tr class="table-light">
                                <td>合計：</td>
                                <td></td>
                                <td></td>
                                <td>{{ number_format($depositPaymentData->price) }}</td>
                                <td></td>
                            </tr>
                        @elseif($type === 'final')
                            @foreach($purchaseItemData as $purchaseItem)
                                <tr>
                                    <td>{{ $productGradeName . '-' .$purchaseItem->title . '（負責人：' . $purchaseItem->name }}）</td>
                                    <td>{{ $purchaseItem->num }}</td>
                                    <td>{{ number_format($purchaseItem->total_price / $purchaseItem->num, 2) }}</td>
                                    <td>{{ number_format($purchaseItem->total_price) }}</td>
                                    <td>{{ $purchaseItem->memo }}</td>
                                </tr>
                            @endforeach
                            @if($logisticsPrice > 0)
                                <tr>
                                    <td>{{ $logisticsGradeName . '- 物流費用' }}</td>
                                    <td></td>
                                    <td></td>
                                    <td>{{ number_format($logisticsPrice) }}</td>
                                    <td>{{ $purchaseData->logistics_memo }}</td>
                                </tr>
                            @endif
                            @if(!is_null($depositPaymentData))
                                <tr>
                                    <td>{{ $productGradeName }}-訂金抵扣（訂金付款單號{{ $depositPaymentData->sn }}）</td>
                                    <td>1</td>
                                    <td>-{{ number_format($depositPaymentData->price, 2) }}</td>
                                    <td>-{{ number_format($depositPaymentData->price) }}</td>
                                    <td>{{$depositPaymentData->memo}}</td>
                                </tr>
                            @endif
                            <tr class="table-light">
                                <td>合計：</td>
                                <td></td>
                                <td></td>
                                <td>{{ number_format($finalPaymentPrice) }}</td>
                                <td></td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                <dl class="row">
                    <div class="col">
                        <dt>財務主管：</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>會計：{{ $accountant ?? '' }}</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>商品主管：</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
{{--                        {{ dd($purchaseChargemanList) }}--}}
                        <dt>商品負責人：{{ $chargemen }}</dt>
                        <dd></dd>
                    </div>
                </dl>
            </div>
        </div>
        @error('del_error')
        <div class="alert alert-danger mt-3">{{ $message }}</div>
        @enderror

        <div id="submitDiv">
            <div class="col-auto">
                <input type="hidden" name="del_item_id">
                {{--                    <button type="submit" class="btn btn-primary px-4">儲存</button>--}}
                <a href="{{ Route('cms.ap.index') }}" class="btn btn-primary px-4"
                   role="button">返回「付款作業」列表</a>
                <a href="{{ Route('cms.purchase.edit', ['id' => $id], true) }}" class="btn btn-outline-primary px-4"
                   role="button">返回採購單資訊</a>
            </div>
        </div>
    </form>
@endsection
@once
    @push('sub-scripts')
    @endpush
@endonce

