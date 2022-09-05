@extends('layouts.main')

@section('sub-content')
    <h2 class="mb-4">電子發票</h2>

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
            <table class="table table-bordered text-center align-middle d-sm-table d-none text-nowrap">
                <tbody class="border-top-0">
                    <tr class="table-light">
                        <td class="col-2">摘要</td>
                        <td class="col-2">數量</td>
                        <td class="col-2">單價</td>
                        <td class="col-2">金額</td>
                        <td class="col-2">稅別</td>
                    </tr>
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
                        <td>{{ $item_count_arr[$key] }}</td>
                        <td>{{ number_format($item_price_arr[$key]) }}</td>
                        <td>{{ number_format($item_amt_arr[$key]) }}</td>
                        <td>{{ $item_tax_type_arr[$key] == 1 ? '應稅' : '免稅' }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="3">發票金額</td>
                        <td>{{ number_format($invoice->total_amt) }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-auto">
        @if($invoice->status == 1 && $invoice->r_status != 'SUCCESS')
        <a href="javascript:void(0)" role="button" class="btn btn-primary px-4" data-bs-toggle="modal" 
            data-bs-target="#confirm-invoice" data-href="{{ Route('cms.order.re-send-invoice', ['id' => $invoice->id]) }}">
            重新開立發票
        </a>
        @endif

        <a href="{{ url()->previous() }}" class="btn btn-outline-primary px-4" 
            role="button">返回上一頁</a>
        <a href="{{ Route('cms.order.detail', ['id' => $invoice->source_id]) }}" class="btn btn-outline-primary px-4" 
            role="button">返回 訂單明細</a>
    </div>

    <!-- Modal -->
    <x-b-modal id="confirm-invoice">
        <x-slot name="title">重新開立發票</x-slot>
        <x-slot name="body">確認要重新開立此發票？</x-slot>
        <x-slot name="foot">
            <a class="btn btn-danger btn-ok" href="#">確認</a>
        </x-slot>
    </x-b-modal>
@endsection

@once
    @push('sub-styles')
        
    @endpush
    @push('sub-scripts')
        <script>
            // Modal Control
            $('#confirm-invoice').on('show.bs.modal', function(e) {
                $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
            });
        </script>
    @endpush
@endonce
