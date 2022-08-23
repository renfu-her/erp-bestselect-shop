@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">轉帳傳票</h2>
    <a href="{{ Route('cms.transfer_voucher.index') }}" class="btn btn-primary" role="button">
        <i class="bi bi-arrow-left"></i> 返回上一頁
    </a>

    <a href="{{ route('cms.transfer_voucher.edit', ['id' => $voucher->tv_id]) }}" class="btn btn-success px-4" role="button">修改</a>

    <a href="javascript:void(0)" role="button" class="btn btn-danger px-4" data-bs-toggle="modal" data-bs-target="#confirm-delete" data-href="{{ Route('cms.transfer_voucher.delete', ['id' => $voucher->tv_id]) }}">刪除</a>

    {{--
    <button type="submit" class="btn btn-danger">中一刀列印畫面</button>
    <button type="submit" class="btn btn-danger">A4列印畫面</button>
    <button type="submit" class="btn btn-danger">修改記錄</button>
    <button type="submit" class="btn btn-danger">明細修改記錄</button>
    --}}

    <br>

    <div class="card shadow mb-4 -detail -detail-primary">
        <div class="card-body px-4">
            <dl class="row">
                <div class="col">
                    <dt>{{ $voucher->company_name }}</dt>
                    <dd></dd>
                </div>
            </dl>

            <dl class="row">
                <div class="col">
                    <dt>轉帳傳票</dt>
                    <dd></dd>
                </div>
            </dl>

            <dl class="row">
                <h4>中華民國 {{ date('Y', strtotime($voucher->tv_voucher_date)) - 1911 }} 年 {{ date('m', strtotime($voucher->tv_voucher_date)) }} 月 {{ date('d', strtotime($voucher->tv_voucher_date)) }} 日</h4>
            </dl>

            <dl class="row">
                <div class="col">
                    <dt>傳票編號：</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>單號：{{ $voucher->tv_sn }}</dt>
                    <dd></dd>
                </div>
            </dl>
        </div>

        <div class="card-body px-4 py-2">
            <div class="table-responsive tableoverbox">
                <table class="table tablelist table-sm mb-0">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th scope="col">會計科目</th>
                            <th scope="col">摘要</th>
                            <th scope="col">幣別</th>
                            <th scope="col">匯率</th>
                            <th scope="col">借方</th>
                            <th scope="col">貸方</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($voucher->tv_items)
                        @foreach(json_decode($voucher->tv_items) as $value)
                        <tr>
                            <td>{{ $value->grade_code . ' ' . $value->grade_name }}</td>
                            <td>{{ $value->summary }}</td>
                            <td>{{ $value->currency_name }}</td>
                            <td>{{ $value->rate }}</td>
                            <td>{{ $value->debit_credit_code == 'debit' ? number_format($value->final_price, 2) : '' }}</td>
                            <td>{{ $value->debit_credit_code == 'credit' ? number_format($value->final_price, 2) : '' }}</td>
                        </tr>
                        @endforeach
                        @endif

                        <tr class="table-light">
                            <td>合計：</td>
                            <td><span class="text-danger">{{ $voucher->tv_debit_price != $voucher->tv_credit_price ? '（借貸不平）' : '' }}</span></td>
                            <td></td>
                            <td></td>
                            <td>{{ number_format($voucher->tv_debit_price) }}</td>
                            <td>{{ number_format($voucher->tv_credit_price) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-body px-4 pb-4">
            <dl class="row">
                <div class="col">
                    <dt>主管：</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>會計：{{ $voucher->accountant_name }}</dt>
                    <dd></dd>
                </div>
                <div class="col">
                    <dt>承辦人：{{ $voucher->creator_name }}</dt>
                    <dd></dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Modal -->
    <x-b-modal id="confirm-delete">
        <x-slot name="title">刪除確認</x-slot>
        <x-slot name="body">確認要刪除此傳票？</x-slot>
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