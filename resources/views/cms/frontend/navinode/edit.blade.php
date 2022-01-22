@extends('layouts.main')
@section('sub-content')
    <div class="pt-2 mb-3">
        <a href="{{ Route('cms.navinode.index', ['level' => $level]) }}" class="btn btn-primary" role="button">
            <i class="bi bi-arrow-left"></i> 返回上一頁
        </a>
        <h3>工程版</h3>
    </div>

    <form method="post" action="{{ $formAction }}" novalidate>
        @method('POST')
        @csrf
        <div class="card mb-4">
            <div class="card-header">
                @if ($method === 'create') 新增 @else 編輯 @endif 選單
            </div>
            <div class="card-body">
                <x-b-form-group name="title" title="名稱" required="true">
                    <input type="text" class="form-control @error('title') is-invalid @enderror" name="title"
                        value="{{ old('title', $data->title ?? '') }}" required aria-label="選單名稱" />
                </x-b-form-group>
                @if ($currentLevel < 3)
                    <x-b-form-group name="has_child" title="子階層" required="true">
                        <div class="px-1">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="has_child" type="radio" value="0" required
                                        @if (old('has_child', $data->has_child ?? '0') == '0') checked @endif>
                                    無
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="has_child" type="radio" value="1" required
                                        @if (old('has_child', $data->has_child ?? '') == '1') checked @endif>
                                    有
                                </label>
                            </div>
                        </div>
                    </x-b-form-group>
                @endif
            </div>
        </div>
        <div id="noChildArea" class="card mb-4" @if (old('has_child', $data->has_child ?? '') == '1') hidden @endif>
            <div class="card-header">選單內容</div>
            <div class="card-body">
                <x-b-form-group name="type" title="內容類型" required="true">
                    <div class="px-1">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="type" type="radio" value="group" required
                                    @if (old('type', $data->type ?? 'group') == 'group') checked @endif>
                                群組
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="type" type="radio" value="url" required
                                    @if (old('type', $data->type ?? '') == 'url') checked @endif>
                                網址
                            </label>
                        </div>
                    </div>
                </x-b-form-group>
                <div class="menu_type -group" @if (old('type', $data->type ?? 'group') !== 'group') hidden @endif>
                    <x-b-form-group name="group_id" title="群組" required="true">
                        <select class="form-select" name="group_id" aria-label="Default select example"
                            @if (old('type', $data->type ?? 'group') == 'group') required @else disabled @endif>
                            @foreach ($collections as $key => $group)
                                <option value="{{ $group['id'] }}" @if (old('group_id', $data->group_id ?? '') == $group['id']) selected @endif>
                                    {{ $group['name'] }}</option>
                            @endforeach
                        </select>
                    </x-b-form-group>
                </div>
                <div class="menu_type -url" @if (old('type', $data->type ?? '') !== 'url') hidden @endif>
                    <x-b-form-group name="url" title="網頁連結" required="true">
                        <input type="url" class="form-control @error('url') is-invalid @enderror" name="url"
                            value="{{ old('url', $data->url ?? '') }}" aria-label="網頁連結" placeholder="請輸入連結"
                            @if (old('type', $data->type ?? '') == 'url') required @else disabled @endif />
                    </x-b-form-group>
                </div>
                <x-b-form-group name="target" title="開啟視窗" required="true">
                    <div class="px-1">
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="target" type="radio" value="_self"
                                    @if (old('target', $data->target ?? '_self') == '_self') checked @endif>
                                當前視窗
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <label class="form-check-label">
                                <input class="form-check-input" name="target" type="radio" value="_blank"
                                    @if (old('target', $data->target ?? '') == '_blank') checked @endif>
                                新開視窗
                            </label>
                        </div>
                    </div>
                </x-b-form-group>
            </div>

        </div>
        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary px-4">儲存</button>
        </div>
    </form>
    @if($errors->any())
    {!! implode('', $errors->all('<div>:message</div>')) !!}
@endif
@endsection
@once
    @push('sub-scripts')
        <script>
            // 子階層
            $('input[name="has_child"]').on('change', function() {
                changeHasChild();
            });

            function changeHasChild() {
                const val = $('input[name="has_child"]:checked').val();
                if (val == '0') {
                    // 無，顯示
                    $('#noChildArea').prop('hidden', false);
                    $('#noChildArea').find('input, select').prop({
                        'required': true,
                        'disabled': false
                    });
                } else {
                    // 有，隱藏
                    $('#noChildArea').prop('hidden', true);
                    $('#noChildArea').find('input, select').prop({
                        'required': false,
                        'disabled': true
                    });
                }
            }

            // 內容類型
            $('input[name="type"]').on('change', function() {
                changeMenuType();
            });

            function changeMenuType() {
                const val = $('input[name="type"]:checked').val();
                $(`div.menu_type:not(.-${val})`).prop('hidden', true);
                $(`div.menu_type:not(.-${val})`).find('select, input').prop({
                    'required': false,
                    'disabled': true
                });

                $(`div.menu_type.-${val}`).prop('hidden', false);
                $(`div.menu_type.-${val}`).find('select, input').prop({
                    'required': true,
                    'disabled': false
                });
            }
        </script>
    @endpush
@endonce
