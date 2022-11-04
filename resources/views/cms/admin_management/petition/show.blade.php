@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">

    </h2>
    <form id="form1" method="post">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <div class="row">
                <div>{{ $data->user_name }}</div>
                <x-b-form-group name="title" title="主旨" required="true" class="mb-3">
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title"
                        name="title" value="{{ old('title', $data->title ?? '') }}" required aria-label="主旨"
                        placeholder="請填入主旨" />
                </x-b-form-group>

                <x-b-form-group name="content" title="內容" required="true" class="mb-3">
                    <textarea type="text" class="form-control @error('content') is-invalid @enderror" id="content" name="content"
                        required aria-label="內容" placeholder="請填入內容">{{ old('content', $data->content ?? '') }}</textarea>
                </x-b-form-group>

                <div>
                    <label class="form-label">相關單號

                    </label>

                    @foreach ($order as $key => $value)
                        <a href="">{{ $value }}</a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card shadow p-4 mb-4">
            <table>
                <thead>
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
                            <td>{{ $value->checked_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
