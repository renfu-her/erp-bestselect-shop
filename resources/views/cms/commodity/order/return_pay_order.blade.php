@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">退貨付款單</h2>

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
            @can('cms.collection_payment.edit')
            <a href="{{ route('cms.collection_payment.edit', ['id' => $paying_order->id]) }}"
	    	class="btn btn-sm btn-success px-3" role="button">修改</a>
            @endcan

            @if(! $paying_order->balance_date)
                <a href="{{ Route('cms.order.return-pay-create', ['id' => $paying_order->source_id, 'sid' => $paying_order->source_sub_id]) }}"
                    class="btn btn-sm btn-primary px-3" role="button">付款</a>
            @endif

            @can('cms.collection_payment.delete')
            @if(! $data_status_check)
            @if(! ($paying_order->payment_date && $paying_order->append_po_id))
                <a href="{{ route('cms.collection_payment.payable_list', ['id' => $paying_order->id]) }}" class="btn btn-sm btn-primary" role="button">付款記錄</a>

                <a href="javascript:void(0)" role="button" class="btn btn-outline-danger btn-sm"
                    data-bs-toggle="modal" data-bs-target="#confirm-delete"
                    data-href="{{ Route('cms.collection_payment.delete', ['id' => $paying_order->id]) }}">刪除付款單</a>
            @endif
            @endif
            @endcan

            <a href="{{ url()->full() . '?action=print' }}" target="_blank"
                class="btn btn-sm btn-warning" rel="noopener noreferrer">中一刀列印畫面</a>
            @can('cms.order.edit-item')
                <a href="{{ Route('cms.order.edit-item', ['id' => $order->id]) }}" role="button"
                   class="btn btn-dark btn-sm my-1 ms-1">編輯訂單</a>

                <a href="{{ Route('cms.order.return-po-edit', ['id' => $order->id]) }}" role="button" class="btn btn-dark btn-sm my-1 ms-1">編輯付款項目備註</a>
            @endcan
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
            <h4 class="text-center">退貨付款單</h4>
            <hr>

            <dl class="row mb-0">
                <div class="col">
                    <dd>付款單號：{{ $paying_order->sn }}{!! $paying_order->append_po_id ? ' / ' . '<a href="' . $paying_order->append_po_link . '">' . $paying_order->append_po_sn . '</a>' : '' !!}</dd>
                </div>
                <div class="col">
                    <dd>製表日期：{{ date('Y-m-d', strtotime($paying_order->created_at)) }}</dd>
                </div>
            </dl>

            <dl class="row mb-0">
                <div class="col">
                    <dd>單據編號：</dd>
                </div>
                <div class="col">
                    <dd>付款日期：{{ $paying_order->payment_date ? date('Y-m-d', strtotime($paying_order->payment_date)) : '' }}</dd>
                </div>
            </dl>

            <dl class="row mb-0">
                <div class="col">
                    <dd>支付對象：
                        {{ $paying_order->payee_name }}
                    </dd>
                </div>
                <div class="col">
                    <dd>承辦人：{{ $undertaker ? $undertaker->name : '' }}</dd>
                </div>
            </dl>

            <dl class="row mb-0">
                <div class="col">
                    <dd>電話：{{ $paying_order->payee_phone }}</dd>
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
                        @foreach($sub_order as $so_value)
                        @foreach($so_value->items as $p_value)
                            <tr>
                                <td>{{ $product_grade_name }} - {{ $p_value->product_title }}{{'（' . $so_value->ship_event . ' - ' . $so_value->ship_category_name . '）'}}{{'（' . $p_value->price . ' * ' . $p_value->qty . '）'}}</td>
                                <td class="text-end">{{ $p_value->qty }}</td>
                                <td class="text-end">{{ number_format($p_value->price, 2) }}</td>
                                <td class="text-end">{{ number_format($p_value->total_price) }}</td>
                                <td>{{ $paying_order->memo }} <a href="{{ Route('cms.order.detail', ['id' => $order->id]) }}">{{ $order->sn }}</a> {{ $p_value->product_taxation == 1 ? '應稅' : '免稅' }} {{ $p_value->note }} {{ $p_value->po_note }}</td>
                            </tr>
                        @endforeach
                        @endforeach

                        @if($order->dlv_fee > 0)
                            <tr>
                                <td>{{ $logistics_grade_name }} - 物流費用</td>
                                <td class="text-end">1</td>
                                <td class="text-end">{{ number_format($order->dlv_fee, 2) }}</td>
                                <td class="text-end">{{ number_format($order->dlv_fee) }}</td>
                                <td>{{ $paying_order->memo }} <a href="{{ Route('cms.order.detail', ['id' => $order->id]) }}">{{ $order->sn }}</a> {{ $order->dlv_taxation == 1 ? '應稅' : '免稅' }}</td>
                            </tr>
                        @endif

                        @if($order->discount_value > 0)
                        @foreach($order_discount ?? [] as $d_value)
                            <tr>
                                <td>{{ $d_value->account_code }} {{ $d_value->account_name }} - {{ $d_value->title }}</td>
                                <td class="text-end">1</td>
                                <td class="text-end">-{{ number_format($d_value->discount_value, 2) }}</td>
                                <td class="text-end">-{{ number_format($d_value->discount_value) }}</td>
                                <td>{{ $paying_order->memo }} <a href="{{ Route('cms.order.detail', ['id' => $order->id]) }}">{{ $order->sn }}</a> {{ $d_value->discount_taxation == 1 ? '應稅' : '免稅' }}</td>
                            </tr>
                        @endforeach
                        @endif
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="3">
                                <div class="d-flex justify-content-between">
                                    <span>合計：</span>
                                    <span>（{{ $zh_price }}）</span>
                                </div>
                            </td>
                            <td class="text-end">{{ number_format($paying_order->price) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
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
                    <dd>商品負責人：</dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Modal -->
    <x-b-modal id="confirm-delete">
        <x-slot name="title">刪除確認</x-slot>
        <x-slot name="body">確認要刪除此付款單？</x-slot>
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
