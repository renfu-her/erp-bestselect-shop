@extends('layouts.main')
@section('sub-content')
    {{--    @if ($method === 'edit')--}}
    <h2 class="mb-3">收款單
    </h2>

    <form id="" method="post" action="{{ Route('cms.ar.review') }}">
        @method('POST')
        @csrf

        <button type="submit" class="btn btn-primary">收款單入帳</button>
        <input type="hidden" name="id[ord_orders]" value="{{ $customer->id ?? null }}">
        {{--    <button type="submit" class="btn btn-primary">修改備註</button>--}}
        {{--    <button type="submit" class="btn btn-primary">新增細項</button>--}}
        {{--    <button type="submit" class="btn btn-primary">變更支收對象</button>--}}
        {{--    <button type="submit" class="btn btn-primary">取消訂金折抵</button>--}}
        {{--    <button type="button" class="btn btn-danger">中一刀列印畫面</button>--}}
        {{--    <button type="button" class="btn btn-danger">A4列印畫面</button>--}}
        <br>
        @error('id')
        <div class="alert alert-danger mt-3">{{ $message }}</div>
        @enderror

        <div class="card shadow mb-4 -detail -detail-primary">
            <div class="card-body px-4">
                <h2>收款單</h2>
                <dl class="row">
                    <div class="col">
                        <dt>喜鴻國際企業股份有限公司</dt>
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
                        <dt>客戶：
                            {{ $customer->name }}
                        </dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>收款單號：{{ $payingOrderData->sn ?? '' }}</dt>
                        <dd></dd>
                    </div>
                </dl>

                <dl class="row mb-0">
                    <div class="col">
                        <dt>電話：{{ $customer->phone }}</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>製表日期：{{ $payingOrderData->created_at ?? ''}}</dt>
                        <dd></dd>
                    </div>
                </dl>
                <dl class="row mb-0">
                    <div class="col">
                        <dt>
                            地址：{{ $customer->address }}
                        </dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>入帳日期：</dt>
                        <dd></dd>
                    </div>
                </dl>
            </div>
            <div class="card-body px-4 py-2">
                <div class="table-responsive tableoverbox">
                    <table class="table tablelist table-sm mb-0">
                        <thead class="table-light text-secondary">
                        <tr>
                            <th scope="col">費用說明</th>
                            <th scope="col">數量</th>
                            <th scope="col">單價</th>
                            <th scope="col">金額</th>
                            <th scope="col">備註</th>
                        </tr>
                        </thead>
                        <tbody>
                        {{--                        @if($type === 'deposit')--}}
                        @foreach($products as $product)
                            <tr>
                                <td>
                                    {{ $product->product_title }}
                                    （訂單編號：
                                    {{ $product->sub_sn }}
                                    ）
                                </td>
                                <td>{{ $product->qty }}</td>
                                <td>{{ number_format($product->price) }}</td>
                                <td>{{ number_format($product->prd_total_price) }}</td>
                                <td>負責人：{{ $product->product_owner }}</td>
                            </tr>
                        @endforeach
                        @foreach($deliveries as $delivery)
                            <tr>
                                <td>
                                    物流運費
                                    （訂單編號：
                                    {{ $delivery->sub_sn }}
                                     ）
                                </td>
                                <td></td>
                                <td></td>
                                <td>{{ $delivery->dlv_fee }}</td>
                                <td></td>
                            </tr>
                        @endforeach

                        {{--                            <tr>--}}
                        {{--                                <td>{{ $productGradeName . '-' . $depositPaymentData->summary }}</td>--}}
                        {{--                                <td>1</td>--}}
                        {{--                                <td>{{ number_format($depositPaymentData->price, 2) }}</td>--}}
                        {{--                                <td>{{ number_format($depositPaymentData->price) }}</td>--}}
                        {{--                                <td>{{ $depositPaymentData->memo }}</td>--}}
                        {{--                            </tr>--}}
                        {{--                            <tr class="table-light">--}}
                        {{--                                <td>合計：</td>--}}
                        {{--                                <td></td>--}}
                        {{--                                <td></td>--}}
                        {{--                                <td>{{ number_format($depositPaymentData->price) }}</td>--}}
                        {{--                                <td></td>--}}
                        {{--                            </tr>--}}
                        {{--                        @elseif($type === 'final')--}}
                        {{--                            @foreach($purchaseItemData as $purchaseItem)--}}
                        {{--                                <tr>--}}
                        {{--                                    <td>{{ $productGradeName . '-' .$purchaseItem->title . '（負責人：' . $purchaseItem->name }}）</td>--}}
                        {{--                                    <td>{{ $purchaseItem->num }}</td>--}}
                        {{--                                    <td>{{ number_format($purchaseItem->total_price / $purchaseItem->num, 2) }}</td>--}}
                        {{--                                    <td>{{ number_format($purchaseItem->total_price) }}</td>--}}
                        {{--                                    <td>{{ $purchaseItem->memo }}</td>--}}
                        {{--                                </tr>--}}
                        {{--                            @endforeach--}}
                        {{--                            @if($logisticsPrice > 0)--}}
                        {{--                                <tr>--}}
                        {{--                                    <td>{{ $logisticsGradeName . '-物流費用' }}</td>--}}
                        {{--                                    <td></td>--}}
                        {{--                                    <td></td>--}}
                        {{--                                    <td>{{ $logisticsPrice }}</td>--}}
                        {{--                                    <td>{{ $purchaseData->logistics_memo }}</td>--}}
                        {{--                                </tr>--}}
                        {{--                            @endif--}}
                        {{--                            @if(!is_null($depositPaymentData))--}}
                        {{--                                <tr>--}}
                        {{--                                    <td>{{ $productGradeName }}-訂金抵扣（訂金收款單號{{ $depositPaymentData->sn }}）</td>--}}
                        {{--                                    <td>1</td>--}}
                        {{--                                    <td>-{{ number_format($depositPaymentData->price, 2) }}</td>--}}
                        {{--                                    <td>-{{ number_format($depositPaymentData->price) }}</td>--}}
                        {{--                                    <td>{{$depositPaymentData->memo}}</td>--}}
                        {{--                                </tr>--}}
                        {{--                            @endif--}}
                        <tr class="table-light text-black">
                            <td>合計：</td>
                            <td></td>
                            <td></td>
                            <td>{{ number_format($customer->total_price) }}</td>
                            <td>{{ $customer->note }}</td>
                        </tr>
                        {{--                        @endif--}}
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
                        <dt>會計：
                            @if($hasReviewed)
                                {{ $accountant ?? '' }}
                            @endif
                        </dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>商品主管：</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>商品負責人：{{ $productOwners }}</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>承辦：{{ $underTaker }}</dt>
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
                <a href="{{ Route('cms.ar.index') }}" class="btn btn-primary px-4"
                   role="button">返回「收款作業」列表</a>
                <a href="{{ Route('cms.order.detail', ['id' => $customer->id], true) }}"
                   class="btn btn-outline-primary px-4"
                   role="button">返回訂單明細</a>
            </div>
        </div>
    </form>
@endsection
@once
    @push('sub-scripts')
    @endpush
@endonce

