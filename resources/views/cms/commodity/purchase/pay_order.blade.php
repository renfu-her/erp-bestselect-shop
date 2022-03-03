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

    <button type="submit" class="btn btn-primary">修改</button>
    <button type="submit" class="btn btn-primary">修改備註</button>
    <button type="submit" class="btn btn-primary">新增細項</button>
    <button type="submit" class="btn btn-primary">變更支付對象</button>
    <button type="submit" class="btn btn-primary">取消訂金折抵</button>
    <button type="submit" class="btn btn-danger">中一刀列印畫面</button>
    <button type="submit" class="btn btn-danger">A4列印畫面</button>
    <button type="submit" class="btn btn-danger">圖片管理</button>
    <br>
    <form id="" method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        @error('id')
        <div class="alert alert-danger mt-3">{{ $message }}</div>
        @enderror

        <div class="card shadow mb-4 -detail -detail-primary">
            <div class="card-body px-4">
                <h2>付款單</h2>
                <dl class="row">
                    <div class="col">
                        <dt>喜鴻國際企業股份有限公司</dt>
                        <dd></dd>
                    </div>
                </dl>
                <dl class="row">
                    <div class="col">
                        <dt>地址：台北市中山區松江路148號6樓之2</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>電話：02-412-8618</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>傳真：02-412-8688</dt>
                        <dd></dd>
                    </div>
                </dl>
                <dl class="row mb-4 border-top">
                    <div class="col">
                        <dt>編號：201562</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>日期：2022-03-15</dt>
                        <dd></dd>
                    </div>
                </dl>
                <dl class="row mb-0">
                    <div class="col">
                        <dt>
                            採購單號：
                            <a href="{{ Route('cms.purchase.edit', ['id' => $id], true) }}"
                            >

                                B20220322
                            </a>

                        </dt>
                        <dd></dd>
                    </div>
                </dl>
                <dl class="row mb-0">
                    <div class="col">
                        <dt>支付對象：茶衣創意行銷有限公司</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>承辦人：葉秀盈</dt>
                        <dd></dd>
                    </div>
                </dl>
            </div>

            <div class="card-body px-4 py-2">
                <div class="table-responsive tableoverbox">
                    <table class="table tablelist table-sm mb-0">
                        <thead class="table-light text-secondary">
                        <tr>
                            <th scope="col">代墊項目</th>
                            <th scope="col">數量</th>
                            <th scope="col">單價</th>
                            <th scope="col">應付金額</th>
                            <th scope="col">備註</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>商品存貨-茶葉金禮盒銘版（茶衣創意）（16.80 * 80）（王小明）</td>
                            <td>80</td>
                            <td>16.80</td>
                            <td>{{ number_format(1344, 2) }}</td>
                            <td>純ERP系統出貨</td>
                        </tr>
                        <tr>
                            <td>商品存貨-茶葉金禮盒（茶衣創意）（126.00 * 80）（陳小佑）</td>
                            <td>80</td>
                            <td>126.00</td>
                            <td>{{ number_format(10800, 2) }}</td>
                            <td>12/17國際企業匯出</td>
                        </tr>
                        <tr>
                            <td>付款項目-訂金折抵（8400）-PSG0000452</td>
                            <td>1</td>
                            <td>{{ number_format(-8400, 2) }}</td>
                            <td>{{ number_format(8400, 2) }}</td>
                            <td></td>
                        </tr>
                        <tr class="table-light">
                            <td>合計：</td>
                            <td></td>
                            <td></td>
                            <td>{{ number_format(6384) }}</td>
                            <td></td>
                        </tr>
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
                        <dt>會計：</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>商品主管：</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>商品負責人：王小明、陳小佑</dt>
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
                <a href="" class="btn btn-primary px-4"
                   role="button">返回「付款作業」列表（會計專用）</a>
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

