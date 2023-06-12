@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">申議書</h2>

    <nav class="col-12 border border-bottom-0 rounded-top nav-bg">
        <div class="p-1 pb-0">
            <a href="{{ Route('cms.petition.print', ['id' => $data->id]) }}" target="_blank"
                class="btn btn-sm btn-warning px-3 mb-1">列印</a>
        </div>
    </nav>
    @php
        $action = isset($type) ? Route('cms.petition.audit-confirm', ['id' => $data->id]) : '';
    @endphp
    <form id="form1" method="post" action="{{ $action }}">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <table class="table table-bordered border-secondary">
                <tbody>
                    <tr>
                        <th width="100">序號</th>
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
                        <th>內容</th>
                        <td>{!! nl2br($data->content) !!}</td>
                    </tr>
                    <tr>
                        <th>相關單號</th>
                        <td>
                            @foreach ($order as $key => $value)
                                <div class="mb-1"><a href="{{ $value->url }}">{{ $value->order_sn }}</a></div>
                            @endforeach
                            @if (count($order) > 0 && $relation_order && count($relation_order) > 0)
                                <hr />
                            @endif
                            @if ($relation_order)
                                @foreach ($relation_order as $key => $value)
                                    <div class="mb-1"><a href="{{ $value->url }}">{{ $value->sn }}</a></div>
                                @endforeach
                            @endif

                        </td>
                    </tr>
                </tbody>
            </table>
            @if (isset($type) && isset($canAudit) && $canAudit)
                <div class="mb-4">
                    <caption>簽核意見</caption>
                    <textarea name="note" class="form-control"></textarea>
                </div>
            @endif

            <table class="table caption-top">
                <caption>簽核狀態</caption>
                <thead class="border-top-0">
                    <tr>
                        <th>主管</th>
                        <th>職稱</th>
                        <th>簽核時間</th>
                        <th>意見</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data->users as $key => $value)
                        <tr>
                            <td>{{ $value->user_name }}</td>
                            <td>{{ $value->user_title }}</td>
                            <td>{{ $value->checked_at ? date('Y/m/d H:i:s', strtotime($value->checked_at)) : '' }}</td>
                            <td>{!! nl2br($value->user_note) !!}</td>
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
            <a href="{{ Route('cms.petition.' . $target, [], true) }}" class="btn btn-outline-primary px-4"
                role="button">返回{{ $bTitle }}列表</a>

            @if (isset($type) && isset($canAudit) && $canAudit)
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
