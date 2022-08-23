@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">請款單</h2>

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
            {{--
            <a href="{{ route('cms.request.edit', ['id' => $request_order->id]) }}" class="btn btn-success px-4" role="button">修改</a>
            --}}
            @if(! $request_order->posting_date)
            <a href="{{ route('cms.request.ro-edit', ['id' => $request_order->id]) }}" class="btn btn-sm btn-primary px-3" 
                role="button">入款</a>
            @endif
            {{--
            <button type="submit" class="btn btn-danger">中一刀列印畫面</button>
            <button type="submit" class="btn btn-danger">A4列印畫面</button>
            <button type="submit" class="btn btn-danger">修改記錄</button>
            <button type="submit" class="btn btn-danger">明細修改記錄</button>
            --}}
        </div>
    </nav>

    <div class="card shadow mb-4 -detail -detail-primary">
        <div class="card-body px-4">
            <h2>請款單</h2>

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
                    <dt>客戶：{{ $request_order->client_name }}</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>地址：{{ $request_order->client_address }}</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>電話：{{ $request_order->client_phone }}</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>傳真：{{ $request_order->client_fax }}</dt>
                    <dd></dd>
                </div>
            </dl>

            <dl class="row">
                <div class="col">
                    <dt>請款單號：{{ $request_order->sn }}</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>日期：{{ date('Y-m-d', strtotime($request_order->created_at)) }}</dt>
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
                @if($request_order->posting_date)
                <div class="col">
                    <dt>入帳日期：{{ date('Y-m-d', strtotime($request_order->posting_date)) }}</dt>
                    <dd></dd>
                </div>
                @endif
            </dl>

            {{--
            <dl class="row mb-0">
                <div class="col">
                    <dt>收款對象：
                        <a href="{{ $supplierUrl }}" target="_blank">{{ $supplier->name }}<span class="icon"><i class="bi bi-box-arrow-up-right"></i></span></a>
                    </dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>承辦人：{{ $sales ? $sales->name : '' }}</dt>
                    <dd></dd>
                </div>
            </dl>
            --}}
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
                        <tr>
                            <td>{{ $request_grade->code . ' ' . $request_grade->name . ' ' . $request_order->summary }}</td>
                            <td>{{ $request_order->qty }}</td>
                            <td>{{ number_format($request_order->price, 2) }}</td>
                            <td>{{ number_format($request_order->total_price) }}</td>
                            <td>{{ $request_order->taxation == 1 ? '應稅' : '免稅' }} {{ $request_order->memo }}</td>
                        </tr>

                        <tr class="table-light">
                            <td>合計：</td>
                            <td></td>
                            <td>（{{ $zh_price }}）</td>
                            <td>{{ number_format($request_order->total_price) }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-body px-4 pb-4">
            <dl class="row">
                <div class="col">
                    □支票　□匯款　□信用卡　□現金
                </div>
            </dl>
            <dl class="row">
                <div class="col">
                    匯款帳號：合作金庫(006) 長春分行 0844-871-001158 戶名：喜鴻國際企業股份有限公司
                </div>
            </dl>
            <dl class="row">
                <div class="col">
                    備註：
                    <br>
                    1.匯款戶名、支票抬頭請開：喜鴻國際企業股份有限公司
                    <br>
                    2.客戶應如期給付團費，如有違反或票據到期未兌現，願負法律責任，並放棄訴抗辯權。
                </div>
            </dl>
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
                    <dt>部門主管：</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>承辦人：</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>業務員：{{ $sales ? $sales->name : '' }}</dt>
                    <dd></dd>
                </div>
            </dl>
        </div>
    </div>
    
    <div class="col-auto">
        <a href="{{ Route('cms.request.index') }}" class="btn btn-outline-primary px-4" 
            role="button">返回上一頁</a>
    </div>
@endsection

@once
    @push('sub-scripts')
    @endpush
@endonce