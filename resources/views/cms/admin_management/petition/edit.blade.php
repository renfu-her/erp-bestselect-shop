@extends('layouts.main')
@section('sub-content')
    <h2 class="mb-4">
        @if ($method === 'create')
            新增
        @else
            編輯
        @endif 申議書
    </h2>
    <form id="form1" method="post" action="{{ $formAction }}">
        @method('POST')
        @csrf

        <div class="card shadow p-4 mb-4">
            <div class="row">
                <x-b-form-group name="title" title="主旨" required="true" class="mb-3">
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title"
                        name="title" value="{{ old('title', $data->title ?? '') }}" required aria-label="主旨"
                        placeholder="請填入主旨" />
                </x-b-form-group>

                <x-b-form-group name="content" title="內容" required="true" class="mb-3">
                    <textarea type="text" class="form-control @error('content') is-invalid @enderror" id="content" name="content"
                        required aria-label="內容" placeholder="請填入內容" rows="5">{{ old('content', $data->content ?? '') }}</textarea>
                </x-b-form-group>

                <div>
                    <label class="form-label">相關單號
                        <button type="button" class="btn btn-sm btn-outline-primary border-dashed ms-2 -newOrder"
                            style="font-weight: 500;">
                            <i class="bi bi-plus-circle"></i> 新增單號
                        </button>
                    </label>
                    <div class="row -appendClone mb-2">
                        @if ($method === 'create' || count(old('order', $order ?? [])) === 0)
                            <div class="input-group col-12 col-md-6 mb-2 -cloneElem">
                                <input class="form-control" type="text" name="order[]" placeholder="請填入相關單號"
                                    aria-label="相關單號">
                                <button class="btn btn-outline-secondary -del" type="button" data-bs-toggle="tooltip"
                                    title="刪除">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        @endif

                        @foreach (old('order', $order ?? []) as $key => $value)
                            <div class="input-group col-12 col-md-6 mb-2 -cloneElem">
                                <input class="form-control @error('order.' . $key) is-invalid @enderror" type="text"
                                    name="order[]" value="{{ $value }}" placeholder="請填入相關單號" aria-label="相關單號">
                                <button class="btn btn-outline-secondary -del" type="button" data-bs-toggle="tooltip"
                                    title="刪除">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <mark><i class="bi bi-exclamation-diamond-fill text-warning"></i>
                        支援格式：採購單號、訂單編號、代墊單號、付款單號</mark>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="col-auto">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
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
                    $elem.find('input').val('').removeClass('is-invalid');
                    $elem.find('input, button').prop('disabled', false);
                    new bootstrap.Tooltip($elem.find('[data-bs-toggle="tooltip"]'));
                }, {
                    beforeDelFn: beforeDelFn
                });
            });
        </script>
    @endpush
@endonce
