@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-3">#{{ $breadcrumb_data['sn'] }} 配送狀態</h2>
    @if ($event === 'consignment')
        <x-b-consign-navi :id="$eventId"></x-b-consign-navi>
    @endif
    @if ($event === 'csn_order')
        <x-b-csnorder-navi :id="$eventId"></x-b-csnorder-navi>
    @endif

    @error('error_msg')
    <div class="alert alert-danger" role="alert">
        {{ $message }}
    </div>
    @enderror
    <div>
        <div class="card shadow p-4 mb-4">
            <h6>加入狀態</h6>
            <fieldset class="border rounded p-2 pt-0 mb-4">
                <legend class="col-form-label px-1 p-0 mb-1">出貨狀態</legend>
                <div primary-status></div>
            </fieldset>
            <fieldset class="border rounded p-2 pt-0">
                <legend class="col-form-label px-1 p-0 mb-1">異常狀態</legend>
                <div danger-status></div>
            </fieldset>
        </div>
    </div>

    <form id="form1" action="{{ Route('cms.logistic.updateLogisticStatus',['event' =>$event, 'eventId' => $eventId, 'deliveryId' => $delivery_id], true) }}" method="post">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <h6>配送歷程</h6>
            <div class="table-responsive">
                <table class="table table-sm tableList table-hover mb-1">
                    <tbody class="-appendClone">
                    </tbody>
                </table>
            </div>
            <div id="flow-list" class="ms-3">
                <ul>
                    @foreach ($flowList as $key => $data)
                    <li>
                        <h6 class="mb-1">{{date('Y-m-d H:i:s', strtotime($data->created_at))}}</h6>
                        <p>
                            <span>{{$data->status}}</span>
                            <span>操作人員：{{$data->user_name ?? ''}}</span>
                        </p>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary px-4">儲存</button>
                <a href="{{ $lastPageAction }}"
                    class="btn btn-outline-primary px-4" role="button">返回明細</a>
            </div>
        </div>
    </form>

@endsection
@once
    @push('sub-styles')
    <style>
        fieldset > legend {
            display: inline-block;
            width: auto;
            margin-top: -12px;
            background-color: #FFF;
        }
    </style>
    @endpush
    @push('sub-scripts')
    <script>
        const logisticStatus = @json($logisticStatus);
        const User = @json($user->name);
        initBtn();

        // 初始化按鈕
        function initBtn() {
            for (const key in logisticStatus) {
                if (Object.hasOwnProperty.call(logisticStatus, key)) {
                    const status = logisticStatus[key];
                    let type = '';
                    switch (status) {
                        case '未送達':
                        case '已回倉':
                        case '退回中':
                        case '已退回':
                            type = 'danger';
                            break;
                        default:
                            type = 'primary';
                            break;
                    }
                    $(`div[${type}-status]`).append(`
                        <button type="button" data-code="${key}" class="btn mb-1 btn-outline-${type}">
                            <i class="bi bi-plus-circle"></i> ${status}
                        </button>
                    `);
                }
            }
        }

        // bind btn
        $('button[data-code]').off('click').on('click', function () {
            const code = $(this).data('code');
            let type = 'success';
            type = $(this).hasClass('btn-outline-primary') ? 'primary' : type;
            type = $(this).hasClass('btn-outline-danger') ? 'danger' : type;

            $('tbody.-appendClone').prepend(`
                <tr class="-cloneElem">
                    <td class="table-${type} text-center" style="width:10%">新增</td>
                    <td>${logisticStatus[code]}</td>
                    <td>${User}</td>
                    <td>
                        <button type="button" title="刪除"
                            class="icon icon-btn fs-5 text-danger rounded-circle border-0 -del">
                            <i class="bi bi-trash"></i>
                        </button>
                        <input type="hidden" name="statusCode[]" value="${code}" />
                    </td>
                </tr>
            `);

            // bind -del
            $('#form1 tr.-cloneElem .-del').off('click').on('click', function () {
                $(this).closest('tr.-cloneElem').remove();
            });
        });
    </script>
    @endpush
@endonce
