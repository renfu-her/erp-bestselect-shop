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

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
            @if(!$pay_off)
                <a href="{{ Route('cms.purchase.po-create', [
                    'payOrdId' => $payOrdId,
                    'payOrdType' => 'pcs',
                    'isFinalPay' => ($type === 'final' ? 1 : 0),
                    'purchaseId' => $id
                ], true) }}" class="btn btn-sm btn-primary px-3" role="button">付款</a>
            @endif

            {{-- <button type="button" class="btn btn-sm btn-primary">圖片管理</button> --}}
            <a href="{{ url()->full() . '&action=print' }}" target="_blank"
                class="btn btn-sm btn-warning" rel="noopener noreferrer">中一刀列印畫面</a>
            {{-- <a href="#" target="_blank" class="btn btn-sm btn-warning" rel="noopener noreferrer">A4列印畫面</a> --}}

            {{-- <button type="button" class="btn btn-primary">修改</button> --}}
            {{-- <button type="button" class="btn btn-primary">修改備註</button> --}}
            {{-- <button type="button" class="btn btn-primary">新增細項</button> --}}
            {{-- <button type="button" class="btn btn-primary">變更支付對象</button> --}}
            {{-- <button type="button" class="btn btn-primary">取消訂金折抵</button> --}}
        </div>
    </nav>

    <form id="" method="POST" action="{{ $formAction }}">
        @csrf

        <div class="card shadow p-4 mb-4">
            <div class="mb-3">
                <h4 class="text-center">{{ $appliedCompanyData->company }}</h4>
                <div class="text-center small mb-2">
                    <span>地址：{{ $appliedCompanyData->address }}</span>
                    <span class="ms-3">電話：{{ $appliedCompanyData->phone }}</span>
                    <span class="ms-3">傳真：{{ $appliedCompanyData->fax }}</span>
                </div>
                <h4 class="text-center">{{ $type === 'deposit' ? '訂金' : '尾款'}}付款單</h4>
                <hr>

                <dl class="row mb-0">
                    <div class="col">
                        <dd>付款單號：{{ $payingOrderData->sn }}</dd>
                    </div>
                    <div class="col">
                        <dd>製表日期：{{ date('Y/m/d', strtotime($payingOrderData->created_at)) }}</dd>
                    </div>
                </dl>
                <dl class="row mb-0">
                    <div class="col">
                        <dd>單據編號：<a href="{{ Route('cms.purchase.edit', ['id' => $id], true) }}">{{ $purchaseData->purchase_sn }}</a></dd>
                    </div>
                    <div class="col">
                        <dd>
                        @if($pay_off)
                            付款日期：{{ $pay_off_date }}
                        @endif
                        </dd>
                    </div>
                </dl>
                <dl class="row mb-0">
                    <div class="col">
                        <dd>支付對象：
                            <a href="{{ $supplierUrl }}" target="_blank">
                                {{ $supplier->name }} <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </dd>
                    </div>
                    <div class="col">
                        <dd>承辦人：{{ $undertaker }}</dd>
                    </div>
                </dl>
            </div>

            <div class="mb-2">
                <div class="table-responsive tableoverbox">
                    <table class="table tablelist table-sm mb-0 align-middle">
                        <thead class="table-light text-secondary text-nowrap">
                            <tr>
                                <th scope="col">付款項目</th>
                                <th scope="col" class="text-end">數量</th>
                                <th scope="col" class="text-end">單價</th>
                                <th scope="col" class="text-end">應付金額</th>
                                <th scope="col">備註</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if($type === 'deposit')
                            <tr>
                                <td>{{ $productGradeName . '-' . $depositPaymentData->summary }}</td>
                                <td class="text-end">1</td>
                                <td class="text-end">{{ number_format($depositPaymentData->price, 2) }}</td>
                                <td class="text-end">{{ number_format($depositPaymentData->price) }}</td>
                                <td>{{ $depositPaymentData->memo }}</td>
                            </tr>
                        @elseif($type === 'final')
                            @foreach($purchaseItemData as $purchaseItem)
                                <tr>
                                    <td>{{ $productGradeName . '-' .$purchaseItem->title . '（負責人：' . $purchaseItem->name }}）</td>
                                    <td class="text-end">{{ $purchaseItem->num }}</td>
                                    <td class="text-end">{{ number_format($purchaseItem->total_price / $purchaseItem->num, 2) }}</td>
                                    <td class="text-end">{{ number_format($purchaseItem->total_price) }}</td>
                                    <td>{{ $purchaseItem->memo }}</td>
                                </tr>
                            @endforeach
                            @if($logisticsPrice > 0)
                                <tr>
                                    <td>{{ $logisticsGradeName . '- 物流費用' }}</td>
                                    <td class="text-end"></td>
                                    <td class="text-end"></td>
                                    <td class="text-end">{{ number_format($logisticsPrice) }}</td>
                                    <td>{{ $purchaseData->logistics_memo }}</td>
                                </tr>
                            @endif
                            @if(!is_null($depositPaymentData))
                                <tr>
                                    <td>{{ $productGradeName }}-訂金抵扣（訂金付款單號{{ $depositPaymentData->sn }}）</td>
                                    <td class="text-end">1</td>
                                    <td class="text-end">-{{ number_format($depositPaymentData->price, 2) }}</td>
                                    <td class="text-end">-{{ number_format($depositPaymentData->price) }}</td>
                                    <td>{{$depositPaymentData->memo}}</td>
                                </tr>
                            @endif
                        @endif
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3">
                                    <div class="d-flex justify-content-between">
                                        <span>合計：</span>
                                        <span>（{{ $zh_price }}）</span>
                                    </div>
                                </td>
                                <td class="text-end">
                                    @if ($type === 'deposit')
                                        {{ number_format($depositPaymentData->price) }}
                                    @elseif($type === 'final')
                                        {{ number_format($finalPaymentPrice) }}
                                    @endif
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="mb-3">
                @foreach($payable_data as $value)
                <dl class="row mb-0">
                    <div class="col-12">
                        <dd>
                            {{ $value->account->code . ' ' . $value->account->name }}
                            {{ number_format($value->tw_price) }}
                            @if($value->acc_income_type_fk == 3)
                                {{ '（' . $value->payable_method_name . ' - ' . $value->summary . '）' }}
                            @elseif($value->acc_income_type_fk == 2)
                                {!! '（<a href="' . route('cms.note_payable.record', ['id'=>$value->payable_method_id]) . '">' . $value->payable_method_name . ' ' . $value->cheque_ticket_number . '（' . date('Y-m-d', strtotime($value->cheque_due_date)) . '）' . '</a>）' !!}
                            @else
                                {{ '（' . $value->payable_method_name . ' - ' . $value->account->name . ' - ' . $value->summary . '）' }}
                            @endif
                        </dd>
                    </div>
                </dl>
                @endforeach
            </div>

            <div>
                <dl class="row">
                    <div class="col">
                        <dd>財務主管：</dd>
                    </div>
                    <div class="col">
                        <dd>會計：{{ $accountant ?? '' }}</dd>
                    </div>
                    <div class="col">
                        <dd>商品主管：</dd>
                    </div>
                    <div class="col">
                        <dd>商品負責人：{{ $chargemen }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        @error('id')
        <div class="alert alert-danger mt-2">{{ $message }}</div>
        @enderror
        @error('del_error')
        <div class="alert alert-danger mt-2">{{ $message }}</div>
        @enderror

        <div class="col-auto">
            <input type="hidden" name="del_item_id">
            {{-- <button type="submit" class="btn btn-primary px-4">儲存</button>--}}
            <a href="{{ Route('cms.collection_payment.index') }}" class="btn btn-outline-primary px-4"
                role="button">返回 付款作業列表</a>
            <a href="{{ Route('cms.purchase.edit', ['id' => $id], true) }}" class="btn btn-outline-primary px-4"
                role="button">返回 採購單資訊</a>
        </div>
    </form>
@endsection
@once
    @push('sub-scripts')
    @endpush
@endonce

