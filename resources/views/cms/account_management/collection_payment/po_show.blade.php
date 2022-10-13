@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">付款單</h2>

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
            @can('cms.collection_payment.edit')
            <a href="{{ route('cms.collection_payment.edit', ['id' => $paying_order->id]) }}" class="btn btn-sm btn-success px-3" role="button">修改</a>
            @endcan

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
            <h4 class="text-center">付　款　單</h4>
            <hr>

            <dl class="row mb-0">
                <div class="col">
                    <dd>付款單號：{{ $paying_order->sn }}</dd>
                </div>
                <div class="col">
                    <dd>製表日期：{{ date('Y-m-d', strtotime($paying_order->created_at)) }}</dd>
                </div>
            </dl>

            <dl class="row mb-0">
                <div class="col">
                    <dd>單據編號：
                        @php
                            $i = 1;
                            $count = count($target_po);
                        @endphp
                        @foreach($target_po as $t_key => $t_value)
                        <a href="{{ $t_value }}">{{ $t_key }}</a>{{ $count != $i ? ' / ' : '' }}
                            @php
                                $i++;
                            @endphp
                        @endforeach
                    </dd>
                </div>
                <div class="col">
                    <dd>付款日期：{{ $paying_order->payment_date ? date('Y-m-d', strtotime($paying_order->payment_date)) : '' }}</dd>
                </div>
            </dl>

            <dl class="row mb-0">
                <div class="col">
                    <dd>支付對象：{{ $paying_order->payee_name }}</dd>
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
                        @foreach($target_items as $t_value)
                            @if($t_value->product_items)
                            @foreach(json_decode($t_value->product_items) as $data)
                            <tr>
                                <td>{{ isset($data->grade_code) && $data->grade_code ? $data->grade_code : $t_value->po_product_grade_code }} {{ isset($data->grade_name) && $data->grade_name ? $data->grade_name : $t_value->po_product_grade_name }} - {{ $data->title || $data->product_owner ? ($data->title . ($data->product_owner ? '（' . $data->product_owner . '）' : '') ) : $data->summary }}</td>
                                <td class="text-end">{{ number_format($data->num) }}</td>
                                <td class="text-end">${{ number_format($data->price / $data->num, 2) }}</td>
                                <td class="text-end">${{ number_format($data->price) }}</td>
                                <td>@php echo $data->memo ?? '' @endphp</td>
                            </tr>
                            @endforeach
                            @endif

                            @if($t_value->logistics_price > 0)
                            <tr>
                                <td>{{ $t_value->po_logistics_grade_code . ' ' . $t_value->po_logistics_grade_name . ' ' . $t_value->logistics_summary }}</td>
                                <td class="text-end">1</td>
                                <td class="text-end">${{ number_format($t_value->logistics_price, 2) }}</td>
                                <td class="text-end">${{ number_format($t_value->logistics_price) }}</td>
                                <td>@php echo $t_value->logistics_memo ?? '' @endphp</td>
                            </tr>
                            @endif

                            @if($t_value->discount_value > 0 && $t_value->order_discount)
                                @foreach(json_decode($t_value->order_discount) as $d_value)
                                <tr>
                                    <td>{{ $d_value->grade_code }} {{ $d_value->grade_name }} - {{ $d_value->title }}</td>
                                    <td class="text-end">1</td>
                                    <td class="text-end">-{{ number_format($d_value->discount_value, 2) }}</td>
                                    <td class="text-end">-{{ number_format($d_value->discount_value) }}</td>
                                    <td></td>
                                </tr>
                                @endforeach
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
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
    
    <div class="col-auto">
        {{--
        <a href="{{ $previous_url }}" class="btn btn-outline-primary px-4" role="button">
            返回上一頁
        </a>
        --}}
        @can('cms.collection_payment.index')
        <a href="{{ session('collection_payment_url') ?? route('cms.collection_payment.index') }}" class="btn btn-outline-primary px-4" role="button">
            返回 付款作業
        </a>
        @endcan
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
