@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-3">付款單</h2>
    <a href="{{ route('cms.accounts_payable.index') }}" class="btn btn-primary" role="button">
        <i class="bi bi-arrow-left"></i> 返回上一頁
    </a>

    <a href="{{ url()->full() . '?method=print' }}" target="_blank" class="btn btn-danger" rel="noopener noreferrer">中一刀列印畫面</a>
    <button type="submit" class="btn btn-danger">A4列印畫面</button>
    <button type="submit" class="btn btn-danger">圖片管理</button>
    <br>
    <form id="" method="POST" action="">
        @csrf
        <div class="card shadow mb-4 -detail -detail-primary">
            <div class="card-body px-4">
                <h2>付款單</h2>

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
                        <dt>客戶：{{ $paying_order->payee_name }}</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>編號：{{ $paying_order->sn }}</dt>
                        <dd></dd>
                    </div>
                </dl>

                <dl class="row mb-0">
                    <div class="col">
                        <dt>電話：{{ $paying_order->payee_phone }}</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>日期：{{ date('Y-m-d', strtotime($paying_order->created_at)) }}</dt>
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
                            @foreach($target_items as $value)
                            <tr>
                                <td>{{ $value->po_payable_grade_code . ' ' . $value->po_payable_grade_name . ' ' . $value->summary }}</td>
                                <td>1</td>
                                <td>{{ number_format($value->tw_price, 2) }}</td>
                                <td>{{ number_format($value->account_amt_net) }}</td>
                                <td>{{ $value->taxation == 1 ? '應稅' : '免稅' }} {{ $value->note }}</td>
                            </tr>
                            @endforeach
                            <tr class="table-light">
                                <td>合計：</td>
                                <td></td>
                                <td>（{{ $zh_price }}）</td>
                                <td>{{ number_format($paying_order->price) }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-body px-4 pb-4">
                @foreach($payable_data as $value)
                <dl class="row">
                    <div class="col">
                        <dt></dt>
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

            <div class="card-body px-4 pb-4">
                <dl class="row">
                    <div class="col">
                        <dt>財務主管：</dt>
                        <dd></dd>
                    </div>
                    <div class="col">
                        <dt>會計：{{ $accountant }}</dt>
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
                    <div class="col">
                        <dt>承辦人：{{ $undertaker ? $undertaker->name : '' }}</dt>
                        <dd></dd>
                    </div>
                </dl>
            </div>
        </div>
    </form>
@endsection

@once
    @push('sub-scripts')

    @endpush
@endonce
