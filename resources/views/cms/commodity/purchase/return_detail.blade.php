@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">#{{ $return->sn }} 採購退出明細</h2>

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pb-0">
            @if(! $received_order)
                <a href="{{ Route('cms.purchase.ro-edit', ['return_id' => $return->id]) }}" class="btn btn-sm btn-primary mb-1" role="button">新增收款單</a>
            @endif

            @if ($edit_check)
                <a class="btn btn-primary btn-sm mb-1"
                   href="{{ route('cms.purchase.return_edit', ['return_id' => $return->id]) }}">編輯退出單</a>
            @endif

            {{--
            <a target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm mb-1"
               href="{{ route('cms.purchase.print_return', ['return_id' => $return->id]) }}">
                列印退出單
            </a>
            --}}
        </div>
    </nav>

    <div class="card shadow p-4 mb-4">
        <h6>採購退出明細</h6>
        <dl class="row">
            <div class="col">
                <dt>採購退出單號</dt>
                <dd>{{ $return->sn ?? '' }}</dd>
            </div>
            <div class="col">
                <dt>狀態</dt>
                <dd>{{ \App\Enums\Purchase\ReturnStatus::getDescription($return->status ?? '') }}</dd>
            </div>
        </dl>
        <dl class="row">
            <div class="col">
                <dt>物流類型</dt>
                <dd>{{ $purchaseData->estimated_depot_name ?? '' }}</dd>
            </div>
            <div class="col">
                <dt>運費</dt>
                <dd>${{ (isset($purchaseData->logistics_price)) ? number_format($purchaseData->logistics_price) : '' }}</dd>
            </div>
        </dl>
        <dl class="row">
            <div class="col">
                <dt>客戶</dt>
                <dd>{{ $purchaseData->supplier_name ?? '' }}</dd>
            </div>
            <div class="col">
                <dt>客戶電話</dt>
                <dd>{{ $contact_tel ?? '' }}</dd>
            </div>
            <div class="col">
                <dt>新增者</dt>
                <dd>{{ $return->user_name ?? '' }}</dd>
            </div>
        </dl>
        <dl class="row">
            <div class="col">
                <dt>發票號碼</dt>
                <dd>{{ $purchaseData->invoice_num ?? '' }}</dd>
            </div>
            <div class="col">
                <dt>發票日期</dt>
                <dd>{{ $purchaseData->invoice_date ? date('Y/m/d', strtotime($purchaseData->invoice_date)) : '' }}</dd>
            </div>
        </dl>
        <dl class="row">
            <div class="col">
                <dt>進貨地址</dt>
                <dd>{{ $contact_address ?? '' }}</dd>
            </div>
        </dl>
        <dl class="row">
            <div class="col">
                <dt>入庫者</dt>
                <dd>{{ $return->inbound_user_name ?? '' }}</dd>
            </div>
        </dl>
        <dl class="row">
            <div class="col">
                <dt>退出單備註</dt>
                <dd>{{ $return->memo ?? '' }}</dd>
            </div>
            <div class="col">
                <dt>採購單號</dt>
                <dd>{{ $purchaseData->purchase_sn ?? '' }}</dd>
            </div>
        </dl>
        <dl class="row">
            <div class="col">
                <dt>收款單號</dt>
                <dd>
                    @if ($received_order)
                        @if($received_order->balance_date)
                            <a href="{{ route('cms.purchase.ro-receipt', ['return_id' => $return->id]) }}" class="-text">{{ $received_order->sn }}</a>
                        @else
                            <a href="{{ route('cms.purchase.ro-edit', ['return_id' => $return->id]) }}" class="-text">{{ $received_order->sn }}</a>
                        @endif
                    @else
                        尚未完成收款
                    @endif
                </dd>
            </div>
        </dl>
    </div>

    <div class="card shadow p-4 mb-4">
        <div class="table-responsive tableOverBox mb-3">
            <table class="table tableList table-striped mb-1">
                <thead class="small align-middle">
                    <tr>
                        <th scope="col" class="text-center" style="width:40px">#</th>
                        <th scope="col">品名規格</th>
                        <th scope="col" class="text-end">退款<br class="d-block d-lg-none">金額</th>
                        <th scope="col" class="text-center">退出<br class="d-block d-lg-none">數量</th>
                        <th scope="col" class="text-end">小計</th>
                        <th scope="col">說明</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total = 0;
                    @endphp
                    @foreach ($return_main_item as $key => $m_value)
                        @if(($m_value->show ?? null) == 1)
                            @php
                                $subtotal = $m_value->price * $m_value->qty;    // 退款金額 * 退出數量
                                $total += $subtotal;
                            @endphp
                            <tr>
                                <th scope="row">{{ $key + 1 }}</th>
                                <td class="wrap lh-sm">{{ $m_value->product_title ?? '' }}</td>
                                <td class="text-end">${{ number_format($m_value->price, 2) }}</td>
                                <td class="text-center">{{ number_format($m_value->qty) }}</td>
                                <td class="text-end">${{ number_format($subtotal) }}</td>
                                <td>{{ $m_value->memo ?? '' }}</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4">合計</th>
                        <td class="text-end">${{ number_format($total) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if (count($return_other_item) > 0)
        <div class="card shadow p-4 mb-4">
            <h6>其他項目</h6>
            <div class="table-responsive tableOverBox mb-3">
                <table class="table tableList table-striped mb-1">
                    <thead class="small align-middle">
                    <tr>
                        <th scope="col" class="text-center" style="width:40px">#</th>
                        <th scope="col">會計科目</th>
                        <th scope="col">項目</th>
                        <th scope="col" class="text-end">金額（單價）</th>
                        <th scope="col" class="text-center">數量</th>
                        <th scope="col">備註</th>
                    </tr>
                    </thead>
                    @php
                        $o_total = 0;
                    @endphp
                    <tbody>
                    @foreach ($return_other_item as $key => $o_value)
                        @php
                            $o_subtotal = $o_value->price * $o_value->qty;    // 退款金額 * 退出數量
                            $o_total += $o_subtotal;
                        @endphp
                        <tr>
                            <th scope="row">{{ $key + 1 }}</th>
                            <td class="wrap lh-sm">{{ $o_value->grade_code }} {{ $o_value->grade_name }}</td>
                            <td class="wrap lh-sm">{{ $o_value->product_title }}</td>
                            <td class="text-end">{{ $o_value->price }}</td>
                            <td class="text-center">{{ $o_value->qty }}</td>
                            <td>{{ $o_value->memo ?? '' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="3">合計</th>
                        <td class="text-end">${{ number_format($o_total) }}</td>
                        <td></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    @if (count($audited_item) > 0)
        <div class="card shadow p-4 mb-4">
            <h6>退出入庫清單</h6>
            <div class="table-responsive tableOverBox">
                <table class="table table-striped tableList">
                    <thead>
                        <tr>
                            <th style="width:3rem;">#</th>
                            <th>商品名稱</th>
                            <th>SKU</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($audited_item as $key => $a_value)
                            <tr>
                                <th scope="row">{{ $key + 1 }}</th>
                                <td><span class="badge rounded-pill bg-success">一般</span>{{ $a_value->product_title }}</td>
                                <td>{{ $a_value->sku }}</td>
                            </tr>

                            <tr>
                                <td></td>
                                <td colspan="2" class="pt-0 ps-0">
                                    <table class="table mb-0 table-sm table-hover border-start border-end">
                                        <thead>
                                        <tr class="border-top-0" style="border-bottom-color:var(--bs-secondary);">
                                            <td>入庫單</td>
                                            <td>倉庫</td>
                                            <td>效期</td>
                                            <td class="text-center" style="width: 10%">退出數量</td>
                                            <td>入庫說明</td>
                                        </tr>
                                        </thead>
                                        <tbody class="border-top-0 -appendClone --selectedIB">
                                        @foreach ($a_value->audited_items as $a_i_value)
                                            @if($a_i_value->inbound_return_num > 0)
                                                <tr class="-cloneElem --selectedIB">
                                                    <td data-td="sn">{{ $a_i_value->inbound_sn }}</td>
                                                    <td data-td="depot">{{ $a_i_value->depot_name }}</td>
                                                    <td data-td="expiry">{{ date('Y/m/d', strtotime($a_i_value->expiry_date)) }}</td>
                                                    <td class="text-center">{{ $a_i_value->qty }}</td>
                                                    <td>{{ $a_i_value->memo ?? '' }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="col-auto">
        <a href="{{ route('cms.purchase.return_list', ['purchase_id' => $return->purchase_id ]) }}" class="btn btn-outline-primary px-4" role="button">返回退出列表</a>
        <a href="{{ route('cms.purchase.edit', ['id' => $return->purchase_id]) }}" class="btn btn-outline-primary px-4" role="button">返回明細</a>
    </div>
@endsection

@once
    @push('sub-styles')
        <style>
            .tableList > thead > * > * {
                line-height: initial;
            }
        </style>
    @endpush
@endonce
