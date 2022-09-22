@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">請款單</h2>

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
            @can('cms.request.edit')
            <a href="{{ route('cms.request.edit', ['id' => $request_order->id]) }}" class="btn btn-sm btn-success px-3" role="button">修改</a>
            @endcan

            @if(! $request_order->ro_receipt_date)
            <a href="{{ route('cms.request.ro-edit', ['id' => $request_order->id]) }}" class="btn btn-sm btn-primary px-3" 
                role="button">入款</a>
            @endif
            {{--
            <button type="submit" class="btn btn-danger">A4列印畫面</button>
            --}}
            @can('cms.request.delete')
            @if(! $request_order->received_order_id)
            <a href="javascript:void(0)" role="button" class="btn btn-outline-danger btn-sm"
                data-bs-toggle="modal" data-bs-target="#confirm-delete"
                data-href="{{ Route('cms.request.delete', ['id' => $request_order->id]) }}">刪除請款單</a>
            @endif
            @endcan

            <a href="{{ url()->full() . '?action=print' }}" target="_blank" 
                class="btn btn-sm btn-warning" rel="noopener noreferrer">中一刀列印畫面</a>
        </div>
    </nav>

    <div class="card shadow p-4 mb-4">
        <div class="mb-3">
            <h4 class="text-center">{{ $applied_company->company }}</h4>
            <div class="text-center small mb-2">
                <span>地址：{{ $applied_company->address }}</span>
                <span class="ms-3">電話：{{ $applied_company->phone }}</span>
                <span class="ms-3">傳真：{{ $applied_company->fax }}</span>
            </div>
            <h4 class="text-center">請款單</h4>
            <hr>

            <dl class="row mb-0">
                <div class="col">
                    <dd>客戶：{{ $request_order->client_name }}</dd>
                </div>
                <div class="col">
                    <dd>地址：{{ $request_order->client_address }}</dd>
                </div>
            <dl class="row mb-0">
            </dl>
                <div class="col">
                    <dd>電話：{{ $request_order->client_phone }}</dd>
                </div>
                <div class="col">
                    <dd>傳真：{{ $request_order->client_fax }}</dd>
                </div>
            </dl>

            <dl class="row mb-0">
                <div class="col">
                    <dd>請款單號：{{ $request_order->sn }}</dd>
                </div>
                <div class="col">
                    <dd>日期：{{ date('Y/m/d', strtotime($request_order->created_at)) }}</dd>
                </div>
            </dl>

            <dl class="row mb-0">
                <div class="col">
                {{--
                    <dd>訂單流水號：<a href="{{ Route('cms.order.detail', ['id' => $order->id], true) }}">{{ $order->sn }}</a></dd>
                --}}
                </div>
                <div class="col">
                    <dd>入帳日期：{{ $request_order->posting_date ? date('Y/m/d', strtotime($request_order->posting_date)) : '' }}</dd>
                </div>
            </dl>

        </div>

        <div class="mb-2">
            <div class="table-responsive tableoverbox">
                <table class="table tablelist table-sm mb-0 align-middle">
                    <thead class="table-light text-secondary text-nowrap">
                        <tr>
                            <th scope="col">費用說明</th>
                            <th scope="col" class="text-end">數量</th>
                            <th scope="col" class="text-end">單價</th>
                            <th scope="col" class="text-end">金額</th>
                            <th scope="col">備註</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $request_grade->code . ' ' . $request_grade->name . ' ' . $request_order->summary }}</td>
                            <td class="text-end">{{ $request_order->qty }}</td>
                            <td class="text-end">{{ number_format($request_order->price, 2) }}</td>
                            <td class="text-end">{{ number_format($request_order->total_price) }}</td>
                            <td>{{ $request_order->taxation == 1 ? '應稅' : '免稅' }} {{ $request_order->memo }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3">
                                <div class="d-flex justify-content-between">
                                    <span>合計：</span>
                                    <span>（{{ $zh_price }}）</span>
                                </div>
                            </td>
                            <td class="text-end">{{ number_format($request_order->total_price) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="mb-3">
            <dl class="row">
                <div class="col">□支票</div>
                <div class="col">□匯款</div>
                <div class="col">□信用卡</div>
                <div class="col">□現金</div>
            </dl>
            <dl class="row">
                <div class="col-auto">
                    匯款帳號：合作金庫(006) 長春分行 0844-871-001158
                </div>
                <div class="col-auto">戶名：喜鴻國際企業股份有限公司</div>
            </dl>
            <dl class="row">
                <div class="col small">
                    <dd class="mb-0">備註：</dd>
                    <dd>
                        <ol>
                            <li>匯款戶名、支票抬頭請開：喜鴻國際企業股份有限公司</li>
                            <li>客戶應如期給付團費，如有違反或票據到期未兌現，願負法律責任，並放棄訴抗辯權。</li>
                        </ol>
                    </dd>
                </div>
            </dl>
        </div>

        <div>
            <dl class="row">
                <div class="col">
                    <dd>財務主管：</dd>
                </div>
                <div class="col">
                    <dd>會計：{{ $accountant ? $accountant->name : '' }}</dd>
                </div>
                <div class="col">
                    <dd>部門主管：</dd>
                </div>
                <div class="col">
                    <dd>承辦人：</dd>
                </div>
                <div class="col">
                    <dd>業務員：{{ $sales ? $sales->name : '' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="col-auto">
        <a href="{{ Route('cms.request.index') }}" class="btn btn-outline-primary px-4" 
            role="button">返回上一頁</a>
    </div>

    <!-- Modal -->
    <x-b-modal id="confirm-delete">
        <x-slot name="title">刪除確認</x-slot>
        <x-slot name="body">確認要刪除此請款單？</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認並刪除</a>
        </x-slot>
    </x-b-modal>
@endsection

@once
    @push('sub-scripts')
        <script>
            // Modal Control
            $('#confirm-delete').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
        </script>
    @endpush
@endonce