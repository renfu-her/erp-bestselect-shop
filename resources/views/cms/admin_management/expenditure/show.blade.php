@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">支出憑單</h2>
    <a href="{{ Route('cms.expenditure.print', ['id' => $data->id]) }}" target="_blank" class="btn btn-outline-primary px-4 mb-1">列印</a>

    @php
        $action = isset($type) ? Route('cms.expenditure.audit-confirm', ['id' => $data->id]) : '';
    @endphp
    <form id="form1" method="post" action="{{ $action }}">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <table class="table table-bordered border-secondary">
                <tbody>
                    <tr>
                        <th width="100">單號</th>
                        <td>{{ $data->sn }}</td>
                    </tr>
                    <tr>
                        <th width="100">建立日期</th>
                        <td>{{ $data->created_at }}</td>
                    </tr>
                    <tr>
                        <th width="100">主旨</th>
                        <td>{{ $data->title }}</td>
                    </tr>
                    <tr>
                        <th>申請人</th>
                        <td>{{ $data->user_name }}</td>
                    </tr>
                    <tr>
                        <th>支出科目</th>
                        <td>{{ $data->item_title }}</td>
                    </tr>
                    <tr>
                        <th>支出部門</th>
                        <td>{{ $data->department_title }}</td>
                    </tr>
                    <tr>
                        <th>預計支付方式</th>
                        <td>{{ $data->payment_title }}</td>
                    </tr>
                    <tr>
                        <th>金額</th>
                        <td>{{ $data->amount }}</td>
                    </tr>
                    <tr>
                        <th>內容</th>
                        <td>{!! nl2br($data->content) !!}</td>
                    </tr>
                    <tr>
                        <th>相關單號</th>
                        <td>
                            @foreach ($order as $key => $value)
                                <div class="mb-1"><a href="{{ $value->url }}">{{ $value->order_sn }}</a></div>
                            @endforeach
                            <hr/>
                            @foreach ($relation_order as $key => $value)
                                <div class="mb-1"><a href="{{ $value->url }}">{{ $value->sn }}</a></div>
                            @endforeach
                        </td>
                    </tr>
                </tbody>
            </table>

            <table class="table caption-top">
                <caption>簽核狀態</caption>
                <thead class="border-top-0">
                    <tr>
                        <th>主管</th>
                        <th>職稱</th>
                        <th>簽核時間</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data->users as $key => $value)
                        <tr>
                            <td>{{ $value->user_name }}</td>
                            <td>{{ $value->user_title }}</td>
                            <td>{{ $value->checked_at ? date('Y/m/d H:i:s', strtotime($value->checked_at)) : '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="col-auto">
            @php
                $target = 'index';
                $bTitle = '';
                if (isset($type)) {
                    $target = 'audit-list';
                    $bTitle = '審核';
                }
                
            @endphp
            <a href="{{ Route('cms.expenditure.' . $target, [], true) }}" class="btn btn-outline-primary px-4"
                role="button">返回{{ $bTitle }}列表</a>

            @if (isset($type))
                <button class="btn btn-outline-primary px-4">審核</button>
            @endif
        </div>
    </form>
@endsection
@once
    @push('sub-scripts')
        <script>
            const $clone = $(`.-cloneElem:first-child`).clone();
            const beforeDelFn = ({
                $this
            }) => {
                const tooltip = bootstrap.Tooltip.getInstance($this);
                if (tooltip) {
                    tooltip.dispose(); // 清除提示工具
                }
            };
            Clone_bindDelElem($('.-appendClone .-del'), {
                beforeDelFn: beforeDelFn
            });
            // 新增單號
            $('.-newOrder').off('click').on('click', function() {
                Clone_bindCloneBtn($clone, function($elem) {
                    $elem.find('input').val('');
                    $elem.find('input, button').prop('disabled', false);
                    new bootstrap.Tooltip($elem.find('[data-bs-toggle="tooltip"]'));
                }, {
                    beforeDelFn: beforeDelFn
                });
            });
        </script>
    @endpush
@endonce
