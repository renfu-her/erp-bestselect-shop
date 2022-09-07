@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">收款單</h2>

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
            @can('cms.collection_received.edit')
                <a href="{{ route('cms.collection_received.edit', ['id' => $received_order->id]) }}" class="btn btn-sm btn-success px-3" role="button">修改</a>

                @if(! $received_order->receipt_date)
                <a href="{{ route('cms.request.ro-review', ['id' => $received_order->source_id]) }}" 
                    class="btn btn-sm btn-primary" role="button">收款單入款審核</a>
                @else
                    @if(! $data_status_check)
                    <a href="{{ route('cms.request.ro-review', ['id' => $received_order->source_id]) }}" 
                        class="btn btn-sm btn-outline-danger" role="button">取消入帳</a>
                    @endif
                @endif
                <a href="{{ route('cms.request.ro-taxation', ['id' => $received_order->source_id]) }}" 
                    class="btn btn-sm btn-dark" role="button">修改摘要/稅別</a>
            @endcan

            <a href="{{ url()->full() . '?action=print' }}" target="_blank" class="btn btn-sm btn-warning" 
                rel="noopener noreferrer">中一刀列印畫面</a>
            {{--
            <button type="submit" class="btn btn-danger">A4列印畫面</button>
            <button type="submit" class="btn btn-danger">修改記錄</button>
            <button type="submit" class="btn btn-danger">明細修改記錄</button>
            --}}

            @can('cms.collection_received.delete')
            @if(!$received_order->receipt_date && !$data_status_check)
                <a href="javascript:void(0)" role="button" data-bs-toggle="modal" data-bs-target="#confirm-delete"
                    data-href="{{ Route('cms.collection_received.delete', ['id' => $received_order->id], true) }}"
                    class="btn btn-sm btn-outline-danger">刪除收款單</a>
            @endif
            @endcan
        </div>
    </nav>

    <div class="card shadow p-4 mb-4">
        <div class="mb-3">
            <h4 class="text-center">喜鴻國際企業股份有限公司</h4>
            <div class="text-center small mb-2">
                <span>地址：台北市中山區松江路148號6樓之2</span>
                <span class="ms-3">電話：02-25637600</span>
                <span class="ms-3">傳真：02-25711377</span>
            </div>
            <h4 class="text-center">收　款　單</h4>
            <hr>

            <dl class="row mb-0">
                <div class="col">
                    <dd>客戶：{{ $received_order->drawee_name }}</dd>
                </div>
                <div class="col">
                    <dd>地址：{{ $received_order->drawee_address }}</dd>
                </div>
            </dl>
            <dl class="row mb-0">
                <div class="col">
                    <dd>電話：{{ $received_order->drawee_phone }}</dd>
                </div>
                <div class="col">
                    <dd>傳真：</dd>
                </div>
            </dl>
            <hr>

            <dl class="row mb-0">
                <div class="col">
                    <dd>收款單號：{{ $received_order->sn }}</dd>
                </div>
                <div class="col">
                    <dd>製表日期：{{ date('Y-m-d', strtotime($received_order->created_at)) }}</dd>
                </div>
            </dl>
            <dl class="row mb-0">
                <div class="col">
                    <dd>訂單流水號：</dd>
                </div>
                <div class="col">
                    @if($received_order->receipt_date)
                    <dd>入帳日期：{{ $received_order->receipt_date ? date('Y-m-d', strtotime($received_order->receipt_date)) : '' }}</dd>
                    @endif
                </div>
            </dl>
            <dl class="row mb-0">
                <div class="col">
                    <dd>收款對象：
                        {{--
                            <a href="{{ $supplierUrl }}" target="_blank">{{ $supplier->name }}</a>
                        --}}
                    </dd>
                </div>
                <div class="col">
                    <dd>承辦人：{{ $undertaker ? $undertaker->name : '' }}</dd>
                </div>
            </dl>
        </div>
        
        <div class="mb-2">
            <div class="table-responsive tableoverbox">
                <table class="table tablelist table-sm mb-0 align-middle">
                    <thead class="table-light text-secondary text-nowrap">
                        <tr>
                            <th scope="col">收款項目</th>
                            <th scope="col" class="text-end">數量</th>
                            <th scope="col" class="text-end">單價</th>
                            <th scope="col" class="text-end">應收金額</th>
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
                            <td class="text-end">{{ number_format($received_order->price) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="mb-3">
            @foreach($received_data as $value)
            <dl class="row mb-0">
                <div class="col-12">
                    <dd>
                        {{ $value->account->code . ' ' . $value->account->name }}
                        {{ number_format($value->credit_card_price ?? $value->tw_price) }}
                        @if($value->received_method == 'credit_card')
                            {{ '（' . $value->received_method_name . ' - ' . $value->credit_card_number . '（' . $value->credit_card_owner_name . '）' . '）' }}
                        @elseif($value->received_method == 'remit')
                            {{ '（' . $value->received_method_name . ' - ' . $value->summary . '（' . $value->remit_memo . '）' . '）' }}
                        @elseif($value->received_method == 'cheque')
                            {!! '（<a href="' . route('cms.note_receivable.record', ['id'=>$value->received_method_id]) . '">' . $value->received_method_name . ' - ' . $value->cheque_ticket_number . '（' . date('Y-m-d', strtotime($value->cheque_due_date)) . '）' . '</a>）' !!}
                        @else
                            {{ '（' . $value->received_method_name . ' - ' . $value->account->name . ' - ' . $value->summary . '）' }}
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
                    <dd>會計：{{ $accountant }}</dd>
                </div>
                <div class="col">
                    <dd>商品主管：</dd>
                </div>
                <div class="col">
                    <dd>商品負責人：{{-- $product_qc --}}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="col-auto">
        <a href="{{ route('cms.request.show', ['id' => $received_order->source_id]) }}" 
            class="btn btn-outline-primary px-4" role="button">返回 請款單</a>
        <a href="{{ Route('cms.request.index') }}" class="btn btn-outline-primary px-4" 
            role="button">返回 請款單列表</a>
    </div>

    <!-- Modal -->
    <x-b-modal id="confirm-delete">
        <x-slot name="title">刪除確認</x-slot>
        <x-slot name="body">確認要刪除此收款單？</x-slot>
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
