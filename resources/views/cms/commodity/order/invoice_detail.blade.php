@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-4">發票資訊</h2>

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pe-2">
            @can('cms.order_invoice_manager.index')
                {{-- @if($invoice->status == 1 && is_null($invoice->r_status)) --}}
                @if($invoice->status == 1 && $invoice->r_status != 'SUCCESS')
                    <a href="javascript:void(0)" role="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" 
                        data-bs-target="#confirm-issue-invoice" data-href="{{ Route('cms.order.send-invoice', ['id' => $invoice->id, 'action' => 'issue']) }}">
                        開立發票
                    </a>

                    <a href="{{ Route('cms.order.edit-invoice', ['id' => $invoice->id]) }}" class="btn btn-sm btn-primary" role="button">編輯發票</a>

                @elseif($invoice->status == 1 && $invoice->r_status == 'SUCCESS')
                    @if($invoice->print_flag == 'Y')
                        <a href="{{ url()->full() . '?action=print_inv_a4' }}" target="_blank" 
                            class="btn btn-sm btn-warning">發票列印(單張)</a>

                        @if($invoice->category == 'B2B')
                        <a href="{{ url()->full() . '?action=print_inv_B2B' }}" target="_blank" 
                            class="btn btn-sm btn-warning">發票列印(B2B)</a>
                        @endif
                    @endif

                    @if($invoice->r_invalid_status != 'SUCCESS')
                        @if($check_inv_allowance)
                            <a href="{{ Route('cms.order.allowance-invoice', ['id' => $invoice->id]) }}" class="btn btn-sm btn-success" role="button">發票折讓</a>
                        @endif

                        @if($check_invoice_invalid)
                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#confirm-invalid-invoice">發票作廢</button>
                        @endif
                    @endif
                @endif
            @endcan
        </div>
    </nav>

    <div class="card shadow p-4 mb-4">
        <dl class="row border-bottom mx-0">
            <div class="col">
                <dd>訂單編號：{{ $invoice->merchant_order_no  }}</dd>
            </div>
        </dl>
        <dl class="row border-bottom mx-0">
            <div class="col">
                <dd>買受人：{{ $invoice->buyer_name }}</dd>
            </div>
            <div class="col">
                <dd>發票號碼：{{ $invoice->invoice_number }}</dd>
            </div>
        </dl>
        <dl class="row border-bottom mx-0">
            <div class="col">
                <dd>統一編號：{{ $invoice->buyer_ubn }}</dd>
            </div>
            <div class="col">
                <dd>開立日期：{{ date('Y/m/d', strtotime($invoice->created_at)) }}</dd>
            </div>
        </dl>
        <dl class="row border-bottom mx-0">
            <div class="col">
                <dd>電子郵件：{{ $invoice->buyer_email }}</dd>
            </div>
            <div class="col">
                <dd>發票類型：{{ $invoice->category }}</dd>
            </div>
        </dl>
        <dl class="row border-bottom mx-0">
            <div class="col">
                <dd>地址：{{ $invoice->buyer_address }}</dd>
            </div>
            <div class="col">
                <dd>經手人：{{ $handler ? $handler->name : '' }}</dd>
            </div>
        </dl>
        <dl class="row border-bottom mx-0">
            <div class="col">
                <dd>銷售金額 / 稅金：{{ number_format($invoice->amt) }} / {{ number_format($invoice->tax_amt) }}</dd>
            </div>
            <div class="col">
                <dd>發票金額（含稅）：{{ number_format($invoice->total_amt) }}</dd>
            </div>
        </dl>
        <dl class="row mx-0">
            <div class="col">
                <dd>發票應稅金額：{{ number_format($invoice->amt_sales) }}</dd>
            </div>
            <div class="col">
                <dd>發票免稅金額：{{ number_format($invoice->amt_free) }}</dd>
            </div>
        </dl>

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle d-sm-table d-none text-nowrap mb-0">
                <thead>
                    <tr class="table-light">
                        <th>摘要</th>
                        <th class="col-2">單價</th>
                        <th class="col-2">數量</th>
                        <th class="col-2">金額</th>
                        <th class="col-2">稅別</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @php
                        $item_name_arr = explode('|', $invoice->item_name);
                        $item_count_arr = explode('|', $invoice->item_count);
                        $item_price_arr = explode('|', $invoice->item_price);
                        $item_amt_arr = explode('|', $invoice->item_amt);
                        $item_tax_type_arr = explode('|', $invoice->item_tax_type);
                    @endphp
                    @foreach($item_name_arr as $key => $value)
                    <tr>
                        <td>{{ $value }}</td>
                        <td>{{ number_format($item_price_arr[$key]) }}</td>
                        <td>{{ $item_count_arr[$key] }}</td>
                        <td>{{ number_format($item_amt_arr[$key]) }}</td>
                        <td>{{ $item_tax_type_arr[$key] == 1 ? '應稅' : '免稅' }}</td>
                    </tr>
                    @endforeach
                    <tr class="table-light">
                        <td colspan="3">發票金額</td>
                        <td>{{ number_format($invoice->total_amt) }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @if($inv_allowance->count() > 0)
        <div class="card shadow p-4 mb-4">
            <h6>發票折讓記錄</h6>

            @foreach($inv_allowance as $index => $value)
                <table class="table table-bordered align-middle small">
                    <tbody>
                        <tr class="table-light text-center">
                            <th rowspan="4">{{ $index + 1 }}</th>
                        </tr>
                        <tr>
                            <th class="table-light" style="width:73px">折讓單號</th>
                            <td style="min-width: 150px;">{{ $value->allowance_no ? $value->allowance_no : '-' }}</td>
                            <th class="table-light" style="width:101px">買受人E-mail</th>
                            <td>{{ $value->buyer_email ? $value->buyer_email : '-' }}</td>
                        </tr>
                        <tr>
                            <th class="table-light">折讓日期</th>
                            <td>{{ $value->r_status == 'SUCCESS' ? date('Y/m/d', strtotime($value->created_at)) : '-' }}</td>
                            <th class="table-light">作廢折讓日期</th>
                            <td>{{ $value->r_invalid_status == 'SUCCESS' ? date('Y/m/d', strtotime($value->deleted_at)) : '-' }}</td>
                        </tr>

                        <tr>
                            <td colspan="4">
                                <button type="button" class="btn btn-sm btn-info"
                                    data-bs-toggle="modal" data-bs-target="#detail-{{ $index }}">折讓商品明細</button>
                                @if($value->r_status != 'SUCCESS')
                                    <a href="javascript:void(0)" role="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" 
                                        data-bs-target="#confirm-allowance-issue" data-href="{{ route('cms.order.send-invoice', ['id' => $value->invoice_id, 'action' => 'allowance_issue', 'allowance_id' => $value->id]) }}">
                                        開立折讓
                                    </a>
                                @elseif($value->r_status == 'SUCCESS')
                                    <button type="button" class="btn btn-sm btn-outline-danger allowance-invalid" data-bs-toggle="modal" data-bs-target="#confirm-allowance-invalid" data-action="{{ route('cms.order.send-invoice', ['id' => $value->invoice_id, 'action' => 'allowanceInvalid', 'allowance_id' => $value->id]) }}">作廢折讓</button>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>

                <x-b-modal id="detail-{{ $index }}" size="modal-dialog-scrollable modal-xl modal-fullscreen-lg-down">
                    <x-slot name="title">折讓商品明細</x-slot>
                    <x-slot name="body">
                        <div class="table-responsive">
                            <table class="table table-bordered text-center align-middle d-sm-table d-none text-nowrap mb-0">
                                <thead>
                                    <tr class="table-light">
                                        <th class="col-2">品名</th>
                                        <th class="col-2">單價</th>
                                        <th class="col-2">數量</th>
                                        <th class="col-2">金額</th>
                                        <th class="col-2">營業稅額</th>
                                        <th class="col-2">稅別</th>
                                    </tr>
                                </thead>
                                <tbody class="border-top-0">
                                    @php
                                        $item_name_arr = explode('|', $value->item_name);
                                        $item_count_arr = explode('|', $value->item_count);
                                        $item_price_arr = explode('|', $value->item_price);
                                        $item_amt_arr = explode('|', $value->item_amt);
                                        $item_tax_type_arr = explode('|', $value->item_tax_type);
                                        $item_tax_amt_arr = explode('|', $value->item_tax_amt);
                                    @endphp
                                    @foreach($item_name_arr as $i_key => $i_value)
                                    <tr>
                                        <td>{{ $i_value }}</td>
                                        <td>{{ number_format($item_price_arr[$i_key]) }}</td>
                                        <td>{{ $item_count_arr[$i_key] }}</td>
                                        <td>{{ number_format($item_amt_arr[$i_key]) }}</td>
                                        <td>{{ number_format($item_tax_amt_arr[$i_key]) }}</td>
                                        <td>{{ $item_tax_type_arr[$i_key] == 1 ? '應稅' : '免稅' }}</td>
                                    </tr>
                                    @endforeach
                                    <tfoot class="border-top-0">
                                        <tr>
                                            <th colspan="3" class="table-light">折讓金額小計</th>
                                            <td>{{ number_format(array_sum($item_amt_arr)) }}</td>
                                            <td>{{ number_format(array_sum($item_tax_amt_arr)) }}</td>
                                            <td>{{ $value->tax_type == 1 ? '應稅' : '免稅' }}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="3" class="table-light">折讓總金額</th>
                                            <td colspan="3">{{ number_format($value->total_amt) }}</td>
                                        </tr> 
                                    </tfoot>
                                </tbody>
                            </table>
                        </div>
                    </x-slot>
                </x-b-modal>
                
            @endforeach
        </div>
    @endif

    <div class="col-auto">
        <a href="{{ request('pre') == 1 ? url()->previous() : Route('cms.order.detail', ['id' => $invoice->source_id]) }}" class="btn btn-outline-primary px-4" 
            role="button">返回 訂單明細</a>
    </div>

    <!-- Modal -->
    <x-b-modal id="confirm-issue-invoice">
        <x-slot name="title">開立發票</x-slot>
        <x-slot name="body">確認要開立此發票？</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認</a>
        </x-slot>
    </x-b-modal>

    <x-b-modal id="confirm-invalid-invoice">
        <x-slot name="title">發票作廢</x-slot>

        <x-slot name="body">
            <form action="{{ Route('cms.order.send-invoice', ['id' => $invoice->id, 'action' => 'invalid']) }}" method="POST">
                @csrf
                <p>確認要作廢此發票？</p>
                <x-b-form-group name="invalid_reason" title="作廢原因" required="true">
                    <i class="bi bi-info-circle" data-bs-toggle="tooltip" title="作廢原因至多為6個字元"></i>
                    <input type="text" class="form-control" name="invalid_reason" value="" placeholder="請輸入作廢原因" aria-label="作廢原因" minlength="1" maxlength="6" required>
                </x-b-form-group>

                <div class="col-auto float-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-danger">確認</button>
                </div>
            </form>
        </x-slot>
    </x-b-modal>

    <x-b-modal id="confirm-allowance-issue">
        <x-slot name="title">開立發票折讓</x-slot>
        <x-slot name="body">確認要開立此發票折讓？</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認</a>
        </x-slot>
    </x-b-modal>

    <x-b-modal id="confirm-allowance-invalid">
        <x-slot name="title">發票折讓作廢</x-slot>

        <x-slot name="body">
            <form action="#" method="POST" id="allowance-invalid">
                @csrf
                <p>確認要作廢此發票折讓？</p>
                <x-b-form-group name="invalid_reason" title="作廢原因" required="true">
                    <i class="bi bi-info-circle" data-bs-toggle="tooltip" title="作廢原因至多為6個字元"></i>
                    <input type="text" class="form-control" name="invalid_reason" value="" placeholder="請輸入作廢原因" aria-label="作廢原因" minlength="1" maxlength="6" required>
                </x-b-form-group>

                <div class="col-auto float-end">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-danger">確認</button>
                </div>
            </form>
        </x-slot>
    </x-b-modal>
@endsection

@once
    @push('sub-styles')
        
    @endpush
    @push('sub-scripts')
        <script>
            // Modal Control
            $('#confirm-issue-invoice').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
            $('#confirm-invalid-invoice').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
            $('#confirm-allowance-issue').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
            $('#confirm-allowance-invalid').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });

            $('.allowance-invalid').on('click', function(){
                $('#allowance-invalid').attr('action', $(this).data('action'));
            });
        </script>
    @endpush
@endonce
