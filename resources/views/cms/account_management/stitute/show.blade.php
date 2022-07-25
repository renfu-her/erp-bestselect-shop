@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-3">代墊單</h2>
    <a href="{{ Route('cms.stitute.index') }}" class="btn btn-primary" role="button">
        <i class="bi bi-arrow-left"></i> 返回上一頁
    </a>
    {{--
    <a href="{{ route('cms.stitute.edit', ['id' => $stitute_order->id]) }}" class="btn btn-success px-4" role="button">修改</a>
    --}}
    @if(! $stitute_order->payment_date)
    <a href="{{ route('cms.stitute.po-edit', ['id' => $stitute_order->id]) }}" class="btn btn-primary px-4" role="button">付款</a>
    @endif
    {{--
    <button type="submit" class="btn btn-danger">中一刀列印畫面</button>
    <button type="submit" class="btn btn-danger">A4列印畫面</button>
    <button type="submit" class="btn btn-danger">修改記錄</button>
    <button type="submit" class="btn btn-danger">明細修改記錄</button>
    --}}

    <br>

    <div class="card shadow mb-4 -detail -detail-primary">
        <div class="card-body px-4">
            <h2>代墊單</h2>

            <dl class="row">
                <div class="col">
                    <dt>{{ $applied_company->company }}</dt>
                    <dd></dd>
                </div>
            </dl>

            <dl class="row">
                <div class="col">
                    <dt>地址：{{ $applied_company->address }}</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>電話：{{ $applied_company->phone }}</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>傳真：{{ $applied_company->fax }}</dt>
                    <dd></dd>
                </div>
            </dl>

            <dl class="row mb-0 border-top">
                <div class="col">
                    <dt>編號：{{ $stitute_order->sn }}</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>日期：{{ date('Y-m-d', strtotime($stitute_order->created_at)) }}</dt>
                    <dd></dd>
                </div>
            </dl>

            <dl class="row">
                <div class="col">
                    <dt>支付對象：{{ $stitute_order->client_name }}</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>承辦人：{{ $sales ? $sales->name : '' }}</dt>
                    <dd></dd>
                </div>
            </dl>

            <dl class="row mb-0">
                <div class="col">
                {{--
                    <dt>訂單流水號：<a href="{{ Route('cms.order.detail', ['id' => $order->id], true) }}">{{ $order->sn }}</a></dt>
                    <dd></dd>
                --}}
                </div>
                @if($stitute_order->payment_date)
                <div class="col">
                    <dt>付款日期：{{ date('Y-m-d', strtotime($stitute_order->payment_date)) }}</dt>
                    <dd></dd>
                </div>
                @endif
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
                            <td>{{ $stitute_grade->code . ' ' . $stitute_grade->name . ' ' . $stitute_order->summary }}</td>
                            <td>{{ $stitute_order->qty }}</td>
                            <td>{{ number_format($stitute_order->price, 2) }}</td>
                            <td>{{ number_format($stitute_order->total_price) }}</td>
                            <td>{{-- $stitute_order->taxation == 1 ? '應稅' : '免稅' --}}{{ $stitute_order->memo }}</td>
                        </tr>

                        <tr class="table-light">
                            <td>合計：</td>
                            <td></td>
                            <td>（{{ $zh_price }}）</td>
                            <td>{{ number_format($stitute_order->total_price) }}</td>
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
                    <dt>會計：{{ $accountant ? $accountant->name : '' }}</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>商品主管：</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>商品負責人：</dt>
                    <dd></dd>
                </div>
            </dl>
        </div>
    </div>
@endsection

@once
    @push('sub-scripts')
    @endpush
@endonce