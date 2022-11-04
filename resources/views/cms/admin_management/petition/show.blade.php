@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">申議書</h2>

    <form id="form1" method="post">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <table class="table table-bordered border-secondary">
                <tbody>
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
                                <div class="mb-1"><a href="">{{ $value }}</a></div>
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
            <a href="{{ Route('cms.petition.index', [], true) }}" class="btn btn-outline-primary px-4"
                role="button">返回列表</a>
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
