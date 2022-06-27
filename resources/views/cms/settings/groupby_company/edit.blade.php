@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.groupby-company.index', [], true) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
    </div>

    <form id="form1" method="post" action="{{ $action }}">
        @method('POST')
        @csrf

        <div class="card mb-4">
            <div class="card-header">
                @if ($method === 'create')
                    新增
                @else
                    編輯
                @endif 團購主公司
            </div>
            <div class="card-body">
                <div class="row">
                    <x-b-form-group name="name" title="公司名稱" required="true" class="col-12 col-sm-6">
                        <input class="form-control @error('title') is-invalid @enderror" name="title"
                            value="{{ old('title', $mainData->title ?? '') }}" placeholder="請輸入公司名稱" />
                    </x-b-form-group>
                    <x-b-form-group name="name" title="啟用" required="true" class="col-12 col-sm-6">
                        <div class="form-check form-switch form-switch-lg">
                            <input class="form-check-input @error('active') is-invalid @enderror" type="checkbox"
                                name="active" value="1" checked>
                        </div>
                    </x-b-form-group>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                子團
            </div>
            <div class="card-body p-4 mb-4">
                <div class="table-responsive tableOverBox">
                    <table class="table tableList table-hover mb-1">
                        <thead>
                            <tr>
                                <th scope="col" class="text-center" width="76">啟用</th>
                                <th scope="col">團名</th>
                                <th scope="col">代碼</th>
                                <th scope="col" class="text-center" width="75">刪除</th>
                            </tr>
                        </thead>
                        <tbody class="-appendClone">
                            <tr class="-cloneElem d-none">
                                <td class="text-center">
                                    <div class="form-check form-switch form-switch-lg">
                                        <input class="form-check-input" type="checkbox" name="n_active[]" value="1"
                                            checked>
                                    </div>
                                </td>
                                <td>
                                    <input class="form-control -ll" name="n_title[]" value="" type="text"
                                        aria-label="子團團名" />
                                </td>
                                <td>
                                    <input class="form-control -l" name="n_code[]" value="" type="text"
                                        aria-label="子團代碼" />
                                </td>
                                <td>
                                    <button type="button"
                                        class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            @if ($method === 'edit')
                                @foreach ($childData as $key => $value)
                                    <tr class="-cloneElem">
                                        <td class="text-center">
                                            <div class="form-check form-switch form-switch-lg">
                                                <input class="form-check-input" type="checkbox"
                                                    name="o_active_{{ $key }}" value="1"
                                                    @if ($value->is_active) checked @endif>
                                            </div>
                                        </td>
                                        <td>
                                            <input class="form-control -ll" name="o_title[]"
                                                value="{{ old('o_title.' . $key, $value['title']) }}" type="text"
                                                aria-label="子團團名" />
                                        </td>
                                        <td>
                                            <input class="form-control -l" name="o_code[]"
                                                value="{{ old('o_code.' . $key, $value['code']) }}" type="text"
                                                aria-label="子團代碼" />
                                        </td>
                                        <td>
                                            <input type="hidden" name="o_id[]" value="{{ $value['id'] }}">
                                        </td>
                                    </tr>
                                @endforeach
                            @endif

                            @foreach (old('n_title', []) as $key => $value)
                                <tr class="-cloneElem">
                                    <td class="text-center">
                                        <div class="form-check form-switch form-switch-lg">
                                            <input class="form-check-input" type="checkbox" name="n_active[]" value="1"
                                                checked>
                                        </div>
                                    </td>
                                    <td>
                                        <input class="form-control -ll @error('n_title.' . $key) is-invalid @enderror"
                                            name="n_title[]" value="{{ old('n_title.' . $key) }}" type="text"
                                            aria-label="子團團名" />
                                    </td>
                                    <td>
                                        <input class="form-control -l @error('n_code.' . $key) is-invalid @enderror"
                                            name="n_code[]" value="{{ old('n_code.' . $key) }}" type="text"
                                            aria-label="子團代碼" />
                                    </td>
                                    <td>
                                        <button type="button"
                                            class="icon -del icon-btn fs-5 text-danger rounded-circle border-0 p-0">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
                <div class="d-grid gap-2 mt-3">
                    <button type="button" class="btn btn-outline-primary border-dashed -newClone"
                        style="font-weight: 500;">
                        <i class="bi bi-plus-circle"></i> 新增子團
                    </button>
                </div>
            </div>
        </div>
        @if ($errors->any())
            {!! implode('', $errors->all('<div>:message</div>')) !!}
        @endif

        <div class="d-flex justify-content-end mt-3">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
        </div>
    </form>
@endsection
@once
    @push('sub-styles')
        <style>
            .tableList input.form-control.-ll {
                min-width: 200px;
            }
        </style>
    @endpush
    @push('sub-scripts')
        <script>
            // clone 項目
            const $clone = $('.-cloneElem.d-none').clone();
            $clone.removeClass('d-none');
            $('.-cloneElem.d-none').remove();

            // 新增子團
            $('.-newClone').off('click').on('click', function() {
                Clone_bindCloneBtn($clone);
            });

            // switch #
            $('#form1').submit(function (e) { 
                $('input[name="n_active[]"]').each(function (index, element) {
                    // element == this
                    $(element).attr('name', `n_active_${index}[]`);
                });
            });
        </script>
    @endpush
@endonce
